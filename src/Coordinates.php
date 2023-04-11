<?php declare(strict_types=1);

namespace DJTommek\Coordinates;

use DJTommek\Coordinates\Exceptions\CoordinatesException;

class Coordinates extends CoordinatesAbstract
{
	/**
	 * Change latitude
	 *
	 * @throws CoordinatesException
	 */
	public function setLat(mixed $lat): self
	{
		$this->setLatInternal($lat);
		return $this;
	}

	/**
	 * Change longitude
	 *
	 * @throws CoordinatesException
	 */
	public function setLon(mixed $lon): self
	{
		$this->setLonInternal($lon);
		return $this;
	}

	public function __set(string $name, mixed $value): void
	{
		match ($name) {
			'lat' => $this->setLat($value),
			'lon' => $this->setLon($value),
			default => throw new \OutOfBoundsException(sprintf('Value "%s" does not exists or it is not accessible', $name)),
		};
	}
}
