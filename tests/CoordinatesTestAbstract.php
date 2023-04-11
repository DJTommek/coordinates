<?php declare(strict_types=1);

namespace DJTommek\Coordinates\Tests;

use DJTommek\Coordinates\Coordinates;
use DJTommek\Coordinates\CoordinatesImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @TODO Test isInPolygon()
 */
class CoordinatesTestAbstract extends TestCase
{
	protected static function randomLat(): float
	{
		return rand(-89_999_999, 89_999_999) / 1_000_000;
	}

	protected static function randomLon(): float
	{
		return rand(-179_999_999, 179_999_999) / 1_000_000;
	}

	/**
	 * Hacky way how to run test multiple times (in this case 100x times)
	 */
	public static function iterator100Provider(): \Generator
	{
	    for ($i = 0; $i < 100; $i++) {
			yield [];
	    }
	}

	protected function abstractTestCoordinates(Coordinates|CoordinatesImmutable $coords, string $keyExpected): void
	{
		$this->assertSame($coords->key(), $keyExpected);
		$this->assertSame((string)$coords, $keyExpected);

		$this->assertSame($coords->lat, $coords->getLat());
		$this->assertSame($coords->lon, $coords->getLon());

		$expectedJson = [
			'lat' => $coords->lat,
			'lon' => $coords->lon,
		];
		$this->assertSame($expectedJson, $coords->jsonSerialize());

		$expectedJsonString = sprintf('{"lat":%s,"lon":%s}', $coords->lat, $coords->lon);
		$realJsonString = json_encode($coords);

		$this->assertSame($expectedJsonString, $realJsonString);

	}

	/**
	 * @return array<array{float, float, string}>
	 */
	public static function validCoordinatesProvider(): array
	{
		return [
			[49.885617, 14.044381, '49.885617,14.044381'],
			[-49.885617, 14.044381, '-49.885617,14.044381'],
			[49.885617, -14.044381, '49.885617,-14.044381'],
			[-49.885617, -14.044381, '-49.885617,-14.044381'],

			// Rounding
			[41.2947078, 174.8344972, '41.294708,174.834497'],
			[-53.7930356, -67.6845906, '-53.793036,-67.684591'],

			[1.234567, 0.123456, '1.234567,0.123456'],

			[0, 0, '0.000000,0.000000'],
			[0.0, 0.0, '0.000000,0.000000'],

			// Absolute borders
			[90, 180, '90.000000,180.000000'],
			[-90, 180, '-90.000000,180.000000'],
			[90, -180, '90.000000,-180.000000'],
			[-90, -180, '-90.000000,-180.000000'],

			// Strings as input
			['49.885617', '14.044381', '49.885617,14.044381'],
			['-49.885617', '14.044381', '-49.885617,14.044381'],
			['49.885617', '-14.044381', '49.885617,-14.044381'],
			['-49.885617', '-14.044381', '-49.885617,-14.044381'],
			['0', '0', '0.000000,0.000000'],
			['0.0', '0.0', '0.000000,0.000000'],
		];
	}

	/**
	 * @return array<array{string, float, float, ?string}>
	 */
	public static function validCoordinatesFromStringProvider(): array
	{
		return [
			['49.885617,14.044381', 49.885617, 14.044381],
			['-49.885617,14.044381', -49.885617, 14.044381],
			['49.885617,-14.044381', 49.885617, -14.044381],
			['-49.885617,-14.044381', -49.885617, -14.044381],

			['41.294708,174.834497', 41.294708, 174.834497],
			['-53.793036,-67.684591', -53.793036, -67.684591],

			['1.234567,0.123456', 1.234567, 0.123456],
			['1.234567_0.123456', 1.234567, 0.123456, '_'],

			['0,0', 0, 0],
			['0.000000,0.000000', 0.0, 0.0],

			// multi-character separator separator
			['1.234567__0.123456', 1.234567, 0.123456, '__'],
			['1.234_abcd_0.123', 1.234, 0.123, '_abcd_'],
		];
	}

	/**
	 * @return array<array{string, ?string}>
	 */
	public static function invalidCoordinatesFromStringProvider(): array
	{
		return [
			['49.885617_14.044381'],
			['49.885617_14.044381', ','],
			['49.885617,14.044381', '_'],

			// multi-character separator separator
			['1.234567__0.123456', '_'],
			['1.234567__0.123456', '___'],

			['some random text'],
			['valid coords (49.885617,14.044381) but inside text'],
			['95.885617,14.044381'], // lat out of bounds
			['1.885617,180.044381'], // lon out of bounds
		];

	}

	/**
	 * Valid input types but invalid coordinates
	 *
	 * @return array<array{float, float}>
	 */
	public static function outOfRangeCoordinatesProvider(): array
	{
		return [
			[149.885617, 14.044381],
			[-149.885617, 14.044381],

			// Barely out of range (float)
			[90.0001, 180],
			[90, 180.0001],

			[-90.0001, 180],
			[-90, 180.0001],

			[90.0001, -180],
			[90, -180.0001],

			[-90.0001, -180],
			[-90, -180.0001],

			[90.00000000000001, 180],
			[90.001, 180.00000000000001],

			// Barely out of range (string as float)
			['90.00000000000001', 180],
			[90.001, '180.00000000000001'],

			// Barely out of range (int)
			[91, 0],
			[-91, 0],
			[0, 181],
			[0, -181],

			// Barely out of range (string as int)
			['91', 0],
			['-91', 0],
			[0, '181'],
			[0, '-181'],

		];
	}
}
