<?php

namespace Oforostianyi\Recipient;

interface CacheInterface
{
    /**
     * add data to cache
     * @param string $prefix
     * @param string $dataSetName
     * @param array $dataToStore
     * @param int $ttl
     * @return bool
     */
    public static function set(string $prefix, string $dataSetName, array $dataToStore = [], int $ttl = 5): bool;

    /**
     * read data from cache
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return array|bool|mixed
     */
    public static function get(string $prefix, string $dataSetName, string $getKey = '');

    /**
     * check if key exist
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return bool
     */
    public static function checkKey(string $prefix, string $dataSetName, string $getKey): bool;


    /**
     * return time when dataSet is expired
     * @param string $prefix
     * @param string $dataSetName
     * @return int
     */
    public static function expiredAt(string $prefix, string $dataSetName);

}