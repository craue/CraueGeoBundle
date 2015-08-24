<?php

namespace Craue\GeoBundle\Tests;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	/**
	 * {@inheritDoc}
	 */
	public static function setUpBeforeClass() {
		static::rebuildDatabase();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp() {
		static::createClient();
		$this->cleanDatabaseBeforeTest();
	}

	protected function cleanDatabaseBeforeTest() {
		static::removeAllGeoPostalCodes();
	}

	/**
	 * @param double $lat
	 * @param double $lng
	 * @param double $maxRadiusInKm
	 * @param boolean $addRadiusOptimization
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistance($lat, $lng, $maxRadiusInKm = null, $addRadiusOptimization = false) {
		$qb = static::getRepo()->createQueryBuilder('poi')
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
	 * @param boolean $addRadiusOptimization
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistanceByPostalCode($country, $postalCode, $maxRadiusInKm = null, $addRadiusOptimization = false) {
		$qb = static::getRepo()->createQueryBuilder('poi')
			->select('poi, GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) AS distance')
			->setParameter('country', $country)
			->setParameter('postalCode', $postalCode)
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			if ($addRadiusOptimization) {
				$qbOrigin = static::getRepo()->createQueryBuilder('poi')
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

		if (class_exists('Craue\GeoBundle\Tests\LocalAppKernel')) {
			return new LocalAppKernel($environment, $configFile);
		}

		return new AppKernel($environment, $configFile);
	}

	protected static function rebuildDatabase() {
		static::createClient();
		$application = new Application(static::$kernel);
		$application->setAutoExit(false);

		static::executeCommand($application, 'doctrine:schema:drop', array('--force' => true, '--full-database' => true));
		static::executeCommand($application, 'doctrine:schema:update', array('--force' => true));
	}

	private static function executeCommand(Application $application, $command, array $options = array()) {
		$options = array_merge($options, array(
			'--env' => 'test',
			'--no-debug' => null,
			'--no-interaction' => null,
			'--quiet' => null,
			'command' => $command,
		));

		return $application->run(new ArrayInput($options));
	}

	/**
	 * Persists a {@code GeoPostalCode}.
	 * @param string $country
	 * @param string $postalCode
	 * @param double $lat
	 * @param double $lng
	 * @return GeoPostalCode
	 */
	protected static function persistGeoPostalCode($country, $postalCode, $lat, $lng) {
		$entity = new GeoPostalCode();
		$entity->setCountry($country);
		$entity->setPostalCode($postalCode);
		$entity->setLat($lat);
		$entity->setLng($lng);

		$em = static::getEntityManager();
		$em->persist($entity);
		$em->flush();

		return $entity;
	}

	/**
	 * Persists a number of {@code GeoPostalCode}s using non-random dummy data.
	 * @param integer $number
	 */
	protected static function persistDummyGeoPostalCodes($number) {
		$em = static::getEntityManager();

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
	protected static function removeAllGeoPostalCodes() {
		$em = static::getEntityManager();

		foreach (static::getRepo()->findAll() as $entity) {
			$em->remove($entity);
		}

		$em->flush();
	}

	/**
	 * @return EntityManager
	 */
	protected static function getEntityManager() {
		return static::getService('doctrine')->getManager();
	}

	/**
	 * @return EntityRepository
	 */
	protected static function getRepo() {
		return static::getEntityManager()->getRepository(get_class(new GeoPostalCode()));
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected static function getService($id) {
		return static::$kernel->getContainer()->get($id);
	}

}
