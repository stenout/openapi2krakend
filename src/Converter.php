<?php

namespace OpenApi2KrakenD;

class Converter
{
    public static function convert(OpenApi $openApi, KrakenD $krakenD): KrakenD
    {
        $endpoints = [];
        foreach ($openApi->paths() as $path => $pathData) {
            $methods = array_keys($pathData);
            foreach ($methods as $method) {
                $tags = $pathData[$method]['tags'] ?? [];
                if (in_array('excludeKrakenD', $tags)) {
                    continue;
                }

                $endpoint = [
                    'endpoint' => $path,
                    'method' => strtoupper($method),
                ];

                $queryStrings = self::getQueryStrings($pathData[$method]['parameters'] ?? []);
                if (!empty($queryStrings)) {
                    $endpoint['input_query_strings'] = $queryStrings;
                }

                $backend =  [
                    'url_pattern' => $path,
                    'method' => strtoupper($method),
                    'host' => [$krakenD->host],
                ];
                $extraConfig = self::getExtraConfig($pathData[$method]['responses']['200'] ?? []);
                if (!empty($extraConfig)) {
                    $backend['extra_config'] = $extraConfig;
                }
                $endpoint['backend'][] = $backend;

                $endpoints[] = $endpoint;
            }
        }

        $cleanEndPoints = self::removeDuplicateEndpoints($endpoints);
        $krakenD->addEndpoints($cleanEndPoints);

        return $krakenD;
    }

    private static function getQueryStrings(array $parameters): array
    {
        $queryParameters = array_filter(
            $parameters,
            fn(array $parameter) => $parameter['in'] === 'query'
        );

        $queryStrings = array_map(
            fn(array $parameter) => $parameter['name'],
            $queryParameters
        );

        return array_values($queryStrings);
    }

    private static function getExtraConfig(array $response): array
    {
        $extraConfig = [];
        $cacheHeader = $response['headers']['Cache-Control'] ?? [];
        if (!empty($cacheHeader)) {
            $extraConfig = [
                'modifier/martian' => [
                    'header.Modifier' => [
                        'scope' => ['response'],
                        'name' => 'Cache-Control',
                        'value' => $cacheHeader['schema']['example'],
                    ],
                ],
            ];
        }

        return $extraConfig;
    }

    private static function removeDuplicateEndpoints(array $endpoints): array
    {
        $cleanEndPoints = [];
        foreach ($endpoints as $endpoint) {
            if (!in_array($endpoint['endpoint'], $cleanEndPoints)) {
                $cleanEndPoints[] = $endpoint;
            }
        }

        return $cleanEndPoints;
    }
}
