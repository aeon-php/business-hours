<?php

declare(strict_types=1);

namespace Aeon\Calendar\Tests\Functional;

use Aeon\Calendar\Gregorian\BusinessHours;
use Aeon\Calendar\Gregorian\BusinessHours\BusinessDays;
use Aeon\Calendar\Gregorian\BusinessHours\BusinssDay\CustomBusinessDay;
use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDay\Holidays;
use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDay\NonWorkingPeriod;
use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDays;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours;
use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\Gregorian\Day;
use Aeon\Calendar\Gregorian\Exception\BusinessDayException;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendar\CountryCodes;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendarRegionalHolidays;
use Aeon\Calendar\Gregorian\Time;
use PHPUnit\Framework\TestCase;

final class BusinessHoursTest extends TestCase
{
    public function test_working_days_during_holiday_with_one_custom_working_day_and_time_period_of_non_working_days() : void
    {
        $regionalHolidays = new GoogleCalendarRegionalHolidays(CountryCodes::US);

        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            $customBusinessDays = new BusinessDays(
                new CustomBusinessDay(Day::fromString('2020-01-03'), new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm')))
            ),
            $nonBusinessDays = new NonBusinessDays(
                new Holidays($regionalHolidays),
                new NonWorkingPeriod(
                    DateTime::fromString('2020-01-02')->to(DateTime::fromString('2020-01-10'))
                )
            )
        );

        $this->assertFalse($businessDays->isOpenOn(Day::fromString('2020-01-01'))); // false - new year holiday
        $this->assertFalse($businessDays->isOpenOn(Day::fromString('2020-01-02'))); // false - non business day
        $this->assertTrue($businessDays->isOpenOn(Day::fromString('2020-01-03'))); // true  - custom business day
        $this->assertFalse($businessDays->isOpenOn(Day::fromString('2020-01-04'))); // false - non business day
        $this->assertFalse($businessDays->isOpenOn(Day::fromString('2020-01-11'))); // false - weekend
        $this->assertFalse($businessDays->isOpenOn(Day::fromString('2020-01-12'))); // false - weekend
        $this->assertTrue($businessDays->isOpenOn(Day::fromString('2020-01-13'))); // true
    }

    public function test_checking_open_hours() : void
    {
        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            BusinessDays::none(),
            NonBusinessDays::none(),
        );

        $this->assertFalse($businessDays->isOpen(DateTime::fromString('2020-01-01 7:59am')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-01 8:00am')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-01 5:59pm')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-01 6:00pm')));
        $this->assertFalse($businessDays->isOpen(DateTime::fromString('2020-01-01 6:01pm')));
    }

    public function test_checking_open_hours_for_custom_business_day() : void
    {
        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            new BusinessDays(
                new CustomBusinessDay(
                    Day::fromString('2020-01-02'),
                    new LinearWorkingHours(Time::fromString('6 am'), Time::fromString('8 pm'))
                )
            ),
            new NonBusinessDays(
                new BusinessHours\NonBusinessDay\NonWorkingDay(
                    Day::fromString('2020-01-02')
                )
            ),
        );

        $this->assertFalse($businessDays->isOpen(DateTime::fromString('2020-01-02 5:59am')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-02 6:00am')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-02 6:00pm')));
        $this->assertTrue($businessDays->isOpen(DateTime::fromString('2020-01-02 8:00pm')));
        $this->assertFalse($businessDays->isOpen(DateTime::fromString('2020-01-02 8:01pm')));
    }

    public function test_finding_next_business_day_from_holiday() : void
    {
        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            BusinessDays::none(),
            new NonBusinessDays(
                new Holidays(new GoogleCalendarRegionalHolidays(CountryCodes::US))
            )
        );

        $this->assertSame(2, $businessDays->nextBusinessDay(Day::fromString('2020-01-01'))->number());
        $this->assertSame(3, $businessDays->nextBusinessDay(Day::fromString('2020-01-02'))->number());
        $this->assertSame(13, $businessDays->nextBusinessDay(Day::fromString('2020-01-11'))->number()); // saturday
    }

    public function test_working_hours() : void
    {
        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            BusinessDays::none(),
            NonBusinessDays::none(),
        );

        $this->assertSame(8, $businessDays->workingHours(Day::fromString('2020-01-01'))->openFrom()->hour());
        $this->assertTrue($businessDays->workingHours(Day::fromString('2020-01-01'))->openFrom()->isAM());
        $this->assertSame(18, $businessDays->workingHours(Day::fromString('2020-01-01'))->openTo()->hour());
        $this->assertFalse($businessDays->workingHours(Day::fromString('2020-01-01'))->openTo()->isAM());
    }

    public function test_working_hours_for_non_working_day() : void
    {
        $businessDays = new BusinessHours(
            $businessDays = BusinessDays::mondayFriday(
                new LinearWorkingHours(Time::fromString('8 am'), Time::fromString('6 pm'))
            ),
            BusinessDays::none(),
            new NonBusinessDays(
                new Holidays(new GoogleCalendarRegionalHolidays(CountryCodes::US))
            )
        );

        $this->expectException(BusinessDayException::class);
        $this->expectExceptionMessage('2020-01-01 is not a business day');

        $this->assertSame(8, $businessDays->workingHours(Day::fromString('2020-01-01'))->openFrom()->hour());
    }

    public function test_working_hours_when_non_business_or_custom_business_days_are_defined() : void
    {
        $businessDays = new BusinessHours(
            BusinessDays::none(),
            BusinessDays::none(),
            NonBusinessDays::none()
        );

        $this->expectException(BusinessDayException::class);
        $this->expectExceptionMessage('2020-01-01 is not a business day');

        $this->assertSame(8, $businessDays->workingHours(Day::fromString('2020-01-01'))->openFrom()->hour());
    }
}
