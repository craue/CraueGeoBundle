<?php

namespace Craue\GeoBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoPostalCode {

	protected int $id;

    #[Assert\NotBlank]
	protected string $country;

    #[Assert\NotBlank]
	protected string $postalCode;

    #[Assert\NotBlank]
	protected float $lat;

    #[Assert\NotBlank]
	protected float $lng;

	public function getId(): int
    {
		return $this->id;
	}

	public function setCountry(string $country): void
    {
		$this->country = $country;
	}

	public function getCountry(): string
    {
		return $this->country;
	}

	public function setPostalCode(string $postalCode): void
    {
		$this->postalCode = $postalCode;
	}

	public function getPostalCode(): string
    {
		return $this->postalCode;
	}

	public function setLat(float $lat): void
    {
		$this->lat = $lat;
	}

	public function getLat(): float
    {
		return $this->lat;
	}

	public function setLng(float $lng): void
    {
		$this->lng = $lng;
	}

	public function getLng(): float
    {
		return $this->lng;
	}

}
