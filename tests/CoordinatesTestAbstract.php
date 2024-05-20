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
		$this->assertSame($coords->getLatLon(), $keyExpected);
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

	protected function abstractTestDistanceBasic(
		Coordinates|CoordinatesImmutable $prague,
		Coordinates|CoordinatesImmutable $berlin,
	): void {
		$this->assertSame(
			'Prague: 50.087500,14.421300',
			'Prague: ' . $prague,
		);

		$this->assertSame(
			'Berlin: 52.518611,13.408333',
			'Berlin: ' . $berlin,
		);
		$distance = $prague->distance($berlin);
		$this->assertSame(
			'Distance between Prague and Berlin is 279 km',
			sprintf('Distance between Prague and Berlin is %d km', $distance / 1000),
		);
	}

	protected function abstractTestDistance(
		float $expectedDistance,
		Coordinates|CoordinatesImmutable $coords1,
		Coordinates|CoordinatesImmutable $coords2,
	): void {
		$distance1 = $coords1->distance($coords2);
		$this->assertEqualsWithDelta($expectedDistance, $distance1, 0.000_000_01);

		$distance2 = $coords2->distance($coords1);
		$this->assertEqualsWithDelta($expectedDistance, $distance2, 0.000_000_01);
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

			// multi-character separator
			['12.3456789, -98.7654321', 12.3456789, -98.7654321, ', '],
			['-23.456, 45.678', -23.456, 45.678, ', '],
			['-1.234567, 11.111111', -1.234567, 11.111111, ', '],

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

			['49.885617abcd,14.044381'],
			['49.885617,14.044381abcd'],
			['abcd49.885617,14.044381'],
			['49.885617,abcd14.044381'],
			['abcd49.885617abcd,14.044381'],
			['49.885617,abcd14.044381abcd'],

			// multi-character separator separator
			['1.234567__0.123456', '_'],
			['1.234567__0.123456', '___'],

			// should be used multi-character separator
			['12.3456789, -98.7654321'],
			['-23.456, 45.678'],
			['-1.234567, 11.111111'],

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

	/**
	 * Invalid types or values, that can't be latitude nor longitude
	 *
	 * @return array<mixed>
	 */
	public static function invalidCoordinateTypeProvider(): array
	{
		return [
			['abc'],
			['12abc'],
			[''],
			[' '],
			[' 12'],
			['12 '],
			[' 12 '],
			[null],
			[[]],
			[[-1]],
			[true],
			[false],
		];
	}

	/**
	 * @return array<mixed>
	 */
	public static function invalidLatitudesProvider(): array
	{
		$invalidTypes = self::invalidCoordinateTypeProvider();
		$outOfRange = [
			// Out of range
			[149.885617],
			[-149.885617],

			// Barely out of range (float)
			[90.0001],
			[-90.0001],
			[90.00000000000001],
			[-90.00000000000001],

			// Barely out of range (string as float)
			['90.0001'],
			['-90.0001'],
			['90.00000000000001'],
			['-90.00000000000001'],

			// Barely out of range (int)
			[91],
			[-91],

			// Barely out of range (string as int)
			['91'],
			['-91'],
		];

		return array_merge($invalidTypes, $outOfRange);
	}

	/**
	 * @return array<mixed>
	 */
	public static function invalidLongitudesProvider(): array
	{
		$invalidTypes = self::invalidCoordinateTypeProvider();
		$outOfRange = [
			// Out of range
			[339.885617],
			[-200.885617],

			// Barely out of range (float)
			[180.0001],
			[-180.0001],
			[180.0000000000001],
			[-180.0000000000001],

			// Barely out of range (string as float)
			['180.0001'],
			['-180.0001'],
			['180.0000000000001'],
			['-180.0000000000001'],

			// Barely out of range (int)
			[181],
			[-181],

			// Barely out of range (string as int)
			['181'],
			['-181'],
		];

		return array_merge($invalidTypes, $outOfRange);
	}

	/**
	 * [
	 *   [distance, lat1, lon1, lat2, lon2]
	 * ]
	 *
	 * @return array<array{float, float, float, float, float}>
	 */
	public static function distanceProvider(): array
	{
		return [
			[42.16747601866312, 50.087725, 14.4211267, 50.0873667, 14.4213203],
			[1_825.0239867033586, 36.6323425, -121.9340617, 36.6219297, -121.9182533],
			[4_532.050463078125, 50.08904, 14.42890, 50.07406, 14.48797],
			[11_471_646.428581407, -50.08904, 14.42890, 50.07406, -14.48797],

			[0, 0, 0, 0, 0],
			[20015086.79602057, 0, 0, 0, 180], // Antipodal points
			[559120.5770615528, 37.7749, -122.4194, 34.0522, -118.2437],
			[343556.060341042, 51.5074, -0.1278, 48.8566, 2.3522],
			[732290.7614908506, -33.8675, 151.207, -27.4698, 153.0251],
		];
	}

	/**
	 * @return array<string, array{array<array{float, float}>, float, float}>
	 */
	public static function polygonsProvider(): array
	{
		$pragueCastlePolygon = [
			[50.089086, 14.392272],
			[50.091675, 14.397796],
			[50.089847, 14.404232],
			[50.085563, 14.406616],
			[50.081262, 14.402157],
			[50.080157, 14.397011],
			[50.081960, 14.391940],
		];
		$eiffelTowerPolygon = [
			[48.859166, 2.292573],
			[48.860540, 2.295058],
			[48.858995, 2.297680],
			[48.857114, 2.297600],
			[48.856421, 2.294914],
			[48.857979, 2.292185],
		];
		$newYorkCentralPark = [
			[40.768094, -73.981702],
			[40.770659, -73.974510],
			[40.765714, -73.971595],
			[40.763204, -73.977857],
			[40.766249, -73.981175],
		];
		$grandCanyon = [
			[36.057977, -112.146882],
			[36.057804, -112.130991],
			[36.063665, -112.129122],
			[36.067847, -112.142792],
			[36.062482, -112.150881],
		];

		return [
			'Prague Castle' => [$pragueCastlePolygon, 50.087738, 14.402767],
			'Eiffel Tower' => [$eiffelTowerPolygon, 48.858373, 2.294554],
			'New York Central Park' => [$newYorkCentralPark, 40.767588, -73.977225],
			'Grand Canyon' => [$grandCanyon, 36.061272, -112.139519],
		];
	}
}
