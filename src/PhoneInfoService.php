<?php

namespace Oforostianyi\Recipient;
class PhoneInfoService
{
    private static $phoneInfoProvider;
    private static $cache;

    public static function setPhoneInfoProvider(PhoneInfoProvider $provider)
    {
        self::$phoneInfoProvider = $provider;
    }

    public static function setCache(MemoryCache $cache)
    {
        self::$cache = $cache;
    }

    public static function getPhoneInfo($msisdn)
    {
        $cachedInfo = self::$cache->get($msisdn);
        if ($cachedInfo) {
            return $cachedInfo;
        }

        $phoneInfo = self::$phoneInfoProvider->getPhoneInfo($msisdn);
        self::$cache->set($msisdn, $phoneInfo);

        return $phoneInfo;
    }
}
