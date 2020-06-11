<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;

use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;
use Aeon\Calendar\Gregorian\Time;
use Webmozart\Assert\Assert;

/**
 * @psalm-immutable
 */
final class ShiftsWorkingHours implements WorkingHours
{
    /**
     * @var array<int, LinearWorkingHours>
     */
    private array $workingHours;

    public function __construct(LinearWorkingHours ...$workingHours)
    {
        Assert::greaterThan(\count($workingHours), 0, 'Shifts can\'t be empty');

        \uasort(
            $workingHours,
            function (LinearWorkingHours $workingHoursA, LinearWorkingHours $workingHoursB) : int {
                return $workingHoursA->openFrom()->isLessThanEq($workingHoursB->openFrom())
                    ? -1
                    : 1;
            }
        );

        $this->workingHours = \array_values($workingHours);
    }

    public function openFrom() : Time
    {
        return $this->workingHours[0]->openFrom();
    }

    public function openTo() : Time
    {
        return $this->workingHours[\count($this->workingHours) - 1]->openTo();
    }

    public function isOpen(Time $time) : bool
    {
        foreach ($this->workingHours as $workingHours) {
            if ($workingHours->isOpen($time)) {
                return true;
            }
        }

        return false;
    }
}
