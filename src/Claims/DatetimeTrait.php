<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Claims;

use DateInterval;
use DateTimeInterface;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
use Tymon\JWTAuth\Support\Utils;

trait DatetimeTrait
{
    /**
     * Time leeway in seconds.
     *
     * @var int
     */
    protected $leeway = 0;

    /**
     * @param  mixed  $value
     * @param  int  $leeway
     *
     * @return void
     */
    public function __construct($value, $leeway = 0)
    {
        $this->leeway = $leeway;

        parent::__construct($value);
    }

    /**
     * Set the claim value, and call a validate method.
     *
     * @param  mixed  $value
     *
     * @throws \Tymon\JWTAuth\Exceptions\InvalidClaimException
     *
     * @return $this
     */
    public function setValue($value)
    {
        if ($value instanceof DateInterval) {
            $value = Utils::now()->add($value);
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->getTimestamp();
        }

        return parent::setValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function validateCreate($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidClaimException($this);
        }

        return $value;
    }

    /**
     * Determine whether the value is in the future.
     *
     * @param  mixed  $value
     *
     * @return bool
     */
    protected function isFuture($value)
    {
        return Utils::isFuture($value, $this->leeway);
    }

    /**
     * Determine whether the value is in the past.
     *
     * @param  mixed  $value
     *
     * @return bool
     */
    protected function isPast($value)
    {
        return Utils::isPast($value, $this->leeway);
    }

    /**
     * Set the leeway in seconds.
     *
     * @param  int  $leeway
     *
     * @return $this
     */
    public function setLeeway($leeway)
    {
        $this->leeway = $leeway;

        return $this;
    }
}
