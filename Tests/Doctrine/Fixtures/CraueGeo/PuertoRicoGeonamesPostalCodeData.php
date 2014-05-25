<?php

namespace Craue\GeoBundle\Tests\Doctrine\Fixtures\CraueGeo;

use Craue\GeoBundle\Doctrine\Fixtures\GeonamesPostalCodeData;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class PuertoRicoGeonamesPostalCodeData extends GeonamesPostalCodeData {

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager) {
		$this->clearPostalCodesTable($manager);

		$zip = new \ZipArchive();
		$res = $zip->open(__DIR__.'/PR.zip');
		if ($res === true) {
			$zip->extractTo(__DIR__);
			$zip->close();
			$this->addEntries($manager, __DIR__.'/PR.txt');
			unlink(__DIR__.'/PR.txt');
			unlink(__DIR__.'/readme.txt');
		}
	}

}
