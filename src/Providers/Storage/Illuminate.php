<?php

/*
 * This file is part of jwt-auth
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Providers\Storage;

use BadMethodCallException;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class Illuminate implements Storage
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @var string
     */
    protected $tag = 'tymon.jwt';
    
    /**
     * @var bool
     */
    protected $supportsTags;

    /**
     * @param \Illuminate\Contracts\Cache\Repository  $cache
     */
    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add a new item into storage
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     *
     * @return void
     */
    public function add($key, $value, $minutes)
    {
        $this->cache()->put($key, $value, $minutes);
    }

    /**
     * Add a new item into storage forever
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever($key, $value)
    {
        $this->cache()->forever($key, $value);
    }

    /**
     * Get an item from storage
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache()->get($key);
    }

    /**
     * Remove an item from storage
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function destroy($key)
    {
        return $this->cache()->forget($key);
    }

    /**
     * Remove all items associated with the tag
     *
     * @return void
     */
    public function flush()
    {
        $this->cache()->flush();
    }

    /**
     * Return the cache instance with tags attached
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function cache()
    {
        if (is_null($this->supportsTags)) {
            $this->determineTagSupport();
        }

        if ($this->supportsTags) {
            return $this->cache->tags($this->tag);
        } else {
            return $this->cache;
        }
    }

    /**
     * Detect as best we can whether tags are supported with this repository & store,
     * and save our result on the $supportsTags flag.
     *
     * @return void
     */
    protected function determineTagSupport()
    {
        if (method_exists($this->cache, 'tags')) { // Laravel >= 5.1.28
            try {
                // Attempt the repository tags command, which throws exceptions when unsupported
                $this->cache->tags($this->tag);
                $this->supportsTags = true;
            } catch (BadMethodCallException $ex) {
                $this->supportsTags = false;
            }
        } else {
            if (method_exists($this->cache, 'getStore')) { // Laravel <= 5.1.27
                // Check for the tags function directly on the store
                $this->supportsTags = method_exists($this->cache->getStore(), 'tags');
            } else {
                // Must be using custom cache repository without getStore(), and all bets are off,
                // or we are mocking the cache contract (in testing), which will not create a getStore method
                $this->supportsTags = false;
            }
        }
    }
}
