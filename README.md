# Information

CraueGeoBundle provides Doctrine functions which allow you to calculate geographical distances within database queries.
This bundle is independent of any web service, so once you got it running, it will keep running.
There are two Doctrine functions, which return a distance in km:

- `GEO_DISTANCE` takes latitude + longitude for origin and destination
- `GEO_DISTANCE_BY_POSTAL_CODE` takes country + postal code for origin and destination

This bundle should be used in conjunction with Symfony2.

# Installation

## Get the bundle

Let Composer download and install the bundle by first adding it to your composer.json

```json
{
	"require": {
		"craue/geo-bundle": "dev-master"
	}
}
```

and then running

```sh
php composer.phar update craue/geo-bundle
```

in a shell.

## Enable the bundle

```php
// in app/AppKernel.php
public function registerBundles() {
	$bundles = array(
		// ...
		new Craue\GeoBundle\CraueGeoBundle(),
	);
	// ...
}
```

## Register the Doctrine functions you need

You need to manually register the Doctrine functions you want to use.
See http://symfony.com/doc/current/cookbook/doctrine/custom_dql_functions.html for details.

```yaml
# in app/config/config.yml
doctrine:
  orm:
    dql:
      numeric_functions:
        GEO_DISTANCE: Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance
        GEO_DISTANCE_BY_POSTAL_CODE: Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistanceByPostalCode
```

## Prepare the table with geographical data needed for calculations

The `GEO_DISTANCE_BY_POSTAL_CODE` function, if you'd like to use it, relies on some data which has to be added to your
database first.

### Create the table

The `GeoPostalCode` entity provided contains the structure for the geographical data. You import it by calling either

```sh
# in a shell
php app/console doctrine:migrations:diff
php app/console doctrine:migrations:migrate
```

or

```sh
# in a shell
php app/console doctrine:schema:update
```

or however you like.

### Import the geographical data

This is probably the most annoying step: Storing all postal codes with their geographical positions for the countries
you need. Fortunately, it's not that hard to get this information and import it into your database.

Go to http://download.geonames.org/export/zip/ and download the archives for the countries you need. Let's just take
`DE.zip`. Unzip the included `DE.txt` file, e.g. to `/tmp/DE.txt`.

Create a fixture class (in a separate folder to be able to load only this one) which extends the provided base class:

```php
// MyCompany/MyBundle/Doctrine/Fixtures/CraueGeo/MyGeonamesPostalCodeData.php
namespace MyCompany\MyBundle\Doctrine\Fixtures\CraueGeo;

use Craue\GeoBundle\Doctrine\Fixtures\GeonamesPostalCodeData;
use Doctrine\Common\Persistence\ObjectManager;

class MyGeonamesPostalCodeData extends GeonamesPostalCodeData {

	public function load(ObjectManager $manager) {
		$this->clearPostalCodesTable($manager);
		$this->addEntries($manager, '/tmp/DE.txt');
	}

}
```

Now, backup your database! Don't blame anyone else for data loss if something goes wrong.
Then import the fixture and remember to use the `--append` parameter.

```sh
# in a shell
php app/console doctrine:fixtures:load --append --fixtures="src/MyCompany/MyBundle/Doctrine/Fixtures/CraueGeo"
```

That's it. Of course you can use other data sources you have access to, and write a custom fixture to import it.

If you have out of memory issues when importing a large number of entries try adding the `--no-debug` switch to avoid
logging every single Doctrine query.

# Usage

Let's say you have an entity `Poi` containing countries and postal codes. Now you wish to find all entities within a
specific geographical distance with a radius of `$radiusInKm` from a given postal code `$postalCode` in country
`$country`, and order them by distance.

```php
// example values which could come from a form, remember to validate/sanitize them first
$country = 'DE';
$postalCode = '10115';
$radiusInKm = 10;

// create a query builder
$queryBuilder = $this->getDoctrine()->getEntityManager()
		->getRepository('MyCompany\MyBundle\Entity\Poi')->createQueryBuilder('poi');

// build the query
$queryBuilder
	->select('poi, GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) AS HIDDEN distance')
	->having('distance <= :radius')
	->setParameter('country', $country)
	->setParameter('postalCode', $postalCode)
	->setParameter('radius', $radiusInKm)
	->orderBy('distance')
;
```

The `HIDDEN` keyword is available as of Doctrine 2.2.
