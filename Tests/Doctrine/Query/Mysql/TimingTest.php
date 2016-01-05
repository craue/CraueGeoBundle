<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2016 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class TimingTest extends IntegrationTestCase {

	const NUMBER_OF_POIS = 50000;

	/**
	 * @var boolean
	 */
	private static $dummyDataAdded = false;

	protected function setUp() {
		$this->initClient(array(), !self::$dummyDataAdded);

		// Only add the dummy data once as it takes quite some time.
		if (!self::$dummyDataAdded) {
			$this->persistDummyGeoPostalCodes(static::NUMBER_OF_POIS);
			self::$dummyDataAdded = true;
		}
	}

	public function testTimingGeoDistance_withRadius() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1, 1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(0.4, $duration);

		$this->assertEquals(854, count($result));
	}

	public function testTimingGeoDistance_withRadius_optimized() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1, 1, true);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(0.3, $duration);

		$this->assertEquals(854, count($result));
	}

	public function testTimingGeoDistance_withoutRadius() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(15, $duration);

		$this->assertCount(static::NUMBER_OF_POIS, $result);
	}

	public function testTimingGeoDistanceByPostalCode_withRadius() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123', 1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(2.8, $duration);

		$this->assertEquals(1703, count($result));
	}

	public function testTimingGeoDistanceByPostalCode_withRadius_optimized() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123', 1, true);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(0.65, $duration);

		$this->assertEquals(1703, count($result));
	}

	public function testTimingGeoDistanceByPostalCode_withoutRadius() {
		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123');
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(15, $duration);

		$this->assertCount(static::NUMBER_OF_POIS, $result);
	}

}
