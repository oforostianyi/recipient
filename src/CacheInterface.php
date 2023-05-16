<?php

namespace Oforostianyi\Recipient;

interface CacheInterface
{
    /**
     * записывает данные в кеш
     * @param string $prefix
     * @param string $dataSetName
     * @param array $dataToStore
     * @param int $ttl
     * @return bool
     */
    public static function set(string $prefix, string $dataSetName = 'uncnown', array $dataToStore = [], int $ttl = 5);

    /**
     * читает данные из кеша
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return array|bool|mixed
     */
    public static function get(string $prefix, string $dataSetName = 'uncnown', string $getKey = '');

    /**
     * позволяет проверить наличие ключа
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return bool
     */
    public static function checkKey(string $prefix, string $dataSetName = 'uncnown', string $getKey = '');


    /**
     * возвращает Time, до которого живет объект
     * @param string $prefix
     * @param string $dataSetName
     * @return int|mixed
     */
    public static function ttl(string $prefix, string $dataSetName = 'uncnown');

}