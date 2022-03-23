<?php

namespace AlphaSnow\OSS\AppServer;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Factory as Cache;

class LaravelCacheCallback implements SimpleCallbackInterface
{
    const KEY_PUBLIC = "oss-appserver:public-key";

    /**
     * @var LaravelCallback
     */
    protected $laravelCallback;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param LaravelCallback $laravelCallback
     * @param Cache $cache
     */
    public function __construct(LaravelCallback $laravelCallback, Cache $cache)
    {
        $this->laravelCallback = $laravelCallback;
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function verifyByRequest()
    {
        $cacheKey = self::KEY_PUBLIC.":".$this->laravelCallback->getRequest()->server(Callback::KEY_PUB);
        $publicKey = $this->cache->store()->get($cacheKey);
        if (!$publicKey) {
            $publicKey = $this->laravelCallback->getCallback()->getPublicKey(Callback::KEY_PUB);
            if (!$publicKey) {
                return false;
            }
            $this->cache->store()->put($cacheKey, $publicKey, Carbon::now()->addHour());
        }
        $this->laravelCallback->getCallback()->setPublicKey($publicKey);

        return $this->laravelCallback->verifyByRequest();
    }
}
