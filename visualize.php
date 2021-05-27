<?php
/**
 * The purpose of this PHP script is to pretty-print and format
 * the measured benchmark data created by benchmark.sh
 */

// check for file
if (!file_exists(__DIR__ . "/results.json")) {
    throw new Exception("results.json not found in " . __DIR__.". Run benchmark.sh first!");
}

// parse results
$results = json_decode(file_get_contents(__DIR__ . "/results.json"), true);

$timestamp = $results["timestamp"];
$means = [];

print("
    Results for benchmarks started at $timestamp
");

// iterate over each port
foreach ($results["benchmarks"] as $benchmark) {
    $url = $benchmark["url"];
    $taken_samples = count($benchmark["data"]);
    $mean = round(calc_mean($benchmark["data"]), 5);
    $standard_deviation = round(calc_standard_deviation($benchmark["data"]), 5);

    $means[] = $mean;

    // print results for port
    print("
    Benchmarking on url [$url]:
        taken samples:      $taken_samples iter.
        mean:               {$mean}s
        standard deviation: {$standard_deviation}s
    ");
}

if (count($means) > 0) {
    if($means[0] < $means[1]){
        $percent = round((1 - ($means[0] / $means[1]))*100, 2);
        print("
    ******************************************************************************************************
    * joonlabs/php-graphql is {$percent}% faster than webonyx/graphql-php, facing the following query...      *
    ******************************************************************************************************
    ");
    }else{
        $percent = round((1 - ($means[1] / $means[0]))*100, 2);
        print("
    ******************************************************************************************************
    * webonyx/graphql-php is {$percent}% faster than joonlabs/php-graphql, facing the following query...      *
    ******************************************************************************************************
    ");
    }
}

// print query
$query = str_replace("\n", "\\n", $results["query"]);
print("
    Query:
$query
");

function calc_mean($data): float
{
    $mean = 0;
    foreach ($data as $datum) {
        $mean += $datum;
    }
    return (float)($mean / count($data));
}

function calc_standard_deviation($data): float
{
    $variance = 0.0;
    $average = array_sum($data) / count($data);
    foreach ($data as $datum) {
        $variance += pow(($datum - $average), 2);
    }
    return (float)sqrt($variance / count($data));
}

