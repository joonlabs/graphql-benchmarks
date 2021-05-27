<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test this using following command
// php -S localhost:8080 ./graphql.php &
// curl http://localhost:8080 -d '{"query": "query { echo(message: \"Hello World\") }" }'
// curl http://localhost:8080 -d '{"query": "mutation { sum(x: 2, y: 2) }" }'
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/__schema.php';

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Server\StandardServer;

try {

    // See docs on schema options:
    // https://webonyx.github.io/graphql-php/type-system/schema/#configuration-options
    $schema = \GraphQL\Tests\StarWarsSchema::build();

    // See docs on server options:
    // https://webonyx.github.io/graphql-php/executing-queries/#server-configuration-options
    $server = new StandardServer([
        'schema' => $schema
    ]);

    $server->handleRequest();
} catch (\Exception $e) {
    StandardServer::send500Error($e);
}