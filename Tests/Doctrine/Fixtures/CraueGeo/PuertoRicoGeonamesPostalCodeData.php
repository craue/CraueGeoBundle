<?php

namespace Craue\GeoBundle\Tests\Doctrine\Fixtures\CraueGeo;

use Craue\GeoBundle\Doctrine\Fixtures\GeonamesPostalCodeData;
use Doctrine\Common\Persistence\ObjectManager as LegacyObjectManager;
use Doctrine\Persistence\ObjectManager;

/**
 * @internal
 */
abstract class BasePuertoRicoGeonamesPostalCodeData extends GeonamesPostalCodeData {

	// TODO update README with Doctrine\Persistence\ObjectManager type-hint as soon as doctrine/persistence >= 2.0 is required
	final protected function _load($manager): void
    {
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

// TODO revert to one clean class definition as soon as doctrine/persistence >= 2.0 is required
if (interface_exists(ObjectManager::class)) {
	/**
	 * @author Christian Raue <christian.raue@gmail.com>
	 * @copyright 2011-2022 Christian Raue
	 * @license http://opensource.org/licenses/mit-license.php MIT License
	 */
	class PuertoRicoGeonamesPostalCodeData extends BasePuertoRicoGeonamesPostalCodeData {
		/**
		 * {@inheritDoc}
		 */
		public function load(ObjectManager $manager): void {
			$this->_load($manager);
		}
	}
} else {
	/**
	 * @author Christian Raue <christian.raue@gmail.com>
	 * @copyright 2011-2022 Christian Raue
	 * @license http://opensource.org/licenses/mit-license.php MIT License
	 */
	class PuertoRicoGeonamesPostalCodeData extends BasePuertoRicoGeonamesPostalCodeData {
		/**
		 * {@inheritDoc}
		 */
		public function load(LegacyObjectManager $manager): void {
			$this->_load($manager);
		}
	}
}
