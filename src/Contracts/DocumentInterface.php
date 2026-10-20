<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Contracts;

use StdNumCentroamerica\ValidationResult;

interface DocumentInterface
{
    public function isValid(string $number): bool;

    public function validate(string $number): ValidationResult;

    public function format(string $number): string;

    public function compact(string $number): string;
}
