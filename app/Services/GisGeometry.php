<?php

namespace App\Services;

/** Shared GeoJSON geometry validation for OPD forms, imports, and maps. */
class GisGeometry
{
    public static function isValid(mixed $geometry, string $expected = 'any'): bool
    {
        if (! is_array($geometry) || ! is_string($geometry['type'] ?? null)) return false;
        $types = [
            'Point' => 'point', 'MultiPoint' => 'point',
            'LineString' => 'linestring', 'MultiLineString' => 'linestring',
            'Polygon' => 'polygon', 'MultiPolygon' => 'polygon',
        ];
        $type = $geometry['type'];
        if (! isset($types[$type]) || ($expected !== 'any' && $types[$type] !== $expected)) return false;
        $vertices = 0;
        $point = static function (mixed $coordinate) use (&$vertices): bool {
            if (! is_array($coordinate) || ! array_is_list($coordinate) || count($coordinate) < 2 || count($coordinate) > 3) return false;
            foreach ($coordinate as $number) if (! is_numeric($number) || ! is_finite((float) $number)) return false;
            if (abs((float) $coordinate[0]) > 180 || abs((float) $coordinate[1]) > 90) return false;
            return ++$vertices <= 20000;
        };
        $line = static fn (mixed $coordinates): bool => is_array($coordinates) && array_is_list($coordinates) && count($coordinates) >= 2 && self::every($coordinates, $point);
        $ring = static function (mixed $coordinates) use ($point): bool {
            if (! is_array($coordinates) || ! array_is_list($coordinates) || count($coordinates) < 4 || ! self::every($coordinates, $point)) return false;
            $first = $coordinates[0]; $last = $coordinates[count($coordinates) - 1];
            return is_array($first) && is_array($last) && $first[0] == $last[0] && $first[1] == $last[1];
        };
        $polygon = static fn (mixed $rings): bool => is_array($rings) && array_is_list($rings) && count($rings) > 0 && self::every($rings, $ring);
        $coordinates = $geometry['coordinates'] ?? null;
        return match ($type) {
            'Point' => $point($coordinates),
            'MultiPoint' => is_array($coordinates) && array_is_list($coordinates) && count($coordinates) > 0 && self::every($coordinates, $point),
            'LineString' => $line($coordinates),
            'MultiLineString' => is_array($coordinates) && array_is_list($coordinates) && count($coordinates) > 0 && self::every($coordinates, $line),
            'Polygon' => $polygon($coordinates),
            'MultiPolygon' => is_array($coordinates) && array_is_list($coordinates) && count($coordinates) > 0 && self::every($coordinates, $polygon),
            default => false,
        };
    }

    private static function every(array $items, callable $callback): bool
    {
        foreach ($items as $item) if (! $callback($item)) return false;
        return true;
    }
}
