<?php

declare(strict_types=1);

namespace Zaphyr\Cookie\Contracts;

use DateTimeInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface CookieManagerInterface
{
    /**
     * @param string                            $name
     * @param string                            $value
     * @param DateTimeInterface|int|string|null $expire
     * @param string|null                       $path
     * @param string|null                       $domain
     * @param bool|null                         $secure
     * @param bool|null                         $httpOnly
     * @param bool|null                         $raw
     * @param string|null                       $sameSite
     *
     * @return CookieInterface
     */
    public function create(
        string $name,
        string $value,
        DateTimeInterface|int|string|null $expire = null,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?bool $raw = null,
        ?string $sameSite = null
    ): CookieInterface;

    /**
     * @param string      $name
     * @param string      $value
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null   $secure
     * @param bool|null   $httpOnly
     * @param bool|null   $raw
     * @param string|null $sameSite
     *
     * @return CookieInterface
     */
    public function forever(
        string $name,
        string $value,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?bool $raw = null,
        ?string $sameSite = null
    ): CookieInterface;

    /**
     * @param string      $name
     * @param string|null $path
     * @param string|null $domain
     *
     * @return CookieInterface
     */
    public function forget(string $name, ?string $path = null, ?string $domain = null): CookieInterface;

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getQueued(string $name, mixed $default = null): mixed;

    /**
     * @return CookieInterface[]
     */
    public function getAllQueued(): array;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasQueued(string $name): bool;

    /**
     * @param CookieInterface $cookie
     *
     * @return void
     */
    public function addToQueue(CookieInterface $cookie): void;

    /**
     * @param string $name
     *
     * @return void
     */
    public function removeFromQueue(string $name): void;
}
