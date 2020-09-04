<?php

declare(strict_types=1);

namespace Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDay;

use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDay;
use Aeon\Calendar\Gregorian\Day;
use Aeon\Calendar\Gregorian\Interval;
use Aeon\Calendar\Gregorian\TimePeriod;
use Aeon\Calendar\TimeUnit;

/**
 * @psalm-immutable
 */
final class NonWorkingPeriod implements NonBusinessDay
{
    private TimePeriod $timePeriod;

    public function __construct(TimePeriod $timePeriod)
    {
        $this->timePeriod = $timePeriod;
    }

    public function is(Day $day) : bool
    {
        $days = $this->timePeriod
            ->iterate(TimeUnit::day(), Interval::rightOpen())
            ->filter(function (TimePeriod $timePeriod) use ($day) : bool {
                return $timePeriod->start()->day()->isEqual($day);
            });

        return (bool) \count($days);
    }
}
