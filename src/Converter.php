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

    /**
     * Удаляет записи, в соответствии с особенностями обработки путей в KrakenD, например:
     * /user/{name} и /user/login воспринимаются как дубли, независимо от того, в каком порядке они расположены.
     */
    private static function removeDuplicateEndpoints(array $endpoints): array
    {
        $endpointPaths = array_column($endpoints, 'endpoint');

        foreach ($endpoints as $key => $endpoint) {
            foreach ($endpointPaths as $path) {
                if (self::isEndpointsMatch($path, $endpoint['endpoint']) && $path !== $endpoint['endpoint']) {
                    unset($endpoints[$key]);
                }
            }
        }

        return array_values($endpoints);
    }

    private static function isEndpointsMatch($endpoint1, $endpoint2): bool
    {
        $endpoint1Parts = explode('/', trim($endpoint1, '/'));
        $endpoint2Parts = explode('/', trim($endpoint2, '/'));

        if (count($endpoint1Parts) !== count($endpoint2Parts)) {
            return false;
        }

        foreach ($endpoint1Parts as $key => $endpoint1Part) {
            $patterns = [
                '/\./',
                '/\{.*\}/',
            ];
            $replacements = [
                '\.',
                '.*',
            ];
            // Экранируем точки и заменяем конструкции {variable} на .*
            $endpoint1Part = preg_replace($patterns, $replacements, $endpoint1Part);
            if (preg_match("/$endpoint1Part/", $endpoint2Parts[$key]) === 0) {
                return false;
            }
        }

        return true;
    }
}
