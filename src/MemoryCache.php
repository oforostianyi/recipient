<?php
/**
 * Class Local Cache
 * 2022-03-39 ZV
 * позволяет хранить данные в локальном кеше
 */

namespace Oforostianyi\Recipient;

class MemoryCache implements CacheInterface
{
    private static array $CACHE = ['DATA' => [], 'TTL' => []];

    /**
     * Записывает данные в кеш
     * @param string $prefix
     * @param string $dataSetName
     * @param array $dataToStore
     * @param int $ttl
     * @return bool
     */
    public static function set(string $prefix, string $dataSetName = 'uncnown', array $dataToStore = [], int $ttl = null)
    {
        # бессрочно ничего не храним
        if ($ttl === 0) return false;

        # имя банка $dataSetName не должен быть пустым
        if (empty($dataSetName)) return false;

        # $dataToStore - входящие данные должны быть не пустым массивом
        if (empty($dataToStore) || false === is_array($dataToStore)) return false;

        # пройдемся по всем ключам,и если их значения массив или объект - закодируем его в json.
        foreach ($dataToStore as $key => $value) {
            # вставим значение в локальный хеш, потом запишем в Redis
            self::$CACHE['DATA'][$prefix][$dataSetName][$key] = $value;
        }
        # проверяем, задан ли ttl и в каком он формате
        if (is_numeric($ttl)) {
            # если ttl задан в виде числа проверим, а существует ли такое значение
            if (isset(self::$CACHE['TTL'][$prefix][$dataSetName])) {
                # и если значение существует и больше либо равно текущему времени, то не обновляем
                if (self::$CACHE['TTL'][$prefix][$dataSetName] >= time()) {
                    return true;
                }
            }
            # устанавливаем новое значение ttl
            self::$CACHE['TTL'][$prefix][$dataSetName] = time() + $ttl;
        } else {
            # если ttl задан в виде строки, приведем ее ко времени
            self::$CACHE['TTL'][$prefix][$dataSetName] = strtotime($ttl);
        }
        return true;
    }

    /**
     * Читает данные из кеша
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return array|bool|mixed
     */
    public static function get(string $prefix, string $dataSetName = 'uncnown', string $getKey = '')
    {
        # имя банка $dataSetName не должен быть пустым
        if (empty($dataSetName)) return null;

        # если ключа TTL не существует, или ключ TTL истек, или не существует такого датасета вернем false
        if (!isset(self::$CACHE['TTL'][$prefix][$dataSetName]) || self::$CACHE['TTL'][$prefix][$dataSetName] < time() || !isset(self::$CACHE['DATA'][$prefix][$dataSetName])) {
            # на всякий случай обнулим данные
            unset(self::$CACHE['DATA'][$prefix][$dataSetName]);
            return null;
        }

        # если в локальном кеше присутствует искомое значение вернем его
        if ($getKey != '' && !empty(self::$CACHE['DATA'][$prefix][$dataSetName][$getKey])) {
            return self::$CACHE['DATA'][$prefix][$dataSetName][$getKey];
        }

        # если ключ не задан, а есть данные в локальном кеше, вернем весь набор
        if ($getKey == '' && !empty(self::$CACHE['DATA'][$prefix][$dataSetName])) {
            return self::$CACHE['DATA'][$prefix][$dataSetName];
        }
        # данных нет в кеше
        return null;
    }

    /**
     * Позволяет проверить наличие ключа
     * @param string $prefix
     * @param string $dataSetName
     * @param string $getKey
     * @return bool
     */
    public static function checkKey(string $prefix, string $dataSetName = 'uncnown', string $getKey = '')
    {
        # имя банка $dataSetName не должен быть пустым
        if (empty($dataSetName)) return false;

        # если ключа TTL не существует, или ключ TTL истек, или не существует такого датасета вернем false
        if (!isset(self::$CACHE['TTL'][$prefix][$dataSetName]) || self::$CACHE['TTL'][$prefix][$dataSetName] < time() || !isset(self::$CACHE['DATA'][$prefix][$dataSetName])) return false;

        return ($getKey != '' && isset(self::$CACHE['DATA'][$prefix][$dataSetName][$getKey])) ? true : false;
    }

    /**
     * Возвращает Time, до которого живет объект
     * @param string $prefix
     * @param string $dataSetName
     * @return int|mixed
     */
    public static function ttl(string $prefix, $dataSetName = 'uncnown')
    {
        return (isset(self::$CACHE['TTL'][$prefix][$dataSetName])) ? self::$CACHE['TTL'][$prefix][$dataSetName] : 0;
    }

    /**
     * Чистилка для кеша. Удаляет все старые значения
     * @return bool
     */
    private static function clearCache()
    {
        foreach (self::$CACHE['TTL'] as $prefix => $dataSets) {
            foreach ($dataSets as $dataSetName => $ttl) {
                if ($ttl < time()) {
                    unset(self::$CACHE['TTL'][$prefix][$dataSetName], self::$CACHE['DATA'][$prefix][$dataSetName]);
                }
            }
        }
        return true;
    }

}