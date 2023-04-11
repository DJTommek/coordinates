<?php declare(strict_types=1);

namespace DJTommek\Coordinates;

class CoordinatesImmutable extends CoordinatesAbstract
{
	/**
	 * Return cloned object with updated latitude
	 */
	public function withLat(mixed $lat): self
	{
		return new self($lat, $this->getLon());
	}

	/**
	 * Return cloned object with updated longitude
	 */
	public function withLon(mixed $lon): self
	{
		return new self($this->getLat(), $lon);
	}
}
