<?php

namespace Craue\GeoBundle\Tests;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
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
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistance($lat, $lng, $maxRadiusInKm = null) {
		$qb = static::getRepo()->createQueryBuilder('poi')
			->select('poi, GEO_DISTANCE(:lat, :lng, poi.lat, poi.lng) AS distance')
			->setParameter('lat', $lat)
			->setParameter('lng', $lng)
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
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
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistanceByPostalCode($country, $postalCode, $maxRadiusInKm = null) {
		$qb = static::getRepo()->createQueryBuilder('poi')
			->select('poi, GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode) AS distance')
			->setParameter('country', $country)
			->setParameter('postalCode', $postalCode)
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			$qb
				->having('distance <= :radius')
				->setParameter('radius', $maxRadiusInKm)
			;
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function createKernel(array $options = array()) {
		return new AppKernel(isset($options['environment']) ? $options['environment'] : 'test',
				isset($options['config']) ? $options['config'] : 'config.yml');
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
	 * Persists a number of {@code GeoPostalCode}s using dummy data.
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
