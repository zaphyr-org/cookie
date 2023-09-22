<?php

declare(strict_types=1);

namespace Zaphyr\CookieTests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\Exceptions\CookieException;

class CookieTest extends TestCase
{
    /* -------------------------------------------------
     * CONSTRUCTOR AND GETTERS
     * -------------------------------------------------
     */

    public function testConstructorAndGetterMethods(): void
    {
        $cookie = new Cookie(
            $name = 'foo',
            $value = 'bar',
            $expire = 120,
            $path = '/foo',
            $domain = 'example.com',
            true,
            false,
            true,
            $sameSite = Cookie::RESTRICTION_LAX
        );

        self::assertSame($name, $cookie->getName());
        self::assertEquals($value, $cookie->getValue());
        self::assertEquals($expire, $cookie->getExpire());
        self::assertEquals(0, $cookie->getMaxAge());
        self::assertTrue($cookie->isCleared());
        self::assertEquals($path, $cookie->getPath());
        self::assertEquals($domain, $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertFalse($cookie->isHttpOnly());
        self::assertTrue($cookie->isRaw());
        self::assertEquals($sameSite, $cookie->getSameSite());
    }

    /**
     * @dataProvider invalidNamesDataProvider
     *
     * @param string $name
     */
    public function testConstructorWithNonRawCookie(string $name): void
    {
        $cookie = new Cookie($name, 'bar');

        self::assertFalse($cookie->isRaw());
    }

    /**
     * @dataProvider invalidNamesDataProvider
     *
     * @param string $name
     */
    public function testConstructorWithRawCookieThrowsExceptionOnInvalidName(string $name): void
    {
        $this->expectException(CookieException::class);

        new Cookie($name, 'baz', raw: true);
    }

    public function testConstructorThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(CookieException::class);

        new Cookie('', 'baz');
    }

    /**
     * @return array<string[]>
     */
    public static function invalidNamesDataProvider(): array
    {
        return [
            [',foo'],
            [';foo'],
            [' foo'],
            ["\tfoo"],
            ["\rfoo"],
            ["\nfoo"],
            ["\vfoo"],
            ["\ffoo"],
        ];
    }

    /* -------------------------------------------------
     * EXPIRE
     * -------------------------------------------------
     */

    public function testGetExpireWithInteger(): void
    {
        $expire = 120;
        $cookie = new Cookie('foo', 'bar', $expire);

        self::assertEquals($expire, $cookie->getExpire());
    }

    public function testGetExpireWithDateTimeInstance(): void
    {
        $expire = new DateTime('+2 days');
        $cookie = new Cookie('foo', 'bar', $expire);

        self::assertEquals($expire->getTimestamp(), $cookie->getExpire());
    }

    public function testGetExpireWithNonNumeric(): void
    {
        $cookie = new Cookie('foo', 'bar', 'now');

        self::assertEquals(time(), $cookie->getExpire());
    }

    public function testExpireThrowsExceptionOnInvalidValue(): void
    {
        $this->expectException(CookieException::class);

        new Cookie('foo', 'bar', 'never');
    }

    public function testGetExpireReturnsZeroByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertEquals(0, $cookie->getExpire());
    }

    public function testGetExpireReturnsZeroOnNegativeExpiration(): void
    {
        $cookie = new Cookie('foo', 'bar', -120);

        self::assertEquals(0, $cookie->getExpire());
    }

    /* ------------------------------------------
     * MAX AGE
     * ------------------------------------------
     */

    public function testGetMaxAge(): void
    {
        $cookie = new Cookie('foo', 'bar', new DateTime('+2 days'));

        self::assertGreaterThan(0, $cookie->getMaxAge());
    }

    /* ------------------------------------------
     * CLEARED
     * ------------------------------------------
     */

    public function testIsCleared(): void
    {
        $cookie = new Cookie('foo', 'bar', time() - 120);

        self::assertTrue($cookie->isCleared());

        $cookie = new Cookie('foo', 'bar', new DateTime('+2 days'));

        self::assertFalse($cookie->isCleared());
    }

    /* ------------------------------------------
     * PATH
     * ------------------------------------------
     */

    public function testGetPathReturnsRootPathOnEmptyString(): void
    {
        $cookie = new Cookie('foo', 'bar', path: '');

        self::assertEquals('/', $cookie->getPath());
    }

    public function testGetPathReturnsRootPathByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');
        self::assertEquals('/', $cookie->getPath());
    }

    /* ------------------------------------------
     * DOMAIN
     * ------------------------------------------
     */

    public function testGetDomainReturnsNullByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertNull($cookie->getDomain());
    }

    /* ------------------------------------------
     * SECURE
     * ------------------------------------------
     */

    public function testIsSecureReturnsFalseByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertFalse($cookie->isSecure());
    }

    /* ------------------------------------------
     * HTTP ONLY
     * ------------------------------------------
     */

    public function testIsHttpOnlyReturnsTrueByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertTrue($cookie->isHttpOnly());
    }

    /* ------------------------------------------
     * RAW
     * ------------------------------------------
     */

    public function testIsRawReturnsFalseByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertFalse($cookie->isRaw());
    }

    /* ------------------------------------------
     * SAME SITE
     * ------------------------------------------
     */

    public function testGetSameSiteWithLax(): void
    {
        $cookie = new Cookie('foo', 'bar', sameSite: Cookie::RESTRICTION_LAX);

        self::assertEquals(Cookie::RESTRICTION_LAX, $cookie->getSameSite());
    }

    public function testGetSameSiteWithStrict(): void
    {
        $cookie = new Cookie('foo', 'bar', sameSite: Cookie::RESTRICTION_STRICT);

        self::assertEquals(Cookie::RESTRICTION_STRICT, $cookie->getSameSite());
    }

    public function testGetSameSiteReturnsNullByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertNull($cookie->getSameSite());
    }

    public function testConstructorThrowsExceptionOnInvalidSameSiteValue(): void
    {
        $this->expectException(CookieException::class);

        new Cookie('foo', 'bar', sameSite: 'invalid');
    }

    /* ------------------------------------------
     * TO STRING
     * ------------------------------------------
     */

    public function testToString(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertEquals('foo=bar; path=/; httponly', (string)$cookie);
    }

    public function testToStringRaw(): void
    {
        $cookie = new Cookie('foo', 'b+a+r', raw: true);

        self::assertEquals('foo=b+a+r; path=/; httponly', (string)$cookie);
    }

    public function testToStringDeletedCookie(): void
    {
        $cookie = new Cookie('foo', '', 1, '/foo', 'example.com');

        self::assertEquals(
            'foo=deleted; expires='
            . gmdate('D, d-M-Y H:i:s T', time() - 31536001)
            . '; Max-Age=0; path=/foo; domain=example.com; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithExpireDate(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com'
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithSecureTrue(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            true
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; secure; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithHttpFalse(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            false,
            false
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com',
            (string)$cookie
        );
    }

    public function testToStringWithSameSiteLax(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            true,
            true,
            false,
            'LAX'
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; secure; httponly; samesite=lax',
            (string)$cookie
        );
    }

    public function testToStringValueWithSpace(): void
    {
        $cookie = new Cookie('foo', 'bar with spaces');

        self::assertEquals('foo=bar%20with%20spaces; path=/; httponly', (string)$cookie);
    }
}
