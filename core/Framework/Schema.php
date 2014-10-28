<?php

/*******************************************************
 * core_Framework_Schema.php
 * 
 * @package game-f
 * @date    2014-10-28
 * @version v1.0.0
 *******************************************************/

class core_Framework_Schema
{
    // 整数类型
    const NUM = 1;
    // 字符串类型
    const STR = 2;
    
    /*
     * schema定义的数据结构及类型,key为数据结构字段名，value为数据类型
     * 
     * @var array
     */
    public $fields = array(
        'key1'     => self::NUM,
        'key2'     => self::STR,
        'key3'     => 'Embedded',
        '_default' => self::NUM,
    );

    // dbkey,取数据库配置字段
    public $db;

    /**
     * 获取key的类型
     * 
     * @param string $key
     * @return mix
     */
    public function type($key)
    {
        if (is_null($key) || $key == '') {
            return null;
        }
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        } elseif(isset($this->fields['_default'])) {
            return $this->fields['_default'];
        }                                          
        return null;
    }
}
