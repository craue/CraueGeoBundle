<?php

namespace Craue\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="craue_geo_postalcode",
 * 	uniqueConstraints={@ORM\UniqueConstraint(name="postal_code_idx", columns={
 * 		"country", "postal_code"
 * 	})}
 * )
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2012 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class GeoPostalCode {

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="country", type="string", length=2, nullable=false)
	 * @Assert\NotBlank
	 */
	protected $country;

	/**
	 * @var string
	 * @ORM\Column(name="postal_code", type="string", length=20, nullable=false)
	 * @Assert\NotBlank
	 */
	protected $postalCode;

	/**
	 * @var double
	 * @ORM\Column(name="lat", type="decimal", precision=9, scale=6, nullable=false)
	 * @Assert\NotBlank
	 */
	protected $lat;

	/**
	 * @var double
	 * @ORM\Column(name="lng", type="decimal", precision=9, scale=6, nullable=false)
	 * @Assert\NotBlank
	 */
	protected $lng;

	public function getId() {
		return $this->id;
	}

	public function setCountry($country) {
		$this->country = $country;
	}

	public function getCountry() {
		return $this->country;
	}

	public function setPostalCode($postalCode) {
		$this->postalCode = $postalCode;
	}

	public function getPostalCode() {
		return $this->postalCode;
	}

	public function setLat($lat) {
		$this->lat = $lat;
	}

	public function getLat() {
		return $this->lat;
	}

	public function setLng($lng) {
		$this->lng = $lng;
	}

	public function getLng() {
		return $this->lng;
	}

	/**
	 * Get the distance in miles between two GeoPostalCodes
	 *
	 * Roughly based on notes from query based version and
	 * http://en.wikipedia.org/wiki/Great-circle_distance
	 *
	 * @return float
	 */
	public function getMilesTo(GeoPostalCode $other) {
		// Convert to radians
		$deltaLat = deg2rad($this->getLat() - $other->getLat());
		$deltaLng = deg2rad($this->getLng() - $other->getLng());
		$origLat  = deg2rad($this->getLat());
		$destLat  = deg2rad($other->getLat());

		return 3956 * 2 * asin(
			sqrt(
				pow(sin($deltaLat / 2), 2) +
				cos($origLat) *
				cos($destLat) *
				pow(sin($deltaLng / 2), 2)
			)
		);
	}
}
