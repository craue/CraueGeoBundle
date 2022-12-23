<?php

namespace Craue\GeoBundle\Doctrine\Fixtures;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager as LegacyObjectManager;
use Doctrine\Common\Persistence\ObjectRepository as LegacyObjectRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

/**
 * @internal
 */
abstract class BaseGeonamesPostalCodeData implements FixtureInterface {

	protected int $batchSize = 1000;

	// TODO remove as soon as doctrine/persistence >= 2.0 is required
	final protected function _getRepository($manager) {
		return $manager->getRepository(GeoPostalCode::class);
	}

	// TODO remove as soon as doctrine/persistence >= 2.0 is required
	final protected function _clearPostalCodesTable($manager): void
    {
		foreach ($this->_getRepository($manager)->findAll() as $entity) {
			$manager->remove($entity);
		}
		$manager->flush();
	}

	// TODO remove as soon as doctrine/persistence >= 2.0 is required
	final protected function _addEntries($manager, $filename): int
    {
		$repo = $this->_getRepository($manager);

		$entriesAdded = 0;
		$currentBatchEntries = [];

		$fcontents = file($filename);
        foreach ($fcontents as $i => $iValue) {
            $line = trim($iValue);
            $arr = explode("\t", $line);

            // skip if no lat/lng values
            if (!array_key_exists(9, $arr) || !array_key_exists(10, $arr)) {
                continue;
            }

            $country = $arr[0];
            $postalCode = $arr[1];

            // skip duplicate entries in current batch
            if (in_array($country.'-'.$postalCode, $currentBatchEntries, true)) {
                continue;
            }

            // skip duplicate entries already persisted
            if ($repo->findOneBy(['country' => $country, 'postalCode' => $postalCode]) !== null) {
                continue;
            }

            $entity = new GeoPostalCode();
            $entity->setCountry($country);
            $entity->setPostalCode($postalCode);
            $entity->setLat((float) $arr[9]);
            $entity->setLng((float) $arr[10]);
            $manager->persist($entity);

            ++$entriesAdded;
            $currentBatchEntries[] = $country.'-'.$postalCode;

            if ((($i + 1) % $this->batchSize) === 0) {
                $manager->flush();
                $manager->clear();
                $currentBatchEntries = [];
                echo '.'; // progress indicator
            }
        }

        $manager->flush(); // Flush for the last batch, which doesn't reach the batch size in most cases. (fixes #2)

		echo ' ', $entriesAdded, "\n";

		return $entriesAdded;
	}

}

// TODO revert to one clean class definition as soon as doctrine/persistence >= 2.0 is required
if (interface_exists(ObjectManager::class)) {
	/**
	 * @author Christian Raue <christian.raue@gmail.com>
	 * @copyright 2011-2022 Christian Raue
	 * @license http://opensource.org/licenses/mit-license.php MIT License
	 */
	abstract class GeonamesPostalCodeData extends BaseGeonamesPostalCodeData {
		/**
		 * @param ObjectManager $manager
		 * @return ObjectRepository
		 */
		protected function getRepository(ObjectManager $manager): ObjectRepository
        {
			return $this->_getRepository($manager);
		}

		/**
		 * @param ObjectManager $manager
		 */
		protected function clearPostalCodesTable(ObjectManager $manager): void
        {
			$this->_clearPostalCodesTable($manager);
		}

		/**
		 * @return int Number of entries actually added.
		 */
		protected function addEntries(ObjectManager $manager, string $filename): int
        {
			return $this->_addEntries($manager, $filename);
		}
	}
} else {
	/**
	 * @author Christian Raue <christian.raue@gmail.com>
	 * @copyright 2011-2022 Christian Raue
	 * @license http://opensource.org/licenses/mit-license.php MIT License
	 */
	abstract class GeonamesPostalCodeData extends BaseGeonamesPostalCodeData {
		/**
		 * @param LegacyObjectManager $manager
		 * @return LegacyObjectRepository
		 */
		protected function getRepository(LegacyObjectManager $manager): LegacyObjectRepository
        {
			return $this->_getRepository($manager);
		}

		/**
		 * @param LegacyObjectManager $manager
		 */
		protected function clearPostalCodesTable(LegacyObjectManager $manager): void
        {
			$this->_clearPostalCodesTable($manager);
		}

		/**
		 * @return int Number of entries actually added.
		 */
		protected function addEntries(LegacyObjectManager $manager, string $filename): int
        {
			return $this->_addEntries($manager, $filename);
		}
	}
}
