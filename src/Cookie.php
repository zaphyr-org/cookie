<?php

declare(strict_types=1);

namespace Zaphyr\Cookie;

use DateTimeInterface;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Exceptions\CookieException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Cookie implements CookieInterface
{
    /**
     * @const string
     */
    public const RESTRICTION_LAX = 'lax';

    /**
     * @const string
     */
    public const RESTRICTION_STRICT = 'strict';

    /**
     * @const string
     */
    public const RESTRICTION_NONE = 'none';

    /**
     * @var int
     */
    protected int $expire;

    /**
     * @param string                       $name
     * @param string                       $value
     * @param DateTimeInterface|int|string $expire
     * @param string                       $path
     * @param string|null                  $domain
     * @param bool                         $secure
     * @param bool                         $httpOnly
     * @param bool                         $raw
     * @param string|null                  $sameSite
     *
     * @throws CookieException
     */
    public function __construct(
        protected string $name,
        protected string $value,
        protected DateTimeInterface|int|string $expire = 0,
        DateTimeInterface|int|string $expire = 0,
        protected string $path = '/',
        protected string|null $domain = null,
        protected bool $secure = false,
        protected bool $httpOnly = true,
        protected bool $raw = false,
        protected string|null $sameSite = null
    ) {
        if ($this->isRaw() && strpbrk($this->name, "/[=,; \t\r\n\v\f]/") !== false) {
            throw new CookieException('Cookie name "' . $this->name . '" contains invalid characters.');
        }

        if (empty($this->name)) {
            throw new CookieException('Cookie name cannot be empty.');
        }

        $this->expire = Utils::prepareExpire($expire);
        $this->path = empty($this->path) ? '/' : $this->path;

        if ($this->sameSite !== null) {
            $this->sameSite = Utils::validateSameSiteRestrictions($this->sameSite);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge(): int
    {
        $maxMage = $this->expire - time();

        return max($maxMage, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function isCleared(): bool
    {
        return $this->expire !== 0 && $this->expire < time();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * {@inheritdoc}
     */
    public function getSameSite(): string|null
    {
        return $this->sameSite;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->isRaw()) {
            $cookieString = $this->getName();
        } else {
            $cookieString = str_replace(
                ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"],
                ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'],
                $this->getName()
            );
        }

        $cookieString .= '=';

        if ($this->getValue() === '') {
            $cookieString .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s \G\M\T', time() - 31536001) . '; Max-Age=0';
        } else {
            $cookieString .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            if ($this->getExpire() !== 0) {
                $cookieString .= '; expires=' . gmdate(
                    'D, d-M-Y H:i:s \G\M\T',
                    $this->expire
                ) . '; Max-Age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath()) {
            $cookieString .= '; path=' . $this->getPath();
        }

        if ($this->getDomain()) {
            $cookieString .= '; domain=' . $this->getDomain();
        }

        if ($this->isSecure()) {
            $cookieString .= '; secure';
        }

        if ($this->isHttpOnly()) {
            $cookieString .= '; httponly';
        }

        if ($this->getSameSite() !== null) {
            $cookieString .= '; samesite=' . $this->getSameSite();
        }

        return $cookieString;
    }
}
