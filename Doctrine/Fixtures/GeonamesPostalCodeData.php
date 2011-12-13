<?php

namespace Craue\GeoBundle\Doctrine\Fixtures;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class GeonamesPostalCodeData implements FixtureInterface {

	protected $batchSize = 1000;

	protected function getRepository(EntityManager $em) {
		return $em->getRepository(get_class(new GeoPostalCode()));
	}

	protected function clearPostalCodesTable(EntityManager $em) {
		foreach ($this->getRepository($em)->findAll() as $entity) {
			$em->remove($entity);
		}
		$em->flush();
	}

	protected function addEntries(EntityManager $em, $filename) {
		$repo = $this->getRepository($em);

		$currentBatchEntries = array();

		$fcontents = file($filename);
		for ($i = 0; $i < sizeof($fcontents); ++$i) {
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
			$em->persist($entity);
			$currentBatchEntries[] = $country.'-'.$postalCode;

			if ((($i + 1) % $this->batchSize) === 0) {
				$em->flush();
				$em->clear();
				$currentBatchEntries = array();
				echo '.'; // progress indicator
			}
		}
	}

}
