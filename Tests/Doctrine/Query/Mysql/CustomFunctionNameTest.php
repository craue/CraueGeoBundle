<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance;
use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CustomFunctionNameTest extends IntegrationTestCase {

	/**
	 * Ensure that custom function names will be used to register the corresponding functions.
	 *
	 * @dataProvider dataCustomFunctionName
	 */
	public function testCustomFunctionName($platform, $config, $requiredExtension): void
    {
		$this->initClient($requiredExtension, ['environment' => 'customFunctionName_' . $platform, 'config' => $config]);

		$this->assertSame(sprintf('Craue\GeoBundle\Doctrine\Query\%s\GeoDistance', ucfirst($platform)),
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE'));
		$this->assertSame(sprintf('Craue\GeoBundle\Doctrine\Query\%s\GeoDistanceByPostalCode', ucfirst($platform)),
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE_BY_POSTAL_CODE'));
	}

	public static function dataCustomFunctionName(): array
    {
		return self::duplicateTestDataForEachPlatform([
			[],
		], 'config_customFunctionName.yml');
	}

	/**
	 * Ensure that a user-defined function will override the bundle-defined default one to preserve BC.
	 *
	 * @dataProvider dataOverrideFunction
	 */
	public function testOverrideFunction($platform, $config, $requiredExtension): void
    {
		$this->initClient($requiredExtension, ['environment' => 'overrideFunction_' . $platform, 'config' => $config]);

		$this->assertSame(GeoDistance::class, $this->getEntityManager()->getConfiguration()->getCustomNumericFunction('MY_GEO_DISTANCE'));
	}

	public static function dataOverrideFunction(): array
    {
		return self::duplicateTestDataForEachPlatform([
			[],
		], 'config_overrideFunction.yml');
	}

}
