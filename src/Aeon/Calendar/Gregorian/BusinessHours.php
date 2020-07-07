<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian;

use Aeon\Calendar\Gregorian\BusinessHours\BusinessDays;
use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDays;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;
use Aeon\Calendar\Gregorian\Exception\BusinessDayException;

/**
 * @psalm-immutable
 */
final class BusinessHours
{
    private BusinessDays $regularBusinessDays;

    private BusinessDays $customBusinessDays;

    private NonBusinessDays $nonBusinessDays;

    /**
     * @param BusinessDays $regularBusinessDays - lowest priority when checking open hours, overwrites nothing
     * @param BusinessDays $customBusinessDays - highest priority when checking open hours, overwrites business days and non business days
     * @param NonBusinessDays $nonBusinessDays - medium priority when checking open hours, overwrites regular business days
     */
    public function __construct(BusinessDays $regularBusinessDays, BusinessDays $customBusinessDays, NonBusinessDays $nonBusinessDays)
    {
        $this->regularBusinessDays = $regularBusinessDays;
        $this->customBusinessDays = $customBusinessDays;
        $this->nonBusinessDays = $nonBusinessDays;
    }

    public function isOpen(DateTime $dateTime) : bool
    {
        if ($this->customBusinessDays->isOpen($dateTime)) {
            return true;
        }

        if ($this->nonBusinessDays->is($dateTime->day())) {
            return false;
        }

        return $this->regularBusinessDays->isOpen($dateTime);
    }

    public function isOpenOn(Day $day) : bool
    {
        if ($this->customBusinessDays->isOpenOn($day)) {
            return true;
        }

        if ($this->nonBusinessDays->is($day)) {
            return false;
        }

        return $this->regularBusinessDays->isOpenOn($day);
    }

    public function nextBusinessDay(Day $day) : Day
    {
        $nextDay = $day->next();

        while (!$this->regularBusinessDays->isOpenOn($nextDay) && !$this->customBusinessDays->isOpenOn($nextDay)) {
            $nextDay = $nextDay->next();
        }

        return $nextDay;
    }

    public function workingHours(Day $day) : WorkingHours
    {
        if ($this->customBusinessDays->isOpenOn($day)) {
            return $this->customBusinessDays->get($day)->workingHours();
        }

        if ($this->nonBusinessDays->is($day)) {
            throw new BusinessDayException(\sprintf('%s is not a business day', $day->format('Y-m-d')));
        }

        if ($this->regularBusinessDays->isOpenOn($day)) {
            return $this->regularBusinessDays->get($day)->workingHours();
        }

        throw new BusinessDayException(\sprintf('%s is not a business day', $day->format('Y-m-d')));
    }
}
