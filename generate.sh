#!/bin/bash

while getopts m:t: flag
do
    case "${flag}" in
        m) model=${OPTARG};;
        t) table=${OPTARG};;
    esac
done

php artisan infyom:api "$model" --fromTable --tableName=$table --skip=migration,controllers,routes,api_routes,views,tests
rm -rf ./app/Http/Requests