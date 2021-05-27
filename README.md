# Introduction
This repo contains benchmark setups and scripts for testing different queries against the 
two PHP-GraphQL implementations **webonyx/graphql-php** and **joonlabs/php-graphql** on an equal Star Wars schema containing the same data.
Both libraries use their built-in default servers.

# Measuring
The measurements are performed with the help of curl. A simple shell script sends a selected query, which can be set in the head of the script, several times to two servers. The absolute response time is then written to a json file. This json file can then be visualized using the `visualize.php` script. Here, the mean value, the standard deviation and the percentage difference in speed are measured.

# Setup
### 1. Start the servers
Using the console, go to the two directories and start one server in each of these directories, e.g. with the PHP built-in server:
```bash
cd joonlabs-php-graphql/
php -S localhost:8888 index.php 
```

```bash
cd webonyx-graphql-php/
php -S localhost:8889 index.php 
```

*HINT: If you do not use the urls mentioned above, you should also change them in the `benchmark.sh` script.* 


### 2. Set the query
In the head of the `benchmark.sh` script you can define the query sent to the server and how many times each query is sent to each of the both servers.
Unless otherwise specified, the following query is sent 200 times to each of the two servers:
```graphql
query{
  hero(episode: JEDI){
    id
    name
    appearsIn
    secretBackstory
    friends{
      id
      name
      ... on Droid{
        appearsIn
        primaryFunction
      }
      ... on Human{
        appearsIn
        secretBackstory
      }
      ... HumanFragment
    }
  }
}

fragment HumanFragment on Human{
    id
    name
    appearsIn
    secretBackstory
    friends{
      id
      name
      ... on Droid{
        appearsIn
        primaryFunction
      }
      ... on Human{
        appearsIn
        secretBackstory
      }
    }
}
```
### 3. Run the benchmark
You can start the benchmark with the following command:
```bash
/bin/bash benchmark.sh
```
This will produce a `results.json` in the root directory, which can be visualized / analyzed with the following command:
```bash
php visualize.php 
```
The results should then be output on the console. E.g. like this:
```
    Results for benchmarks started at 2021-05-27 23:07:05

    Benchmarking on url [http://localhost:8888/]:
        taken samples:      200 iter.
        mean:               0.00764s
        standard deviation: 0.00105s
    
    Benchmarking on url [http://localhost:8889/]:
        taken samples:      200 iter.
        mean:               0.01141s
        standard deviation: 0.00019s
    
    ******************************************************************************************************
    * joonlabs/php-graphql is 33.04% faster than webonyx/graphql-php, facing the following query...      *
    ******************************************************************************************************
    
    Query:
{"query":"query{
  hero(episode: JEDI){
    id
    name
    appearsIn
    secretBackstory
    friends{
      id
      name
      ... on Droid{
        appearsIn
        primaryFunction
      }
      ... on Human{
        appearsIn
        secretBackstory
      }
      ... HumanFragment
    }
  }
}

fragment HumanFragment on Human{
	id
    name
    appearsIn
    secretBackstory
    friends{
      id
      name
      ... on Droid{
        appearsIn
        primaryFunction
      }
      ... on Human{
        appearsIn
        secretBackstory
      }
    }
}","variables":{},"operationName":null}
```

# Please note...
This is not meant to be evidence or a statement that one of the libraries is better than the other. Both libraries have different strengths and weaknesses. This repository should only provide a reproducible possibility to enable an independent benchmark with as real-world influences as possible and to compare response times. Also only the Star Wars schema is tested here, which should not be used solely as a basis for an absolute speed evaluation, since schemas in real-world use cases usually deviate strongly from one another in size, complexity and area of application.