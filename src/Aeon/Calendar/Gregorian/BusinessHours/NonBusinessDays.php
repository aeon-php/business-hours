<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian\BusinessHours;

use Aeon\Calendar\Gregorian\Day;

/**
 * @psalm-immutable
 */
final class NonBusinessDays
{
    /**
     * @var array<int, NonBusinessDay>
     */
    private array $nonBusinessDays;

    public function __construct(NonBusinessDay ...$nonBusinessDays)
    {
        $this->nonBusinessDays = $nonBusinessDays;
    }

    /**
     * @psalm-pure
     */
    public static function none() : self
    {
        return new self();
    }

    public function is(Day $day) : bool
    {
        foreach ($this->nonBusinessDays as $exceptionalNonBusinessDay) {
            if ($exceptionalNonBusinessDay->is($day)) {
                return true;
            }
        }

        return false;
    }
}
