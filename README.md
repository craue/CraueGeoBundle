# Information

CraueGeoBundle provides Doctrine functions for your Symfony project which allow you to calculate geographical distances within database queries.
This bundle is independent of any web service, so once you got it running, it will keep running.
There are two Doctrine functions, which return a distance in km:

- `GEO_DISTANCE` takes latitude + longitude for origin and destination
- `GEO_DISTANCE_BY_POSTAL_CODE` takes country + postal code for origin and destination

# Installation

## Get the bundle

Let Composer download and install the bundle by running

```sh
composer require craue/geo-bundle
```

in a shell.

## Enable the bundle

```php
// in app/AppKernel.php
public function registerBundles() {
	$bundles = [
		// ...
		new Craue\GeoBundle\CraueGeoBundle(),
	];
	// ...
}
```

## Prepare the table with geographical data needed for calculations

The `GEO_DISTANCE_BY_POSTAL_CODE` function, if you'd like to use it, relies on some data which has to be added to your
database first.

### Create the table

The `GeoPostalCode` entity provided contains the structure for the geographical data. You import it by calling either

```sh
# in a shell
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

or

```sh
# in a shell
php bin/console doctrine:schema:update
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

Choose the following steps depending on the version of DoctrineFixturesBundle you're using.

#### Either: DoctrineFixturesBundle < 3.0

Load the fixture(s) in the given folder.

```sh
# in a shell
php bin/console doctrine:fixtures:load --append --fixtures="src/MyCompany/MyBundle/Doctrine/Fixtures/CraueGeo"
```

#### Or: DoctrineFixturesBundle >= 3.1

You first need to register the fixture as a service with a group of your choice.

```yaml
# in app/config/config.yml
services:
  my_geonames_postal_code_data:
    class: MyCompany\MyBundle\Doctrine\Fixtures\CraueGeo\MyGeonamesPostalCodeData
    public: false
    tags:
     - { name: doctrine.fixture.orm, group: my_geo_data }
```

It's also possible to register all classes in a specific folder as services.

```yaml
# in app/config/config.yml
services:
  MyCompany\MyBundle\Doctrine\Fixtures\CraueGeo\:
    resource: '../../src/MyCompany/MyBundle/Doctrine/Fixtures/CraueGeo/*'
    public: false
    tags:
     - { name: doctrine.fixture.orm, group: my_geo_data }
```

Then, load the fixture(s) of that group.

```sh
# in a shell
php bin/console doctrine:fixtures:load --append --group=my_geo_data
```

#### In both cases

That's it. Of course you can use other data sources you have access to, and write a custom fixture to import it.

If you have out of memory issues when importing a large number of entries try adding the `--no-debug` switch to avoid
logging every single Doctrine query.

# Usage

Let's say you have an entity `Poi` containing countries and postal codes. Now you wish to find all entities within a
specific geographical distance with a radius of `$radiusInKm` from a given postal code `$postalCode` in country
`$country`, and order them by distance.

```php
use MyCompany\MyBundle\Entity\Poi;

// example values which could come from a form, remember to validate/sanitize them first
$country = 'DE';
$postalCode = '10115';
$radiusInKm = 10;

// create a query builder
$queryBuilder = $this->getDoctrine()->getEntityManager()->getRepository(Poi::class)->createQueryBuilder('poi');

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

# Advanced stuff

## Using the Doctrine functions for a different database platform

By default, the Doctrine functions are automatically registered for usage with MySQL. But you can tell the bundle that
you want to use them with a different database platform by setting a `flavor`:

```yaml
# in app/config/config.yml
craue_geo:
  flavor: postgresql
```

Currently, the following flavors are supported:
- `mysql`: MySQL (default value)
- `postgresql`: PostgreSQL
- `none`: prevents registration of the Doctrine functions in case you want to do it manually

As PostgreSQL doesn't support aliases in the HAVING clause and further requires `poi` to appear in the GROUP BY clause,
you need to adapt the query (from the usage example above):

```php
$queryBuilder
	->select('poi, GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) AS HIDDEN distance')
	->having('GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) <= :radius')
	->setParameter('country', $country)
	->setParameter('postalCode', $postalCode)
	->setParameter('radius', $radiusInKm)
	->groupBy('poi')
	->orderBy('distance')
;
```

## Avoid creating the postal code table

If you want to avoid registering the `GeoPostalCode` entity (and as a result, avoid creating the `craue_geo_postalcode` table) at all, add

```yaml
# in app/config/config.yml
craue_geo:
  enable_postal_code_entity: false
```

to your configuration.

## Use custom names for the Doctrine functions

If you don't like the default names or need to avoid conflicts with other functions, you can set custom names:

```yaml
# in app/config/config.yml
craue_geo:
  functions:
    geo_distance: MY_VERY_OWN_GEO_DISTANCE_FUNCTION
    geo_distance_by_postal_code: MY_VERY_OWN_GEO_DISTANCE_BY_POSTAL_CODE_FUNCTION
```
