<?php

declare(strict_types=1);


namespace Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;

use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;
use Aeon\Calendar\Gregorian\Time;
use Webmozart\Assert\Assert;

/**
 * @psalm-immutable
 */
final class LinearWorkingHours implements WorkingHours
{
    private Time $startHour;

    private Time $endHour;

    public function __construct(Time $startHour, Time $endHour)
    {
        Assert::true($endHour->isGreaterThan($startHour), 'End hour needs to be greater than start hour');
        $this->startHour = $startHour;
        $this->endHour = $endHour;
    }

    public function openFrom() : Time
    {
        return $this->startHour;
    }

    public function openTo() : Time
    {
        return $this->endHour;
    }

    public function isOpen(Time $time) : bool
    {
        return $time->isGreaterThanEq($this->openFrom()) &&
            $time->isLessThanEq($this->openTo());
    }
}
