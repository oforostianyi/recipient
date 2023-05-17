<?php

namespace Oforostianyi\Recipient;

/**
 * Simple in memory key value storage
 * Olexander Forostianyi aka ZViruS
 * 2023-05-17
 */
class MemoryCache implements CacheInterface
{
    private static array $CACHE = ['DATA' => [], 'TTL' => []];

    /**
     * Writes data to the cache
     * @param string $prefix
     * @param string $dataSetName
     * @param array $dataToStore
     * @param int $ttl in seconds
     * @return bool
     */
    public static function set(string $prefix, string $dataSetName, array $dataToStore = [], int $ttl = 60): bool
    {
        if ($ttl < 1|| $dataSetName == '' || $prefix == '' || empty($dataToStore) || !is_array($dataToStore)) {
            return false;
        }

        if (!isset(self::$CACHE['DATA'][$prefix])) {
            self::$CACHE['DATA'][$prefix] = [$dataSetName => []];
        }

        foreach ($dataToStore as $key => $value) {
            self::$CACHE['DATA'][$prefix][$dataSetName][$key] = $value;
        }

        if (isset(self::$CACHE['TTL'][$prefix][$dataSetName])) {
            if (self::$CACHE['TTL'][$prefix][$dataSetName] >= time()) {
                return true;
            }
        }

        self::$CACHE['TTL'][$prefix][$dataSetName] = time() + $ttl;

        return true;
    }

    /**
     * Reads data from the cache. If key not set, return all dataDet
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return mixed|null
     */
    public static function get(string $prefix, string $dataSetName, string $getKey = '')
    {
        if (self::checkDatasetIsExist($prefix, $dataSetName)) {
            if ($getKey === '') {
                return self::$CACHE['DATA'][$prefix][$dataSetName];
            }
            if (self::checkKey($prefix, $dataSetName, $getKey)) {
                return self::$CACHE['DATA'][$prefix][$dataSetName][$getKey];
            }
        }
        return null;
    }

    /**
     * Checks if the key exists in the cache
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return bool
     */
    public static function checkKey(string $prefix, string $dataSetName, string $getKey): bool
    {
        return (self::checkDatasetIsExist($prefix, $dataSetName) && isset(self::$CACHE['DATA'][$prefix][$dataSetName][$getKey]));
    }

    /**
     * return time when dataSet is expired
     * @param string $prefix
     * @param string $dataSetName
     * @return int
     */
    public static function expiredAt(string $prefix, string $dataSetName): int
    {
        return (self::checkDatasetIsExist($prefix, $dataSetName)) ? self::$CACHE['TTL'][$prefix][$dataSetName] : 0;
    }

    /**
     * @param string $prefix
     * @param string $dataSetName
     * @return bool
     */
    private static function checkDatasetIsExist(string $prefix, string $dataSetName): bool
    {
        if (!isset(self::$CACHE['TTL'][$prefix][$dataSetName]) || self::$CACHE['TTL'][$prefix][$dataSetName] < time() || !isset(self::$CACHE['DATA'][$prefix][$dataSetName])) {
            self::deleteDataset($prefix, $dataSetName);
            return false;
        }
        return true;
    }


    /**
     * @param string $prefix
     * @param string $dataSetName
     * @return void
     */
    private static function deleteDataset(string $prefix, string $dataSetName): void
    {
        unset(self::$CACHE['TTL'][$prefix][$dataSetName], self::$CACHE['DATA'][$prefix][$dataSetName]);
    }

    /**
     * Cache cleaner. Removes all expired values
     * @return void
     */
    private static function clearCache(): void
    {
        foreach (self::$CACHE['TTL'] as $prefix => $dataSets) {
            foreach ($dataSets as $dataSetName => $ttl) {
                if ($ttl < time()) {
                    self::deleteDataset($prefix, $dataSetName);
                }
            }
        }
    }
}
