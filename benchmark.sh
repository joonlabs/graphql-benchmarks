#!/bin/bash

clear

# define random variable pattern
pattern="{{RANDOMVARIABLE}}"

# Here you can pre define different raw query-data
query_no_complexity='{"query":"{{RANDOMVARIABLE}}\n {\n  hero{\n    name\n  }\n}","variables":{},"operationName":null}'
query_middle_complexity='{"query":"{{RANDOMVARIABLE}}\n query{\n  hero(episode: JEDI){\n    id\n    name\n    appearsIn\n    secretBackstory\n    friends{\n      id\n      name\n      ... on Droid{\n        appearsIn\n        primaryFunction\n      }\n      ... on Human{\n        appearsIn\n        secretBackstory\n      }\n      ... HumanFragment\n    }\n  }\n}\n\nfragment HumanFragment on Human{\n\tid\n    name\n    appearsIn\n    secretBackstory\n    friends{\n      id\n      name\n      ... on Droid{\n        appearsIn\n        primaryFunction\n      }\n      ... on Human{\n        appearsIn\n        secretBackstory\n      }\n    }\n}","variables":{},"operationName":null}'
query_high_complexity='{"query":"{{RANDOMVARIABLE}}\n query{\n  human(id:\"1001\"){\n    ... HumanFragment\n  }\n  droid(id:\"2001\"){\n  \t... DroidFragment\n  }\n  hero(episode: JEDI){\n    id\n    name\n    appearsIn\n    secretBackstory\n    friends{\n      id\n      name\n      ... on Droid{\n        appearsIn\n        primaryFunction\n      }\n      ... on Human{\n        appearsIn\n        secretBackstory\n      }\n      ... HumanFragment\n    }\n  }\n}\n\nfragment HumanFragment on Human{\n\tid\n    name\n    appearsIn\n    secretBackstory\n    friends{\n      id\n      name\n      ... on Droid{\n        appearsIn\n        primaryFunction\n        friends{\n          ... CharacterFragment\n        }\n      }\n      ... on Human{\n        appearsIn\n        secretBackstory\n        friends{\n          ... CharacterFragment\n        }\n      }\n    }\n}\n\nfragment DroidFragment on Droid{\n\tid\n    name\n    appearsIn\n    primaryFunction\n    friends{\n      id\n      name\n      ... on Droid{\n        appearsIn\n        primaryFunction\n        friends{\n          ... CharacterFragment\n        }\n      }\n      ... on Human{\n        appearsIn\n        secretBackstory\n        friends{\n          ... CharacterFragment\n        }\n      }\n    }\n}\n\nfragment CharacterFragment on Character{\n  id\n  name\n  friends{\n    name\n  }\n}","variables":{},"operationName":null}'
query_introspection='{"query":"{{RANDOMVARIABLE}}\n query IntrospectionQuery {\n            __schema {\n              queryType { name }\n              mutationType { name }\n              subscriptionType { name }\n              types {\n                ...FullType\n              }\n              directives {\n                name\n                description\n                args {\n                  ...InputValue\n                }\n              }\n            }\n          }\n        \n          fragment FullType on __Type {\n            kind\n            name\n            description\n            fields(includeDeprecated: true) {\n              name\n              description\n              args {\n                ...InputValue\n              }\n              type {\n                ...TypeRef\n              }\n              isDeprecated\n              deprecationReason\n            }\n            inputFields {\n              ...InputValue\n            }\n            interfaces {\n              ...TypeRef\n            }\n            enumValues(includeDeprecated: true) {\n              name\n              description\n              isDeprecated\n              deprecationReason\n            }\n            possibleTypes {\n              ...TypeRef\n            }\n          }\n        \n          fragment InputValue on __InputValue {\n            name\n            description\n            type { ...TypeRef }\n            defaultValue\n          }\n        \n          fragment TypeRef on __Type {\n            kind\n            name\n            ofType {\n              kind\n              name\n              ofType {\n                kind\n                name\n                ofType {\n                  kind\n                  name\n                }\n              }\n            }\n          }","variables":{},"operationName":"IntrospectionQuery"}'

# SART CONFIGURATION SECTION:
# here you can configure the query that is being sent and the number of iterations perfomed to each url
query="$query_introspection"
max_iteration=150
# END CONFIGURATION SECTION:

# START SCRIPT
escapedQuery=$(echo "$query" | sed 's/"/\\"/g')
escapedQuery=$(echo "$escapedQuery" | sed 's/\\\\/\\/g')
output="{\"timestamp\":\"$(date +"%Y-%m-%d %H:%M:%S")\", \"query\":\"$escapedQuery\",\"benchmarks\":["
num_urls=2
max_iteration=$((max_iteration+5))
#for url in "http://localhost:8888/" "http://localhost:8889/"
for url in "https://graphql.joonlabs.com/joonlabs-php-graphql/" "https://graphql.joonlabs.com/webonyx-graphql-php/"
do
  output="$output{\"url\":\"$url\", \"data\":["
  for i in $(seq "$max_iteration")
  do
    random_number=$RANDOM
    temp_query=$(echo $query | sed -e "s/${pattern}/#${random_number}/g")
    echo -ne "url: $url | iteration: $i/$max_iteration (with 5 iter. warm-up)\r"
    #result=$(curl "$url" \
    result=$(curl -s -w "%{time_total}" -o /dev/null "$url" \
    -H 'Connection: keep-alive' \
    -H 'Accept: application/json' \
    -H 'Accept-Language: de' \
    -H 'Content-Type: application/json' \
    -H 'Origin: joonlabs://graphql-benchmarks' \
    -H 'Sec-Fetch-Dest: empty' \
    -H 'Sec-Fetch-Mode: cors' \
    -H 'Sec-Fetch-Site: cross-site' \
    -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 11_3_1) AppleWebKit/537.36 (KHTML, like Gecko) joonlabs-graphql-benchmarks/1.0.0 Chrome/89.0.4389.82 Safari/537.36' \
    --data-raw "$temp_query" \
    --compressed)
    #echo "$result"
    if [ "$i" -gt "5" ]; then
      output="$output$result"
      if [ "$i" -lt "$max_iteration" ]; then
        output="$output,"
      fi
    fi

  done
  echo "------------------- finished url $url -------------------"
  output="$output]}"
  num_urls=$((num_urls-1))
  if [ "$num_urls" -gt "0" ]; then
      output="$output,"
  fi
done
output="$output]}"
# save output json string to results.json
echo "$output" > results.json
# END SCRIPT