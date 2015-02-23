<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoDistanceTest extends IntegrationTestCase {

	/**
	 * @dataProvider dataGeoDistance
	 */
	public function testGeoDistance($latOrigin, $lngOrigin, $latDestination, $lngDestination, $expectedDistance) {
		// there must be any data in the table to get a result at all
		static::persistDummyGeoPostalCodes(1);

		$qb = static::getRepo()->createQueryBuilder('poi')
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
		return array(
			array(52.392759, 13.065135, 52.392759, 13.065135, 0),
			array(52.392759, 13.065135, 52.525011, 13.369438, 25.352806214654937),
		);
	}

}
