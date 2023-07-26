<?php

namespace OpenApi2KrakenD;

/**
 * @param array $krakenDConfig {@see KrakenD::__construct()}
 */
function convert(OpenApi $openApi, array $krakenDConfig = []): KrakenD
{
    $krakenD = new KrakenD($krakenDConfig);

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

            $queryStrings = getQueryStrings($pathData[$method]['parameters'] ?? []);
            if (!empty($queryStrings)) {
                $endpoint['input_query_strings'] = $queryStrings;
            }

            $endpoint['backend'] = getBackend($pathData[$method]['responses']['200'] ?? []);

            $krakenD->addEndpoint($endpoint);
        }
    }

    return $krakenD;
}

function getQueryStrings(array $parameters): array
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

function getBackend(array $response): array
{
    $backend = [];
    $cacheHeader = $response['headers']['Cache-Control'] ?? [];

    if (!empty($cacheHeader)) {
        $backend[] = [
            'extra_config' => [
                'modifier/martian' => [
                    'header.Modifier' => [
                        'scope' => ['response'],
                        'name' => 'Cache-Control',
                        'value' => $cacheHeader['schema']['example'],
                    ],
                ],
            ],
        ];
    }

    return $backend;
}
