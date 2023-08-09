<?php

namespace OpenApi2KrakenD;

class Converter
{
    private OpenApi $openApi;

    public function convert(OpenApi $openApi, KrakenD $krakenD): KrakenD
    {
        $this->openApi = $openApi;

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

                $queryStrings = $this->getQueryStrings($pathData[$method]['parameters'] ?? []);
                if (!empty($queryStrings)) {
                    $endpoint['input_query_strings'] = $queryStrings;
                }

                $backend =  [
                    'url_pattern' => $path,
                    'method' => strtoupper($method),
                    'host' => [$krakenD->host],
                ];
                $extraConfig = $this->getExtraConfig($pathData[$method]['responses']['200'] ?? []);
                if (!empty($extraConfig)) {
                    $backend['extra_config'] = $extraConfig;
                }
                $endpoint['backend'][] = $backend;

                $endpoints[] = $endpoint;
            }
        }

        $cleanEndPoints = $this->removeDuplicateEndpoints($endpoints);
        $krakenD->addEndpoints($cleanEndPoints);

        return $krakenD;
    }

    private function getQueryStrings(array $parameters): array
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

    private function getExtraConfig(array $response): array
    {
        $extraConfig = [];
        $cacheHeader = $response['headers']['Cache-Control'] ?? [];
        if (!empty($cacheHeader)) {
            if (array_key_exists('$ref', $cacheHeader)) {
                $cacheHeader = $this->getRef($cacheHeader['$ref']);
            }
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

    private function getRef(string $ref): array
    {
        $result = [];

        $keys = explode('/', str_replace('#/components/', '', $ref));
        foreach ($keys as $key) {
            $result = $result[$key] ?? $this->openApi->components()[$key] ?? [];
        }

        return $result;
    }

    /**
     * Удаляет записи, в соответствии с особенностями обработки путей в KrakenD, например:
     * /user/{name} и /user/login воспринимаются как дубли, независимо от того, в каком порядке они расположены.
     */
    private function removeDuplicateEndpoints(array $endpoints): array
    {
        $endpointPaths = array_column($endpoints, 'endpoint');

        foreach ($endpoints as $key => $endpoint) {
            foreach ($endpointPaths as $path) {
                if ($this->isEndpointsMatch($path, $endpoint['endpoint']) && $path !== $endpoint['endpoint']) {
                    unset($endpoints[$key]);
                }
            }
        }

        return array_values($endpoints);
    }

    private function isEndpointsMatch($endpoint1, $endpoint2): bool
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
