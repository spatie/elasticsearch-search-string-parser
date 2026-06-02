# Upgrading

## From v1 to v2

Version 2 adds support for Elasticsearch 8 and drops support for Elasticsearch 7. It mirrors the major version split that [`spatie/elasticsearch-query-builder`](https://github.com/spatie/elasticsearch-query-builder) made for Elasticsearch 8 (its `v3`).

If you are still running Elasticsearch 7, stay on the `1.x` release of this package.

### Requirements

* PHP 8.3 or higher is now required.
* Elasticsearch 8 and the matching `elasticsearch/elasticsearch` 8.x PHP client are now required.

### Client namespace

The Elasticsearch 8 PHP client moved from the `Elasticsearch` namespace to `Elastic\Elasticsearch`. Update your imports accordingly.

Before:

```php
use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->build();
```

After:

```php
use Elastic\Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->build();
```

`SearchQuery::forClient()` now type-hints `Elastic\Elasticsearch\Client`. Passing an instance of the old `Elasticsearch\Client` will throw a `TypeError`.

### Dependencies

Update the relevant constraints in your `composer.json`:

```diff
-    "elasticsearch/elasticsearch": "^7.12",
+    "elasticsearch/elasticsearch": "^8.0",
-    "spatie/elasticsearch-search-string-parser": "^1.0"
+    "spatie/elasticsearch-search-string-parser": "^2.0"
```

### Directives

The directive layer is unchanged. The query and aggregation APIs used internally (`addQuery`, `addAggregation`, `size`, `from`, `MultiMatchQuery::create`, `TermsAggregation::create`, `TopHitsAggregation::create`, `RangeQuery`) have identical signatures across query-builder `v1` and `v3`, so any custom directives keep working without changes.
