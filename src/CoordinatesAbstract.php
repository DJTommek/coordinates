<?php declare(strict_types=1);

namespace DJTommek\Coordinates;

use DJTommek\Coordinates\Exceptions\CoordinatesException;

/**
 * @property float $lat
 * @property float $lon
 */
class CoordinatesAbstract implements CoordinatesInterface, \JsonSerializable
{
	/**
	 * Regex for basic latitude coordinates validation from string
	 */
	public const RE_BASIC_LAT = '-?[0-9]{1,2}(?:\.[0-9]{1,99})?';

	/**
	 * Regex for basic longitude coordinates validation from string
	 */
	public const RE_BASIC_LON = '-?[0-9]{1,3}(?:\.[0-9]{1,99})?';

	/**
	 * Regex for basic lat,lon coordinates validation from string and ',' as divider.
	 */
	public const RE_BASIC = self::RE_BASIC_LAT . ',' . self::RE_BASIC_LON;

	public const NORTH = 'N';
	public const SOUTH = 'S';
	public const EAST = 'E';
	public const WEST = 'W';

	/**
	 * Earth radius in meters
	 */
	public const EARTH_RADIUS = 6_371_000;

	protected float $latInternal;
	protected float $lonInternal;

	/**
	 * @param float|int $lat Latitude coordinate in WGS-84 format
	 * @param float|int $lon Longitude coordinate in WGS-84 format
	 * @throws CoordinatesException
	 */
	public function __construct(mixed $lat, mixed $lon)
	{
		$this->setLatInternal($lat);
		$this->setLonInternal($lon);
	}

	public function getLat(): float
	{
		return $this->latInternal;
	}

	public function getLon(): float
	{
		return $this->lonInternal;
	}

	public function __get(string $name): float|null
	{
		return match ($name) {
			'lat' => $this->getLat(),
			'lon' => $this->getLon(),
			default => throw new \OutOfBoundsException(sprintf('Value "%s" does not exists or it is not accessible', $name)),
		};
	}

	public function getLatHemisphere(): string
	{
		return $this->latInternal >= 0 ? self::NORTH : self::SOUTH;
	}

	public function getLonHemisphere(): string
	{
		return $this->lonInternal >= 0 ? self::EAST : self::WEST;
	}

	/** Create new instance but return null if lat and/or lon are invalid */
	public static function safe(mixed $lat, mixed $lon): ?self
	{
		try {
			return new self($lat, $lon);
		} catch (CoordinatesException) {
			return null;
		}
	}

	public function key(): string
	{
		return sprintf('%F,%F', $this->latInternal, $this->lonInternal);
	}

	public function __toString(): string
	{
		return $this->key();
	}

	/**
	 * Calculates the great-circle distance between two points, with the Vincenty formula.
	 *
	 * @return float Distance between points in meters (same unit as self::EARTH_RADIUS)
	 * @author https://stackoverflow.com/a/10054282/3334403
	 */
	public function distance(CoordinatesInterface $coords): float
	{
		// convert from degrees to radians
		$latFrom = deg2rad($this->latInternal);
		$lonFrom = deg2rad($this->lonInternal);
		$latTo = deg2rad($coords->getLat());
		$lonTo = deg2rad($coords->getLon());

		$lonDelta = $lonTo - $lonFrom;
		$a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

		$angle = atan2(sqrt($a), $b);
		return $angle * self::EARTH_RADIUS;
	}

	public static function distanceLatLon(float $lat1, float $lon1, float $lat2, float $lon2): float
	{
		$location1 = new self($lat1, $lon1);
		$location2 = new self($lat2, $lon2);

		return $location1->distance($location2);
	}

	/**
	 * Check if input is valid latitude in WGS84 format. Should be float between -90 and +90
	 */
	public static function isLat(mixed $lat): bool
	{
		$latReal = $lat;
		switch (gettype($lat)) {
			case 'double':
				// correct value, do nothing
				break;
			case 'integer':
				$latReal = (float)$lat;
				break;
			case 'string':
				if (preg_match('/^' . self::RE_BASIC_LAT . '$/', $lat)) {
					$latReal = (float)$lat;
				}
				break;
			default:
				return false;
		}

		return ($latReal <= 90 && $latReal >= -90);
	}

	/**
	 * Check if input is valid longitude in WGS84 format. Should be float between -180 and +180
	 */
	public static function isLon(mixed $lon): bool
	{
		$lonReal = $lon;
		switch (gettype($lon)) {
			case 'double':
				// correct value, do nothing
				break;
			case 'integer':
				$lonReal = (float)$lon;
				break;
			case 'string':
				if (preg_match('/^' . self::RE_BASIC_LON . '$/', $lon)) {
					$lonReal = (float)$lon;
				}
				break;
			default:
				return false;
		}

		return ($lonReal <= 180 && $lonReal >= -180);
	}

	/**
	 * Safely create Coordinates object from format 'latitude,longitude' or return null
	 */
	public static function fromString(string $input, string $separator = ','): ?self
	{
		$coords = explode($separator, $input);
		if (count($coords) === 2) {
			return self::safe($coords[0], $coords[1]);
		}
		return null;
	}

	/**
	 * Check if point is inside of polygon
	 *
	 * @param array<array{float, float}> $polygon multi-array of coordinates, example: [[50.5,16.5], [51.5,16.5], [51.5,17.5], [50.5,17.5]]
	 * @author https://stackoverflow.com/a/18190354/3334403
	 */
	public function isInPolygon(array $polygon): bool
	{
		$c = 0;
		$p1 = $polygon[0];
		$n = count($polygon);

		for ($i = 1; $i <= $n; $i++) {
			$p2 = $polygon[$i % $n];
			if ($this->lonInternal > min($p1[1], $p2[1])
				&& $this->lonInternal <= max($p1[1], $p2[1])
				&& $this->latInternal <= max($p1[0], $p2[0])
				&& $p1[1] != $p2[1]) {
				$xinters = ($this->lonInternal - $p1[1]) * ($p2[0] - $p1[0]) / ($p2[1] - $p1[1]) + $p1[0];
				if ($p1[0] == $p2[0] || $this->latInternal <= $xinters) {
					$c++;
				}
			}
			$p1 = $p2;
		}
		// if the number of edges we passed through is even, then it's not in the poly.
		return $c % 2 != 0;
	}

	/**
	 * @return array{lat: float, lon: float}
	 */
	public function jsonSerialize(): array
	{
		return [
			'lat' => $this->getLat(),
			'lon' => $this->getLon(),
		];
	}

	/**
	 * @throws CoordinatesException
	 */
	protected function setLatInternal(mixed $lat): self
	{
		if (self::isLat($lat) === false) {
			throw new CoordinatesException('Latitude coordinate must be numeric between or equal from -90 to 90 degrees.');
		}
		$this->latInternal = floatval($lat);

		return $this;
	}

	/**
	 * @throws CoordinatesException
	 */
	protected function setLonInternal(mixed $lon): self
	{
		if (self::isLon($lon) === false) {
			throw new CoordinatesException('Longitude coordinate must be numeric between or equal from -180 to 180 degrees.');
		}
		$this->lonInternal = floatval($lon);

		return $this;
	}
}
