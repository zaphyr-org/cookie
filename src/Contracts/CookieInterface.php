<?php

declare(strict_types=1);

namespace Zaphyr\Cookie\Contracts;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface CookieInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @return int
     */
    public function getExpire(): int;

    /**
     * @return int
     */
    public function getMaxAge(): int;

    /**
     * @return bool
     */
    public function isCleared(): bool;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string|null
     */
    public function getDomain(): ?string;

    /**
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * @return bool
     */
    public function isRaw(): bool;

    /**
     * @return string|null
     */
    public function getSameSite(): ?string;

    /**
     * @return string
     */
    public function __toString(): string;
}
