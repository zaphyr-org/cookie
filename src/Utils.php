<?php

declare(strict_types=1);

namespace Zaphyr\Cookie;

use DateTimeInterface;
use Zaphyr\Cookie\Exceptions\CookieException;

/**
 * @author   merloxx <merloxx@zaphyr.org>
 * @internal This class is not part of the public API of this package and may change at any time without notice
 */
class Utils
{
    /**
     * @param DateTimeInterface|int|string $expire
     *
     * @throws CookieException if the expiration time is not valid
     * @return int
     */
    public static function prepareExpire(DateTimeInterface|int|string $expire): int
    {
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        }

        if (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if ($expire === false) {
                throw new CookieException('Cookie expiration time is not valid.');
            }
        }

        return $expire > 0 ? (int)$expire : 0;
    }

    /**
     * @param string $sameSite
     *
     * @throws CookieException if the sameSite parameter is not valid
     * @return string
     */
    public static function validateSameSiteRestrictions(string $sameSite): string
    {
        $sameSite = strtolower($sameSite);

        if (
            !in_array(
                $sameSite,
                [Cookie::RESTRICTION_LAX, Cookie::RESTRICTION_STRICT, Cookie::RESTRICTION_NONE],
                true
            )
        ) {
            throw new CookieException(
                'Cookie sameSite parameter "' . $sameSite . '" is not valid. '
                . 'Must be "' . Cookie::RESTRICTION_LAX . '", "' . Cookie::RESTRICTION_STRICT . '" or "' . Cookie::RESTRICTION_NONE . '".',
            );
        }

        return $sameSite;
    }
}
