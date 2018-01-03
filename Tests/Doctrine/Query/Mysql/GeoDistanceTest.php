<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoDistanceTest extends IntegrationTestCase {

	/**
	 * @var bool[]
	 */
	private static $dummyDataAdded = array();

	protected function prepareDatabase($platform, $config, $requiredExtension) {
		if (!array_key_exists($platform, self::$dummyDataAdded)) {
			self::$dummyDataAdded[$platform] = false;
		}

		$this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config), !self::$dummyDataAdded[$platform]);

		// There must be some data in the table to get a result at all, but it's fine to only add the dummy data once (per platform).
		if (!self::$dummyDataAdded[$platform]) {
			$this->persistDummyGeoPostalCodes(1);
			self::$dummyDataAdded[$platform] = true;
		}
	}

	/**
	 * @dataProvider dataGeoDistance
	 */
	public function testGeoDistance($platform, $config, $requiredExtension,
			$latOrigin, $lngOrigin, $latDestination, $lngDestination, $expectedDistance) {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$qb = $this->getRepo()->createQueryBuilder('poi')
			->select('GEO_DISTANCE(:latOrigin, :lngOrigin, :latDestination, :lngDestination)')
			->setParameter('latOrigin', $latOrigin)
			->setParameter('lngOrigin', $lngOrigin)
			->setParameter('latDestination', $latDestination)
			->setParameter('lngDestination', $lngDestination)
			->setMaxResults(1)
		;

		$this->assertEquals($expectedDistance, $qb->getQuery()->getSingleScalarResult());
	}

	public function dataGeoDistance() {
		return self::duplicateTestDataForEachPlatform(array(
			array(52.392759, 13.065135, 52.392759, 13.065135, 0),
			array(52.392759, 13.065135, 52.525011, 13.369438, 25.32498093345365),
			array(-43.5131367, 172.5990772, -43.8951617, 171.7203311, 82.42610380554926),
			array(-0.1865943, -78.4305382, 0.3516889, -78.1234253, 68.91091929958483),
		));
	}

}
