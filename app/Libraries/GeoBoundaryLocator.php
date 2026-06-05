<?php

namespace App\Libraries;

class GeoBoundaryLocator
{
    public function containsPoint(string|array|null $geojson, float $latitude, float $longitude): bool
    {
        if ($geojson === null || $geojson === '') {
            return false;
        }

        $decoded = is_array($geojson) ? $geojson : json_decode($geojson, true);
        if (!is_array($decoded)) {
            return false;
        }

        return $this->containsInNode($decoded, $latitude, $longitude);
    }

    protected function containsInNode(array $node, float $latitude, float $longitude): bool
    {
        $type = strtolower((string) ($node['type'] ?? ''));

        return match ($type) {
            'featurecollection' => $this->containsInFeatureCollection($node, $latitude, $longitude),
            'feature' => $this->containsInNode((array) ($node['geometry'] ?? []), $latitude, $longitude),
            'polygon' => $this->polygonContainsPoint((array) ($node['coordinates'] ?? []), $latitude, $longitude),
            'multipolygon' => $this->multiPolygonContainsPoint((array) ($node['coordinates'] ?? []), $latitude, $longitude),
            default => false,
        };
    }

    protected function containsInFeatureCollection(array $collection, float $latitude, float $longitude): bool
    {
        foreach ((array) ($collection['features'] ?? []) as $feature) {
            if (is_array($feature) && $this->containsInNode($feature, $latitude, $longitude)) {
                return true;
            }
        }

        return false;
    }

    protected function multiPolygonContainsPoint(array $polygons, float $latitude, float $longitude): bool
    {
        foreach ($polygons as $polygon) {
            if (is_array($polygon) && $this->polygonContainsPoint($polygon, $latitude, $longitude)) {
                return true;
            }
        }

        return false;
    }

    protected function polygonContainsPoint(array $rings, float $latitude, float $longitude): bool
    {
        if ($rings === []) {
            return false;
        }

        $outerRing = $rings[0] ?? [];
        if (!$this->pointInRing($outerRing, $latitude, $longitude)) {
            return false;
        }

        foreach (array_slice($rings, 1) as $holeRing) {
            if ($this->pointInRing((array) $holeRing, $latitude, $longitude)) {
                return false;
            }
        }

        return true;
    }

    protected function pointInRing(array $ring, float $latitude, float $longitude): bool
    {
        $inside = false;
        $count = count($ring);

        if ($count < 3) {
            return false;
        }

        $x = $longitude;
        $y = $latitude;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $current = $ring[$i] ?? null;
            $previous = $ring[$j] ?? null;

            if (!is_array($current) || !is_array($previous) || count($current) < 2 || count($previous) < 2) {
                continue;
            }

            $xi = (float) $current[0];
            $yi = (float) $current[1];
            $xj = (float) $previous[0];
            $yj = (float) $previous[1];

            $intersects = (($yi > $y) !== ($yj > $y))
                && ($x < (($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 0.0000000001)) + $xi);

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
