<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\ElasticSearchQueryBuilder\Commands\ElasticSearchQueryBuilderCommand;

class ElasticSearchQueryBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-elasticsearch-query-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-elasticsearch-query-builder_table')
            ->hasCommand(ElasticSearchQueryBuilderCommand::class);
    }
}
