<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests;

use PHPUnit\Framework\TestCase;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\CookieManager;
use Zaphyr\Cookie\Exceptions\CookieException;

class CookieManagerTest extends TestCase
{
    /**
     * @var CookieManager
     */
    protected CookieManager $cookieManager;

    public function setUp(): void
    {
        $this->cookieManager = new CookieManager();
    }

    public function tearDown(): void
    {
        unset($this->cookieManager);
    }

    /* ------------------------------------------
     * CONSTRUCTOR
     * ------------------------------------------
     */

    public function testConstructor(): void
    {
        $factory = new CookieManager(
            $expire = '+1 day',
            $path = '/foo',
            $domain = 'example.com',
            true,
            false,
            true,
            Cookie::RESTRICTION_STRICT
        );

        $cookie = $factory->create('foo', 'bar');

        self::assertEquals(strtotime($expire), $cookie->getExpire());
        self::assertEquals(86400, $cookie->getMaxAge());
        self::assertFalse($cookie->isCleared());
        self::assertEquals($path, $cookie->getPath());
        self::assertEquals($domain, $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertFalse($cookie->isHttpOnly());
        self::assertTrue($cookie->isRaw());
        self::assertEquals('strict', $cookie->getSameSite());

        $forever = $factory->forever('foo', 'bar');

        self::assertEquals(2147483647, $forever->getExpire());
        self::assertEquals($forever->getExpire() - time(), $forever->getMaxAge());
        self::assertFalse($forever->isCleared());
        self::assertEquals($path, $forever->getPath());
        self::assertEquals($domain, $forever->getDomain());
        self::assertTrue($forever->isSecure());
        self::assertFalse($forever->isHttpOnly());
        self::assertEquals('strict', $forever->getSameSite());

        $forget = $factory->forget('foo');

        self::assertEquals(strtotime('-1 hour'), $forget->getExpire());
        self::assertEquals(0, $forget->getMaxAge());
        self::assertTrue($forget->isCleared());
        self::assertEquals($path, $forget->getPath());
        self::assertEquals($domain, $forget->getDomain());
        self::assertTrue($forget->isSecure());
        self::assertFalse($forget->isHttpOnly());
        self::assertEquals('strict', $forget->getSameSite());
    }

    public function testConstructorThrowsExceptionOnInvalidExpire(): void
    {
        $this->expectException(CookieException::class);

        new CookieManager('foo', 'bar', 'never');
    }

    public function testConstructorThrowsExceptionOnInvalidSameSite(): void
    {
        $this->expectException(CookieException::class);

        new CookieManager(0, '/', 'example.com', true, false, false, 'invalid');
    }

    public function testConstructorChangesPathToRootWhenEmptyString(): void
    {
        $factory = new CookieManager(0, '');

        self::assertEquals('/', $factory->create('foo', 'bar')->getPath());
    }

    /* ------------------------------------------
     * CREATE
     * ------------------------------------------
     */

    public function testCreate(): void
    {
        $cookie = (new CookieManager())->create('foo', 'bar');

        self::assertEquals('foo=bar; path=/; httponly; samesite=lax', (string)$cookie);
        self::assertEquals('foo', $cookie->getName());
        self::assertEquals('bar', $cookie->getValue());
        self::assertEquals(0, $cookie->getExpire());
        self::assertEquals(0, $cookie->getMaxAge());
        self::assertFalse($cookie->isCleared());
        self::assertEquals('/', $cookie->getPath());
        self::assertNull($cookie->getDomain());
        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertEquals(Cookie::RESTRICTION_LAX, $cookie->getSameSite());
    }

    /* ------------------------------------------
     * FOREVER
     * ------------------------------------------
     */

    public function testForever(): void
    {
        $cookie = (new CookieManager())->forever('foo', 'bar');
        $expire = gmdate('D, d-M-Y H:i:s T', $timestamp = 2147483647);

        self::assertEquals(
            'foo=bar; expires=' . $expire . '; Max-Age=' . ($timestamp - time()) . '; path=/; httponly; samesite=lax',
            (string)$cookie
        );
        self::assertEquals('foo', $cookie->getName());
        self::assertEquals('bar', $cookie->getValue());
        self::assertEquals($timestamp, $cookie->getExpire());
        self::assertEquals($timestamp - time(), $cookie->getMaxAge());
        self::assertFalse($cookie->isCleared());
        self::assertEquals('/', $cookie->getPath());
        self::assertNull($cookie->getDomain());
        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertEquals(Cookie::RESTRICTION_LAX, $cookie->getSameSite());
    }

    /**
     * ------------------------------------------
     * FORGET
     * ------------------------------------------
     */

    public function testForget(): void
    {
        $cookie = (new CookieManager())->forget('foo');
        $expire = gmdate('D, d-M-Y H:i:s T', strtotime('-1 hour'));

        self::assertEquals($expire, gmdate('D, d-M-Y H:i:s T', $cookie->getExpire()));
        self::assertTrue($cookie->isCleared());
    }

    /**
     * ------------------------------------------
     * GET QUEUED
     * ------------------------------------------
     */

    public function testGetQueued(): void
    {
        $this->cookieManager->addToQueue($this->cookieManager->create('foo', 'bar'));

        self::assertInstanceOf(CookieInterface::class, $this->cookieManager->getQueued('foo'));
    }

    public function testGetQueuedWithDefaultValue(): void
    {
        self::assertNull($this->cookieManager->getQueued('nope'));
        self::assertSame('bar', $this->cookieManager->getQueued('foo', 'bar'));
    }

    /**
     * ------------------------------------------
     * GET ALL QUEUED
     * ------------------------------------------
     */

    public function testGetAllQueued(): void
    {
        $this->cookieManager->addToQueue($this->cookieManager->create('foo', 'bar'));

        self::assertInstanceOf(CookieInterface::class, $this->cookieManager->getAllQueued()['foo']);
    }

    public function testGetAllQueuedCookiesReturnsEmptyArrayByDefault(): void
    {
        self::assertEmpty($this->cookieManager->getAllQueued());
    }

    /**
     * ------------------------------------------
     * HAS QUEUED
     * ------------------------------------------
     */

    public function testHasQueued(): void
    {
        $this->cookieManager->addToQueue($this->cookieManager->create('foo', 'bar'));

        self::assertTrue($this->cookieManager->hasQueued('foo'));
    }

    public function testHasQueuedReturnsFalse(): void
    {
        self::assertFalse($this->cookieManager->hasQueued('baz'));
    }

    /**
     * ------------------------------------------
     * ADD TO QUEUE
     * ------------------------------------------
     */

    public function testAddToQueue(): void
    {
        $this->cookieManager->addToQueue($this->cookieManager->create($name = 'foo', 'bar'));

        self::assertInstanceOf(CookieInterface::class, $this->cookieManager->getQueued($name));
        self::assertArrayHasKey($name, $this->cookieManager->getAllQueued());
        self::assertTrue($this->cookieManager->hasQueued($name));
    }

    /**
     * ------------------------------------------
     * REMOVE QUEUED
     * ------------------------------------------
     */

    public function testRemoveQueued(): void
    {
        $this->cookieManager->addToQueue($this->cookieManager->create($name = 'foo', 'bar'));

        self::assertTrue($this->cookieManager->hasQueued($name));

        $this->cookieManager->removeFromQueue($name);

        self::assertFalse($this->cookieManager->hasQueued($name));
    }
}
