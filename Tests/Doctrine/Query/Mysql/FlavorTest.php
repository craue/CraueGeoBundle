<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;
use Doctrine\ORM\Query\QueryException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class FlavorTest extends IntegrationTestCase {

	/**
	 * Ensure that only valid values can be used for the flavor.
	 */
	public function testFlavorInvalid() {
		$this->expectException(InvalidConfigurationException::class);
		$this->expectExceptionMessage('The value "invalid" is not allowed for path "craue_geo.flavor". Permissible values: "none", "mysql", "postgresql"');

		$this->initClient(null, ['environment' => 'invalidFlavor', 'config' => ['config.yml', 'config_flavor_invalid.yml']]);
	}

	/**
	 * Ensure that function GEO_DISTANCE is not registered when using flavor 'none'.
	 */
	public function testFlavorNone_geoDistance() {
		$this->initClient(null, ['environment' => 'flavorNone', 'config' => ['config.yml', 'config_flavor_none.yml']]);

		$this->expectException(QueryException::class);
		$this->expectExceptionMessage("Error: Expected known function, got 'GEO_DISTANCE'");

		$this->getPoisPerGeoDistance(52.1, 13.1, 1);
	}

	/**
	 * Ensure that function GEO_DISTANCE_BY_POSTAL_CODE is not registered when using flavor 'none'.
	 */
	public function testFlavorNone_geoDistanceByPostalCode() {
		$this->initClient(null, ['environment' => 'flavorNone', 'config' => ['config.yml', 'config_flavor_none.yml']]);

		$this->expectException(QueryException::class);
		$this->expectExceptionMessage("Error: Expected known function, got 'GEO_DISTANCE_BY_POSTAL_CODE'");

		$this->getPoisPerGeoDistanceByPostalCode('DE', '123');
	}

}
