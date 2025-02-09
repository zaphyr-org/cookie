<?php

declare(strict_types=1);

namespace Zaphyr\Cookie;

use DateTimeInterface;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Cookie\Exceptions\CookieException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CookieManager implements CookieManagerInterface
{
    /**
     * @var CookieInterface[]
     */
    protected array $queued = [];

    /**
     * @param DateTimeInterface|int|string $expire
     * @param string                       $path
     * @param string|null                  $domain
     * @param bool                         $secure
     * @param bool                         $httpOnly
     * @param bool                         $raw
     * @param string                       $sameSite
     *
     * @throws CookieException if the expiration date or same site restriction is invalid
     */
    public function __construct(
        protected DateTimeInterface|int|string $expire = 0,
        protected string $path = '/',
        protected ?string $domain = null,
        protected bool $secure = false,
        protected bool $httpOnly = true,
        protected bool $raw = false,
        protected string $sameSite = Cookie::RESTRICTION_LAX
    ) {
        $this->expire = Utils::prepareExpire($this->expire);
        $this->sameSite = Utils::validateSameSiteRestrictions($this->sameSite);
        $this->path = empty($this->path) ? '/' : $this->path;
    }

    /**
     *{@inheritdoc}
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
    ): CookieInterface {
        $expire = $expire ?? $this->expire;
        $path = $path ?? $this->path;
        $domain = $domain ?? $this->domain;
        $secure = $secure ?? $this->secure;
        $httpOnly = $httpOnly ?? $this->httpOnly;
        $raw = $raw ?? $this->raw;
        $sameSite = $sameSite ?? $this->sameSite;

        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     *{@inheritdoc}
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
    ): CookieInterface {
        return $this->create($name, $value, strtotime('+1 year'), $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     *{@inheritdoc}
     */
    public function forget(string $name, ?string $path = null, ?string $domain = null): CookieInterface
    {
        return $this->create($name, '', strtotime('-1 hour'), $path, $domain);
    }

    /**
     *{@inheritdoc}
     */
    public function getQueued(string $name, mixed $default = null): mixed
    {
        return $this->queued[$name] ?? $default;
    }

    /**
     *{@inheritdoc}
     */
    public function getAllQueued(): array
    {
        return $this->queued;
    }

    /**
     *{@inheritdoc}
     */
    public function hasQueued(string $name): bool
    {
        return $this->getQueued($name) !== null;
    }

    /**
     *{@inheritdoc}
     */
    public function addToQueue(CookieInterface $cookie): void
    {
        $this->queued[$cookie->getName()] = $cookie;
    }

    /**
     *{@inheritdoc}
     */
    public function removeFromQueue(string $name): void
    {
        if ($this->hasQueued($name)) {
            unset($this->queued[$name]);
        }
    }
}
