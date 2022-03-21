
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Parse custom search strings and execute them using ElasticSearch

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/elasticsearch-search-string-parser.svg?style=flat-square)](https://packagist.org/packages/spatie/elasticsearch-search-string-parser)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/elasticsearch-search-string-parser/run-tests?label=tests)](https://github.com/spatie/elasticsearch-search-string-parser/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/elasticsearch-search-string-parser/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/elasticsearch-search-string-parser/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/elasticsearch-search-string-parser.svg?style=flat-square)](https://packagist.org/packages/spatie/elasticsearch-search-string-parser)

This package allows you to convert a search string like `foo bar status:active @john.doe` to its corresponding ElasticSearch request. Any custom _directives_ like `status:active` and `@john.doe` can be added using regex and the [`spatie/elasticsearch-query-builder`](https://github.com/spatie/elasticsearch-query-builder). There's also basic support for grouping directives (e.g. `group_by:project`) and providing auto-completion suggestions for certain directives.

```php
use Elasticsearch\ClientBuilder;
use Spatie\ElasticsearchStringParser\SearchQuery;

$subjects = SearchQuery::forClient(ClientBuilder::create())
    ->baseDirective(new SubjectBaseDirective())
    ->patternDirectives(
        new CompanyDirective(),
        new UserDirective(),
    )  
    ->search('deadly neurotoxin company:aperture @glados');
```

In the example above, an ElasticSearch request is executed with the appropriate parameters set to search for results with the given company (`aperture`), user (`glados`) and subject string (`deadly neurotoxin`). The returned value is a `\Spatie\ElasticsearchStringParser\SearchResults` object that contains search results and suggestions for the applied directives.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/elasticsearch-search-string-parser.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/elasticsearch-search-string-parser)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source)
. You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are
using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received
postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/elasticsearch-search-string-parser
```

## How it works: directives

When creating a search string parser, you decide how each part of the search string is parsed by defining _directives_. When a directive is found in the search string, it is applied to the underlying ElasticSearch. Directives can be used to add basic match queries but also to add sorts, aggregations, facets, etc...

Let's dive into the inner workings of the package by dissecting an example search string and its parser:

```php
$searchString = 'cheap neurotoxin company:aperture deadly @glados';

SearchQuery::forClient(ClientBuilder::create())
    ->baseDirective(new SubjectBaseDirective())
    ->patternDirectives(
        new CompanyDirective(),
        new UserDirective(),
    )->search($searchString);
```

 A search string parser can have multiple `PatternDirective`s and at most one `BaseDirective`. In the example search string there are two pattern directives: `company:aperture` and `@glados`. These will be parsed by the `CompanyDirective` and `UserDirective`. The remaining string (`cheap nearotoxin deadly`) will be processed by the base directive.

To do this, we'll loop over all configured pattern directives. Each patter directive has a regular expression it looks for. If one of the directives finds a match in the search string, it will be applied and the match will be removed from the search string. The process is then repeated for the next match or the next pattern directive.

Back to our example: the `CompanyDirective` is configured to match `company:(.*)`. In the example string, this regex pattern will match `company:aperture`. This means the `CompanyDirective` will be applied and a query for `company_name="aperture"` will be added to the ElasticSearch builder. Finally, the directive is removed from the search string, leaving us with the following string:

```
cheap neurotoxin deadly @glados
```

As there are no other matches for the `CompanyDirective`, we'll look for the `UserDirective` next. The user directive will search for `@(.*)` and thus match `@glados`. The `UserDirective` will now apply its queries to the ElasticSearch builder and remove the matches string. We're left with:

```
cheap neurotoxin deadly
```

There are no pattern directives left to apply. The entire remaining string is then passed to the `SubjectBaseDirective`. This base directive then decides what to do with the remaining search string, for example, using it for a fuzzy search on the subject field.

## Usage

```php
$elasticsearch-search-string-parser = new Spatie\ElasticsearchStringParser();
echo $elasticsearch-search-string-parser->echoPhrase('Hello, Spatie!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alex Vanderbist](https://github.com/AlexVanderbist)
- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
