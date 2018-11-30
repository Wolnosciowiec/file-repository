<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

class PositiveNumberOrZero extends PositiveNumber
{
    protected function setValue($number): void
    {
        if ($number < 0) {
            throw new \InvalidArgumentException('Number cannot be negative value');
        }

        $this->value = $number;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
