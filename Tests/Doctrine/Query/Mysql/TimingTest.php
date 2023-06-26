<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class TimingTest extends IntegrationTestCase {

	public const NUMBER_OF_POIS = 50000;

	/**
	 * @var bool[]
	 */
	private static array $dummyDataAdded = [];

	protected function prepareDatabase($platform, $config, $requiredExtension): void
    {
		if (!array_key_exists($platform, self::$dummyDataAdded)) {
			self::$dummyDataAdded[$platform] = false;
		}

		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config], !self::$dummyDataAdded[$platform]);

		// Only add the dummy data once (per platform) as it takes quite some time.
		if (!self::$dummyDataAdded[$platform]) {
			$this->persistDummyGeoPostalCodes(static::NUMBER_OF_POIS);
			self::$dummyDataAdded[$platform] = true;
		}
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistance_withRadius($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1, 1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan($platform === self::PLATFORM_POSTGRESQL ? 0.6 : 0.4, $duration);

		$this->assertCount(854, $result);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistance_withRadius_optimized($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1, 1, true);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(0.4, $duration);

		$this->assertCount(854, $result);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistance_withoutRadius($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistance(52.1, 13.1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan(15, $duration);

		$this->assertCount(static::NUMBER_OF_POIS, $result);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistanceByPostalCode_withRadius($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123', 1);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan($platform === self::PLATFORM_POSTGRESQL ? 7 : 2.8, $duration);

		$this->assertCount(1703, $result);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistanceByPostalCode_withRadius_optimized($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123', 1, true);
		$duration = microtime(true) - $startTime;
		$this->assertLessThan($platform === self::PLATFORM_POSTGRESQL ? 0.8 : 0.65, $duration);

		$this->assertCount(1703, $result);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testTimingGeoDistanceByPostalCode_withoutRadius($platform, $config, $requiredExtension): void
    {
		$this->prepareDatabase($platform, $config, $requiredExtension);

		$startTime = microtime(true);
		$result = $this->getPoisPerGeoDistanceByPostalCode('DE', '123');
		$duration = microtime(true) - $startTime;
		$this->assertLessThan($platform === self::PLATFORM_POSTGRESQL ? 17 : 15, $duration);

		$this->assertCount(static::NUMBER_OF_POIS, $result);
	}

}
