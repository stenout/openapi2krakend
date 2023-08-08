# Библиотека для конвертации OpenApi в KrakenD

### Пример использования
```php
// С файлами
$openApi = \OpenApi2KrakenD\OpenApi::fromFile('openapi.json');
\OpenApi2KrakenD\convert($openApi, $host)->toFile('krakend.json');

// С json-строками
$openApi = \OpenApi2KrakenD\OpenApi::fromJson($openApiJson);
$krakend = \OpenApi2KrakenD\convert($openApi, $host);
$krakendJson = $krakend->toJson(); // без форматирования
$krakendPrettyJson = $krakend->toPrettyJson(); // с форматированием
```