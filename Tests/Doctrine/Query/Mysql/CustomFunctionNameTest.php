<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CustomFunctionNameTest extends IntegrationTestCase {

	/**
	 * Ensure that custom function names will be used to register the corresponding functions.
	 *
	 * @dataProvider dataCustomFunctionName
	 */
	public function testCustomFunctionName($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, array('environment' => 'customFunctionName_' . $platform, 'config' => $config));

		$this->assertSame(sprintf('Craue\GeoBundle\Doctrine\Query\%s\GeoDistance', ucfirst($platform)),
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE'));
		$this->assertSame(sprintf('Craue\GeoBundle\Doctrine\Query\%s\GeoDistanceByPostalCode', ucfirst($platform)),
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE_BY_POSTAL_CODE'));
	}

	public function dataCustomFunctionName() {
		return self::duplicateTestDataForEachPlatform(array(
			array(),
		), 'config_customFunctionName.yml');
	}

	/**
	 * Ensure that a user-defined function will override the bundle-defined default one to preserve BC.
	 *
	 * @dataProvider dataOverrideFunction
	 */
	public function testOverrideFunction($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, array('environment' => 'overrideFunction_' . $platform, 'config' => $config));

		$this->assertSame('Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance',
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('MY_GEO_DISTANCE'));
	}

	public function dataOverrideFunction() {
		return self::duplicateTestDataForEachPlatform(array(
			array(),
		), 'config_overrideFunction.yml');
	}

}
