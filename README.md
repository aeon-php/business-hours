# Aeon 

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/aeon-php/business-hours/license)](//packagist.org/packages/aeon-php/business-hours)
![Tests](https://github.com/aeon-php/business-hours/workflows/Tests/badge.svg?branch=1.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Faeon-php%2Fbusiness-hours%2F1.x)](https://dashboard.stryker-mutator.io/reports/github.com/aeon-php/business-hours/1.x)


Time Management Framework for PHP

> The word aeon /ˈiːɒn/, also spelled eon (in American English), originally meant "life", "vital force" or "being", 
> "generation" or "a period of time", though it tended to be translated as "age" in the sense of "ages", "forever", 
> "timeless" or "for eternity".

[Source: Wikipedia](https://en.wikipedia.org/wiki/Aeon) 

Define working hours and check Date Time against them, exclude holidays, 
define exceptions and custom working days. 

Business Hours class takes 3 parameters, described below. 

```php
<?php
use Aeon\Calendar\Gregorian\BusinessHours\BusinessDays;
use Aeon\Calendar\Gregorian\BusinessHours\NonBusinessDays;

final class BusinessHours
{
    /**
     * @param BusinessDays $regularBusinessDays - lowest priority when checking open hours, overwrites nothing
     * @param BusinessDays $customBusinessDays - highest priority when checking open hours, overwrites business days and non business days
     * @param NonBusinessDays $nonBusinessDays - medium priority when checking open hours, overwrites regular business days
     */
    public function __construct(BusinessDays $regularBusinessDays, BusinessDays $customBusinessDays, NonBusinessDays $nonBusinessDays)
    {
    }
}
``` 

So it's all about the priority of execution, custom business days comes over non-business days,
and non-business days comes over regular business days. 

Working Hours can be dined in linear way but also as a collection of shifts (if you take a break in the middle of the day).

* `\Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours();`
* `\Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\ShiftsWorkingHours()`

```php
<?php
use Aeon\Calendar\Gregorian\Time;

interface WorkingHours
{
    public function openFrom() : Time;

    public function openTo() : Time;

    public function isOpen(Time $time) : bool;
}
```

### Simple business open from Monday to Friday 6am - 6pm 

```php
<?php

use Aeon\Calendar\Gregorian\BusinessHours;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours;
use Aeon\Calendar\Gregorian\Time;
use \Aeon\Calendar\Gregorian\DateTime;

$businessHours = new BusinessHours(
    $regularBusinessDays = BusinessHours\BusinessDays::mondayFriday(
        new LinearWorkingHours(Time::fromString('6am'), Time::fromString('6pm'))
    ),
    BusinessHours\BusinessDays::none(),
    BusinessHours\NonBusinessDays::none()
);

$businessHours->isOpen(DateTime::fromString('2020-01-03 8am')); // true
```

### Monday - Friday 6am - 6pm, Weekends 11am - 6pm 

```php
<?php

use Aeon\Calendar\Gregorian\BusinessHours;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours;
use Aeon\Calendar\Gregorian\Time;
use \Aeon\Calendar\Gregorian\DateTime;

$businessHours = new BusinessHours(
    $regularBusinessDays = BusinessHours\BusinessDays::wholeWeek(
        $weekWorkingHours = new LinearWorkingHours(Time::fromString('6am'), Time::fromString('6pm')),
        $weekendWorkingHours = new LinearWorkingHours(Time::fromString('11am'), Time::fromString('6pm'))
    ),
    BusinessHours\BusinessDays::none(),
    BusinessHours\NonBusinessDays::none()
);

$businessHours->isOpen(DateTime::fromString('2020-01-03 8am')); // true
```
### Closed during regional holidays in Poland

```php
<?php

use Aeon\Calendar\Gregorian\BusinessHours;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendar\CountryCodes;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendarRegionalHolidays;
use Aeon\Calendar\Gregorian\Time;
use \Aeon\Calendar\Gregorian\Day;

$businessHours = new BusinessHours(
    $regularBusinessDays = BusinessHours\BusinessDays::wholeWeek(
        $weekWorkingHours = new LinearWorkingHours(Time::fromString('6am'), Time::fromString('6pm')),
        $weekendWorkingHours = new LinearWorkingHours(Time::fromString('11am'), Time::fromString('6pm'))
    ),
    BusinessHours\BusinessDays::none(),
    $nonBusinessDay = new BusinessHours\NonBusinessDays(
        new BusinessHours\NonBusinessDay\Holidays(
            new GoogleCalendarRegionalHolidays(CountryCodes::PL)
        )
    )
);

$businessHours->isOpenOn(Day::fromString('2020-06-11')); // false
```

### Closed during regional holidays in Poland but open on January first

```php
<?php

use Aeon\Calendar\Gregorian\BusinessHours;
use Aeon\Calendar\Gregorian\BusinessHours\WorkingHours\LinearWorkingHours;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendar\CountryCodes;
use Aeon\Calendar\Gregorian\Holidays\GoogleCalendarRegionalHolidays;
use Aeon\Calendar\Gregorian\Time;
use \Aeon\Calendar\Gregorian\Day;

$businessHours = new BusinessHours(
    $regularBusinessDays = BusinessHours\BusinessDays::wholeWeek(
        $weekWorkingHours = new LinearWorkingHours(Time::fromString('6am'), Time::fromString('6pm')),
        $weekendWorkingHours = new LinearWorkingHours(Time::fromString('11am'), Time::fromString('6pm'))
    ),
    $customBusinessDays = new BusinessHours\BusinessDays(
        new BusinessHours\BusinssDay\CustomBusinessDay(
            Day::fromString('2020-01-01'),
            new LinearWorkingHours(Time::fromString('11am'), Time::fromString('3pm'))
        )
    ),
    $nonBusinessDay = new BusinessHours\NonBusinessDays(
        new BusinessHours\NonBusinessDay\Holidays(
            new GoogleCalendarRegionalHolidays(CountryCodes::PL)
        )
    )
);

$businessHours->isOpenOn(Day::fromString('2020-06-11')); // false
```