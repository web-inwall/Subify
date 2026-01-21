<?php

declare(strict_types=1);

namespace App\Domains\Payment\ValueObjects;

use InvalidArgumentException;
use NumberFormatter;

/**
 * Immutable value object representing a monetary value in cents.
 */
readonly class Money
{
    /**
     * @param  int  $amount  Amount in cents.
     * @param  string  $currency  3-letter ISO currency code.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public int $amount,
        public string $currency
    ) {
        if ($this->amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter ISO code.');
        }
    }

    /**
     * Format the money to a localized string.
     */
    public function format(string $locale = 'en_US'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($this->amount / 100, $this->currency);
    }

    /**
     * Create a new instance from cents.
     */
    public static function fromCents(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }
}
