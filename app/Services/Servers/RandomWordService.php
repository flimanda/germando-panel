<?php

namespace App\Services\Servers;

class RandomWordService
{
    private const RANDOM_WORDS = [
        'robin', 'mando', 'pigeon', 'blue-jay', 'irrenanstalt', 'finch', 'falcon', 'phoenix', 'squirrel', 'parrot', 'hawk',
        'sparrow', 'owl', 'swan', 'dove', 'cardinal', 'psychose', 'penguin', 'chupacabra', 'spoonbill', 'humming', 'turkey',
        'chicken', 'splitfiction', 'eagle', 'woodpecker', 'mockingbird', 'sekte', 'lovebird', 'bluebird', 'magpie', 'starling',
        'cockatiel', 'swallow', 'grosbeak', 'goose', 'forpus', 'budgerigar', 'mango', 'towhee', 'warbler', 'peregrine',
        'nuthatch', 'chickadee', 'bananaquit', 'crow', 'raven', 'merlin', 'spatuletail',
    ];

    public function word(): string
    {
        return array_random(self::RANDOM_WORDS);
    }
}
