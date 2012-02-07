<?php

namespace Craue\GeoBundle\Doctrine\Fixtures;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class GeonamesPostalCodeData implements FixtureInterface {

	protected $batchSize = 1000;

	protected function getRepository(ObjectManager $manager) {
		return $manager->getRepository(get_class(new GeoPostalCode()));
	}

	protected function clearPostalCodesTable(ObjectManager $manager) {
		foreach ($this->getRepository($manager)->findAll() as $entity) {
			$manager->remove($entity);
		}
		$manager->flush();
	}

	protected function addEntries(ObjectManager $manager, $filename) {
		$repo = $this->getRepository($manager);

		$currentBatchEntries = array();

		$fcontents = file($filename);
		for ($i = 0, $numLines = count($fcontents); $i < $numLines; ++$i) {
			$line = trim($fcontents[$i]);
			$arr = explode("\t", $line);

			// skip if no lat/lng values
			if (!array_key_exists(9, $arr) || !array_key_exists(10, $arr)) {
				continue;
			}

			$country = $arr[0];
			$postalCode = $arr[1];

			// skip duplicate entries in current batch
			if (in_array($country.'-'.$postalCode, $currentBatchEntries)) {
				continue;
			}

			// skip duplicate entries already persisted
			if ($repo->findOneBy(array('country' => $country, 'postalCode' => $postalCode)) !== null) {
				continue;
			}

			$entity = new GeoPostalCode();
			$entity->setCountry($country);
			$entity->setPostalCode($postalCode);
			$entity->setLat((float) $arr[9]);
			$entity->setLng((float) $arr[10]);
			$manager->persist($entity);
			$currentBatchEntries[] = $country.'-'.$postalCode;

			if ((($i + 1) % $this->batchSize) === 0) {
				$manager->flush();
				$manager->clear();
				$currentBatchEntries = array();
				echo '.'; // progress indicator
			}
		}
		echo "\n";
	}

}
