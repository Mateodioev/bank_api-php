<?php

namespace BankApi;

/**
 * Crea un nuevo id unico universal ? 
 */
function __UUIDv4($data): string
{
    $data[6] = \chr(\ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = \chr(\ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($data), 4));
}

function genUUIDv4(): string
{
    return __UUIDv4(
        // Generate seed
        \md5(
            \str_shuffle(getenv('APP_KEY') . \mt_rand(100000000, PHP_INT_MAX)),
            true
        )
    );
}
