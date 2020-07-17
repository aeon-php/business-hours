<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian\BusinessHours\BusinssDay;

use Aeon\Calendar\Gregorian\BusinessHours\BusinessDay;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;
use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\Gregorian\Day;

/**
 * @psalm-immutable
 */
final class CustomBusinessDay implements BusinessDay
{
    private Day $day;

    private WorkingHours $workingHours;

    public function __construct(Day $day, WorkingHours $workingHours)
    {
        $this->day = $day;
        $this->workingHours = $workingHours;
    }

    public function is(Day $day) : bool
    {
        return $this->day->isEqual($day);
    }

    public function isOpen(DateTime $dateTime) : bool
    {
        return $this->is($dateTime->day()) && $this->workingHours()->isOpen($dateTime->time());
    }

    public function workingHours() : WorkingHours
    {
        return $this->workingHours;
    }
}
