<?php


namespace app\common\srevice;

/**业务基类
 * 2020.11.26 14：22     王瑶
 * Class Service
 * @package app\common\srevice
 */

class Service
{
    private static $_instances = [];

    /**
     * @return static|mixed
     */
    public static function Factory()
    {
        $className = get_called_class();
        if (isset(self::$_instances[$className])) {
            return self::$_instances[$className];
        }
        self::$_instances[$className] = new static();
        return self::$_instances[$className];
    }
}