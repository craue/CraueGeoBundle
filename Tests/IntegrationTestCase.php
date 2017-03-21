<?php

namespace Craue\GeoBundle\Tests;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	/**
	 * @var bool[]
	 */
	private static $databaseInitialized = array();

	/**
	 * @param double $lat
	 * @param double $lng
	 * @param double $maxRadiusInKm
	 * @param bool $addRadiusOptimization
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistance($lat, $lng, $maxRadiusInKm = null, $addRadiusOptimization = false) {
		$qb = $this->getRepo()->createQueryBuilder('poi')
			->select('poi, GEO_DISTANCE(:lat, :lng, poi.lat, poi.lng) AS distance')
			->setParameter('lat', $lat)
			->setParameter('lng', $lng)
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			if ($addRadiusOptimization) {
				$this->addRadiusOptimization($qb, $lat, $lng, $maxRadiusInKm);
			}

			$qb
				->having('distance <= :radius')
				->setParameter('radius', $maxRadiusInKm)
			;
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * @param string $country
	 * @param string $postalCode
	 * @param double $maxRadiusInKm
	 * @param bool $addRadiusOptimization
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistanceByPostalCode($country, $postalCode, $maxRadiusInKm = null, $addRadiusOptimization = false) {
		$qb = $this->getRepo()->createQueryBuilder('poi')
			->select('poi, GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) AS distance')
			->setParameter('country', $country)
			->setParameter('postalCode', $postalCode)
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			if ($addRadiusOptimization) {
				$qbOrigin = $this->getRepo()->createQueryBuilder('poi')
					->andWhere('poi.country = :country')
					->andWhere('poi.postalCode = :postalCode')
					->setParameter('country', $country)
					->setParameter('postalCode', $postalCode)
				;
				$origin = $qbOrigin->getQuery()->getOneOrNullResult();
				if ($origin !== null) {
					$this->addRadiusOptimization($qb, $origin->getLat(), $origin->getLng(), $maxRadiusInKm);
				}
			}

			$qb
				->having('distance <= :radius')
				->setParameter('radius', $maxRadiusInKm)
			;
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * Adds the radius optimization mentioned in {@see http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL} (pages 11-13) to
	 * the given {@code QueryBuilder} instance.
	 * @param QueryBuilder $qb
	 * @param double $latOrigin
	 * @param double $lngOrigin
	 * @param double $maxRadiusInKm
	 */
	private function addRadiusOptimization(QueryBuilder $qb, $latOrigin, $lngOrigin, $maxRadiusInKm) {
		$latDistance = 111.2; // distance between two latitudes is about 111.2 km
		$latDiff = $maxRadiusInKm / $latDistance;
		$lngDiff = $maxRadiusInKm / abs(cos(deg2rad($latOrigin)) * $latDistance);

		$qb
			->andWhere('poi.lat BETWEEN :lat1 AND :lat2')
			->andWhere('poi.lng BETWEEN :lng1 AND :lng2')
			->setParameter('lat1', $latOrigin - $latDiff)
			->setParameter('lat2', $latOrigin + $latDiff)
			->setParameter('lng1', $lngOrigin - $lngDiff)
			->setParameter('lng2', $lngOrigin + $lngDiff)
		;
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function createKernel(array $options = array()) {
		$environment = isset($options['environment']) ? $options['environment'] : 'test';
		$configFile = isset($options['config']) ? $options['config'] : 'config.yml';

		return new AppKernel($environment, $configFile);
	}

	/**
	 * Initializes a client and prepares the database.
	 * @param array $options options for creating the client
	 * @param bool $cleanDatabase if the database should be cleaned in case it already exists
	 * @return Client
	 */
	protected function initClient(array $options = array(), $cleanDatabase = true) {
		$client = static::createClient($options);
		$environment = static::$kernel->getEnvironment();

		// Avoid completely rebuilding the database for each test. Create it only once per environment. After that, cleaning it is enough.
		if (!array_key_exists($environment, self::$databaseInitialized) || !self::$databaseInitialized[$environment]) {
			$this->rebuildDatabase();
			self::$databaseInitialized[$environment] = true;
		} elseif ($cleanDatabase) {
			$this->removeAllGeoPostalCodes();
		}

		return $client;
	}

	protected function rebuildDatabase() {
		$em = $this->getEntityManager();
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new SchemaTool($em);

		$schemaTool->dropSchema($metadata);
		$schemaTool->createSchema($metadata);
	}

	/**
	 * Persists a {@code GeoPostalCode}.
	 * @param string $country
	 * @param string $postalCode
	 * @param double $lat
	 * @param double $lng
	 * @return GeoPostalCode
	 */
	protected function persistGeoPostalCode($country, $postalCode, $lat, $lng) {
		$entity = new GeoPostalCode();
		$entity->setCountry($country);
		$entity->setPostalCode($postalCode);
		$entity->setLat($lat);
		$entity->setLng($lng);

		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush();

		return $entity;
	}

	/**
	 * Persists a number of {@code GeoPostalCode}s using non-random dummy data.
	 * @param int $number
	 */
	protected function persistDummyGeoPostalCodes($number) {
		$em = $this->getEntityManager();

		for ($i = 0; $i < $number; ++$i) {
			$entity = new GeoPostalCode();
			$entity->setCountry('DE');
			$entity->setPostalCode($i);
			$entity->setLat('52.'.$i);
			$entity->setLng('13.'.$i);
			$em->persist($entity);

			if ((($i + 1) % 10000) === 0) {
				$em->flush();
				$em->clear();
			}
		}

		$em->flush();
	}

	/**
	 * Removes all {@code GeoPostalCode}s.
	 */
	protected function removeAllGeoPostalCodes() {
		$em = $this->getEntityManager();

		foreach ($this->getRepo()->findAll() as $entity) {
			$em->remove($entity);
		}

		$em->flush();
	}

	/**
	 * @return EntityManager
	 */
	protected function getEntityManager() {
		return $this->getService('doctrine')->getManager();
	}

	/**
	 * @return EntityRepository
	 */
	protected function getRepo() {
		return $this->getEntityManager()->getRepository('Craue\GeoBundle\Entity\GeoPostalCode');
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected function getService($id) {
		return static::$kernel->getContainer()->get($id);
	}

}
