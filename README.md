# Build eElasticsearch queries based of a query string

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-elasticsearch-query-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-elasticsearch-query-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-elasticsearch-query-builder/run-tests?label=tests)](https://github.com/spatie/laravel-elasticsearch-query-builder/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-elasticsearch-query-builder/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-elasticsearch-query-builder/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-elasticsearch-query-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-elasticsearch-query-builder)

---

- aggregations toevoegen aan builder
- sorts in builder
- options per directive support


```php
[
    'raw' => ['raw els response'],
    'hits' => [],
    'directives' => [
        // alle applied directives
        [
            'directive' => 'comapny:aperture',
            'options' => [], // optioneel options uit directive
        ],
    ],
]


$a = [
    'hits' => [
        [
            'value' => ['object'],
            'group' => ['key' => 'Error Class', 'last_seen_at' => '...', '...'], // of null
        ]
    ],
    
    // tbd
    'filters' => [
        'company' => [
            'options' => [ ... ]
        ],
    ],
]
```


```php
SearchBuilder::for($elasticsearch)
    ->filters([
        CompanyFilter::class,
        UserFilter::class,
    ])  
    ->query('test subjects company:aperture @glados'); // query string can come from $request
```

Filters extract their filter strings using regex and build the underlying elasticsearch query with filters and
facets. Finished ELS query looks something like this:

```json
{
    "query": "test subjects",
    "facets": {
        "companies": {
            "type": "value",
            "size": 30
        },
        "users": {
            "type": "value",
            "size": 30
        }
    },
    "filters": {
        "all": [
            {
                "any": [
                    {
                        "companies": "aperture"
                    }
                ]
            },
            {
                "any": [
                    {
                        "users": "glados"
                    }
                ]
            }
        ]
    }
}


```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-elasticsearch-query-builder.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-elasticsearch-query-builder)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source)
. You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are
using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received
postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-elasticsearch-query-builder
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Spatie\ElasticSearchQueryBuilder\ElasticSearchQueryBuilderServiceProvider" --tag="laravel-elasticsearch-query-builder-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\ElasticSearchQueryBuilder\ElasticSearchQueryBuilderServiceProvider" --tag="laravel-elasticsearch-query-builder-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$laravel-elasticsearch-query-builder = new Spatie\ElasticSearchQueryBuilder();
echo $laravel-elasticsearch-query-builder->echoPhrase('Hello, Spatie!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alex Vanderbist](https://github.com/AlexVanderbist)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
