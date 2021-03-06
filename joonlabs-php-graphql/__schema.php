<?php

use GraphQL\Errors\ForbiddenError;
use GraphQL\Types\GraphQLObjectType;
use GraphQL\Types\GraphQLEnum;
use GraphQL\Types\GraphQLEnumValue;
use GraphQL\Types\GraphQLString;
use GraphQL\Types\GraphQLInterface;
use GraphQL\Types\GraphQLNonNull;
use GraphQL\Types\GraphQLList;
use GraphQL\Fields\GraphQLTypeField;
use GraphQL\Arguments\GraphQLFieldArgument;

require __DIR__ . "/__data.php";

/**
 * EPISODE
 */
$Episode = new GraphQLEnum("Episode", "One of the films in the Star Wars Trilogy.", [
    new GraphQLEnumValue("NEW_HOPE", "Released in 1977."),
    new GraphQLEnumValue("EMPIRE", "Released in 1980."),
    new GraphQLEnumValue("JEDI", "Released in 1983.")
]);

/**
 * CHARACTER
 */
$Character = new GraphQLInterface("Character", "A character in the Star Wars Trilogy.", function () use (&$Character, &$Episode) {
    return [
        new GraphQLTypeField("id", new GraphQLNonNull(new GraphQLString()), "The id of the character."),
        new GraphQLTypeField("name", new GraphQLString(), "The name of the character."),
        new GraphQLTypeField("friends", new GraphQLList($Character), "The friends of the character, or an empty list if they have none."),
        new GraphQLTypeField("appearsIn", new GraphQLList($Episode), "Which movies they appear in."),
        new GraphQLTypeField("secretBackstory", new GraphQLString(), "All secrets about their past."),
    ];
}, function ($character) {
    if ($character["type"] === "human") {
        return "Human";
    }
    return "Droid";
});

/**
 * HUMAN
 */
$Human = new GraphQLObjectType("Human", "A humanoid creature in the Star Wars universe.", function () use (&$Character, &$Episode, &$humans, &$droids) {
    return [
        new GraphQLTypeField("id", new GraphQLNonNull(new GraphQLString()), "The id of the human."),
        new GraphQLTypeField("name", new GraphQLString(), "The name of the human."),
        new GraphQLTypeField("friends", new GraphQLList($Character), "The friends of the human, or an empty list if they have none.", function ($character) use (&$humans, &$droids) {
            return array_map(function ($friendId) use (&$humans, &$droids) {
                return $humans[$friendId] ?? $droids[$friendId];
            }, $character["friends"]);
        }),
        new GraphQLTypeField("appearsIn", new GraphQLList($Episode), "Which movies they appear in."),
        new GraphQLTypeField("homePlanet", new GraphQLString(), "The home planet of the human, or null if unknown."),
        new GraphQLTypeField("secretBackstory", new GraphQLString(), "Where are they from and how they came to be who they are.", function (){
            throw new ForbiddenError("secretBackstory is secret.");
        })
    ];
}, [
    $Character
]);

/**
 * DROID
 */
$Droid = new GraphQLObjectType("Droid", "A mechanical creature in the Star Wars universe.", function () use (&$Character, &$Episode, &$humans, &$droids) {
    return [
        new GraphQLTypeField("id", new GraphQLNonNull(new GraphQLString()), "The id of the droid."),
        new GraphQLTypeField("name", new GraphQLString(), "The name of the droid."),
        new GraphQLTypeField("friends", new GraphQLList($Character), "The friends of the droid, or an empty list if they have none.", function ($character) use (&$humans, &$droids) {
            return array_map(static function ($friendId) use (&$humans, &$droids) {
                return $humans[$friendId] ?? $droids[$friendId];
            }, $character["friends"]);
        }),
        new GraphQLTypeField("appearsIn", new GraphQLList($Episode), "Which movies they appear in."),
        new GraphQLTypeField("primaryFunction", new GraphQLString(), "The primary function of the droid."),
        new GraphQLTypeField("secretBackstory", new GraphQLString(), "Construction date and the name of the designer.", static function (){
            throw new ForbiddenError("secretBackstory is secret.");
        })
    ];
}, [
    $Character
]);


/**
 * QUERY
 */
$Query = new GraphQLObjectType("Query", "Root Query", function () use (&$Episode, &$Character, &$Human, &$Droid, &$humans, &$droids) {
    return [
        new GraphQLTypeField("hero", $Character, "", static function ($_, $args) use (&$humans, &$droids) {
            if (($args["episode"] ?? null) === "EMPIRE") {
                return $humans["1000"]; // Luke Skywalker
            }
            return $droids["2001"]; // R2-D2
        }, [
                new GraphQLFieldArgument("episode", $Episode, "If omitted, returns the hero of the whole saga. If provided, returns the hero of that particular episode")
            ]
        ),
        new GraphQLTypeField("human", $Human, "", static function ($_, $args) use (&$humans) {
            return $humans[$args["id"]] ?? null;
        }, [
                new GraphQLFieldArgument("id", new GraphQLNonNull(new GraphQLString()), "id of the human")
            ]
        ),
        new GraphQLTypeField("droid", $Droid, "", static function ($_, $args) use (&$droids) {
            return $droids[$args["id"]] ?? null;
        }, [
                new GraphQLFieldArgument("id", new GraphQLNonNull(new GraphQLString()), "id of the droid")
            ]
        )
    ];
});