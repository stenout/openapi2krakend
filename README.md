# Библиотека для конвертации OpenApi в KrakenD

### Пример использования
```php
// Хост, на который KrakenD будет перенаправлять запросы
$host = 'https://my-service.test';

// С файлами
$openApi = \OpenApi2KrakenD\OpenApi::fromFile('openapi.json');
$openApi->toKrakenD($host)->toFile('path/krakend.json');

// С json-строками
$openApi = \OpenApi2KrakenD\OpenApi::fromJson($openApiJson);
$krakend = $openApi->toKrakenD($host);
$krakendJson = $krakend->toJson(); // без форматирования
$krakendPrettyJson = $krakend->toPrettyJson(); // с форматированием
```