<?php

namespace Craue\GeoBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoPostalCode {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 * @Assert\NotBlank
	 */
	protected $country;

	/**
	 * @var string
	 * @Assert\NotBlank
	 */
	protected $postalCode;

	/**
	 * @var double
	 * @Assert\NotBlank
	 */
	protected $lat;

	/**
	 * @var double
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

}
