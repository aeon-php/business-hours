<?php

declare(strict_types=1);

namespace Aeon\Calendar\Tests\Unit\Calendar\Gregorian\BusinessHours;

use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours;
use Aeon\Calendar\Gregorian\Time;
use PHPUnit\Framework\TestCase;

final class WorkingHoursTest extends TestCase
{
    public function test_creating_working_hours_with_ending_hours_before_starting_hours() : void
    {
        $this->expectExceptionMessage('End hour needs to be greater than start hour');

        new WorkingHours(Time::fromString('8pm'), Time::fromString('7pm'));
    }
}
