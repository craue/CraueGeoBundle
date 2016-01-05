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
class CustomFunctionNameTest extends IntegrationTestCase {

	/**
	 * Ensure that custom function names will be used to register the corresponding functions.
	 */
	public function testCustomFunctionName() {
		$this->initClient(array('environment' => 'customFunctionName', 'config' => 'config_customFunctionName.yml'));
		$this->assertSame('Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance',
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE'));
		$this->assertSame('Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistanceByPostalCode',
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('CRAUE_GEO_DISTANCE_BY_POSTAL_CODE'));
	}

	/**
	 * Ensure that a user-defined function will override the bundle-defined default one to preserve BC.
	 */
	public function testOverrideFunction() {
		$this->initClient(array('environment' => 'overrideFunction', 'config' => 'config_overrideFunction.yml'));
		$this->assertSame('Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance',
				$this->getEntityManager()->getConfiguration()->getCustomNumericFunction('MY_GEO_DISTANCE'));
	}

}
