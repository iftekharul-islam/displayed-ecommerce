<?php

namespace App\Constants;

class ShortUrlConstant
{
    public const VALID = 200;
    public const INVALID = 404;
    public const EXPIRED = 498;

    public const EXPIRED_NEXT_THREE_DAYS = 3;
    public const EXPIRED_NEXT_SEVEN_DAYS = 7;
    public const EXPIRED_NEXT_FIFTEEN_DAYS = 15;
    public const EXPIRED_NEXT_ONE_MONTH = 30;
    public const EXPIRED_NEXT_THREE_MONTHS = 90;
    public const EXPIRED_NEXT_SIX_MONTHS = 180;
    public const ALL = -1;
}
