<?php

namespace Craue\GeoBundle\Tests\Entity;

use Craue\GeoBundle\Entity\GeoPostalCode;

class GeoPostalCodeTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider sampleDistances
	 */
	public function testGetMilesTo($origLat, $origLng, $destLat, $destLng, $expected) {
		$orig = new GeoPostalCode();
		$orig->setLat($origLat);
		$orig->setLng($origLng);

		$dest = new GeoPostalCode();
		$dest->setLat($destLat);
		$dest->setLng($destLng);

		$this->assertEquals($expected, $orig->getMilesTo($dest), '', 0.5);
	}

	/**
	 * @dataProvider sampleDistances
	 */
	public function testGetKmTo($origLat, $origLng, $destLat, $destLng, $expected) {
		$orig = new GeoPostalCode();
		$orig->setLat($origLat);
		$orig->setLng($origLng);

		$dest = new GeoPostalCode();
		$dest->setLat($destLat);
		$dest->setLng($destLng);

		$this->assertEquals($expected * 1.609344, $orig->getKmTo($dest), '', 0.5);
	}

	public function sampleDistances() {
		return array(
			array(32.9697, -96.8032, 29.4678, -98.5350, 262.67779380543),
			array(43.3315, -89.0271, 33.8631, -84.5382, 697.34581863172),
		);
	}
}
