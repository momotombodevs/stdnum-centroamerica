<?php

namespace StdNumCentroamerica\Contracts;

interface DocumentInterface
{
    public function isValid(string $number): bool;

    public function format(string $number): string;

    public function compact(string $number): string;
}