<?php

declare(strict_types=1);


namespace Aeon\Calendar\Gregorian\BusinessHours;

use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\Gregorian\Time;
use Webmozart\Assert\Assert;

/**
 * @psalm-immutable
 */
final class WorkingHours
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

    public function covers(DateTime $dateTime) : bool
    {
        return $dateTime->time()->isGreaterThanEq($this->openFrom()) &&
            $dateTime->time()->isLessThanEq($this->openTo());
    }
}
