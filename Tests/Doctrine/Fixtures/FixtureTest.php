<?php

namespace Craue\GeoBundle\Tests\Doctrine\Fixtures;

use Craue\GeoBundle\Tests\Doctrine\Fixtures\CraueGeo\PuertoRicoGeonamesPostalCodeData;
use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class FixtureTest extends IntegrationTestCase {

	public function testImport() {
		// [A] add some data which is meant to be removed by importing new data
		static::persistGeoPostalCode('DE', '14473', 52.392759, 13.065135);

		// import new data using the fixture
		$fixture = new PuertoRicoGeonamesPostalCodeData();
		ob_start();
		$fixture->load(static::getEntityManager());
		$output = ob_get_clean();
		$this->assertEquals(" 177\n", $output);

		// [A] verify that old data has been removed
		$this->assertCount(0, static::getRepo()->findBy(array('country' => 'DE')));

		// verify that new data was imported as expected
		$this->assertCount(177, static::getRepo()->findAll());
	}

}
