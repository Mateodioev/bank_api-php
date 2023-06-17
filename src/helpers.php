<?php

namespace BankApi;

/**
 * Crea un nuevo id unico universal ? 
 */
function UUIDv4($data): string
{
    \assert(\strlen($data) == 16);

    $data[6] = \chr(\ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = \chr(\ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($data), 4));
}

function genUUIDv4(): string
{
    $data = openssl_random_pseudo_bytes(16, $strong);
    while (true) {
        try {
            assert($data !== false && $strong);
            return UUIDv4($data);
        } catch (\Throwable) {
            $data = openssl_random_pseudo_bytes(16, $strong);
            continue;
        }
    }
}
