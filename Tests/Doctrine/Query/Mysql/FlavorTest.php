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
class FlavorTest extends IntegrationTestCase {

	/**
	 * Ensure that only valid values can be used for the flavor.
	 *
	 * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
	 * @expectedExceptionMessage The value "invalid" is not allowed for path "craue_geo.flavor". Permissible values: "none", "mysql", "postgresql"
	 */
	public function testFlavorInvalid() {
		$this->initClient(null, array('environment' => 'invalidFlavor', 'config' => array('config.yml', 'config_flavor_invalid.yml')));
	}

	/**
	 * Ensure that function GEO_DISTANCE is not registered when using flavor 'none'.
	 *
	 * @expectedException \Doctrine\ORM\Query\QueryException
	 * @expectedExceptionMessage Error: Expected known function, got 'GEO_DISTANCE'
	 */
	public function testFlavorNone_geoDistance() {
		$this->initClient(null, array('environment' => 'flavorNone', 'config' => array('config.yml', 'config_flavor_none.yml')));

		$this->getPoisPerGeoDistance(52.1, 13.1, 1);
	}

	/**
	 * Ensure that function GEO_DISTANCE_BY_POSTAL_CODE is not registered when using flavor 'none'.
	 *
	 * @expectedException \Doctrine\ORM\Query\QueryException
	 * @expectedExceptionMessage Error: Expected known function, got 'GEO_DISTANCE_BY_POSTAL_CODE'
	 */
	public function testFlavorNone_geoDistanceByPostalCode() {
		$this->initClient(null, array('environment' => 'flavorNone', 'config' => array('config.yml', 'config_flavor_none.yml')));

		$this->getPoisPerGeoDistanceByPostalCode('DE', '123');
	}

}
