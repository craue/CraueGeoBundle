<?php

namespace Craue\GeoBundle\Doctrine\Fixtures;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

	/**
	 * @author Christian Raue <christian.raue@gmail.com>
	 * @copyright 2011-2022 Christian Raue
	 * @license http://opensource.org/licenses/mit-license.php MIT License
	 */
	abstract class GeonamesPostalCodeData implements FixtureInterface {

        protected int $batchSize = 1000;

		protected function getRepository(ObjectManager $manager): ObjectRepository
        {
            return $manager->getRepository(GeoPostalCode::class);
		}

		protected function clearPostalCodesTable(ObjectManager $manager): void
        {
            foreach ($this->getRepository($manager)->findAll() as $entity) {
                $manager->remove($entity);
            }
            $manager->flush();
		}

		/**
		 * @return int Number of entries actually added.
		 */
		protected function addEntries(ObjectManager $manager, string $filename): int
        {
            $repo = $this->getRepository($manager);

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

        public function load(ObjectManager $manager): void {}
}
