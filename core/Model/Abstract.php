<?php

/*******************************************************
 * core_Model_Abstract.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

abstract class core_Model_Abstract implements ArrayAccess, IteratorAggregate, Countable
{
    public $schema; // 视图，控制属性字段
	public $root;   // 根节点
	public $container = array (); // 存储子树对象
	public $is_new = true;        // 不存在于数据库中
    public $_id;
    public $data = array(); // 保存数据
    
    /**
     * 构造函数
     * 
     * @param schema 视图实例
     * @return  void
     */
    public function __construct($schema, $root = null)
    {
        $this->schema = $schema;
        $this->root   = $root;
    }
    
    /**
     * 设置索引值，可能是多个，或者为id
     *
     * @params array index 
     *              _id
     */
    public function setIndex($index = null)
    {
        if ($index == null){
            return;
        }
        if (is_array($index)) {
            foreach($index as $key => $value) {
                $this->$key = $value;
            }
        }else {
            throw new core_Exception_LogicAlertException(
                'index must to be array when try to set index to a model', 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM
            );
        }
    }
    
    /**
     * 使用数据来初始化model
     *
     * @param arr $data
     * @param bol $forceNew 强制设置该model为is_new
     * @return void
     */
    public function initWithData(&$data, $forceNew = false)
    {
        $this->is_new = false;
        if ($forceNew) {
            $this->is_new = true;
        }elseif (!is_array($data)){
            $this->is_new = true;
        }
        $this->fillData($data);
    }


    /**
     * 是用data来初始化该model
     * 
     * @params array data
     * @return void
     */
    public function fillData(&$data){
        if (!is_array($data)){
            return false;
        }

        if ( !$this->_id && isset($data['_id']) ){
            $this->_id = $data['_id'];
        }
        if (empty($this->data)) {
            // data之前没有赋值
            $this->data = &$data;
            return true;
        }
        // 如果之前已经有数据，根据新的数据，进行merge覆盖
        foreach( $data as $k => $v ) {
            $this->_setdata($k, $v, false);
        }
    }

    /**
     * 是用data来重置该model
     * 
     * @params array data
     * @return void
     */
    public function resetWithData($data) 
    {
        if (!is_array($data)){
            return;
        }
        foreach($data as  $k => $v){
            if ($type = $this->schema->type($k)){
                if(is_numeric($type)){
                    $this->$k = $v;
                }else{
                    $this->$k->resetWithData($v);
                }
            }
		}
    }

    /**
     * 获取fields所指的数据，数组结构返回
     * 
     * @param $fields 要获取的数据，fields == 1 为全部获取
     * @return array
     */
    public function getDataByFields($fields = 1)
    {
        $data = array();
        if ($fields  == 1) {
            return $this->toArray();
        }
        foreach ($fields as $fkey => $fvalue){
            if (is_array($fvalue)){
                $d = $this->fieldGet($fkey);
                if ($d instanceof core_Model_Abstract){
                    $d = $d->getDataByFields($fvalue);
                }
                $data[$fkey] = $d;
            } else {
                if (isset($this->data[$fvalue])) {
                    $data[$fvalue] = $this->data[$fvalue];
                }
            }
        }
        return $data;
    }

    /**
     * 把当前该Model下所含数据转成数组形式
     * getDataByFields的别名
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * 清空整个model数据
     * 
     * @return void
     */
    public function cleanModel()
    {
        foreach($this->container as $k=>$v){
            if ($v instanceof core_Model_Abstract){
                $v->cleanModel();
            }
            unset($this->container[$k]);
        }
        $this->data = array();
    }

    /**
	 * 获取一个字段值，如果该字段值为空，并且类型为对象的话，则new一个新的对象返回
	 * @param string $k key
	 * @return  对应的值
     */
    public function fieldGet($k)
    {
        $type = $this->_checkschema($k);
        if (isset($this->container[$k])) {
            // 已经赋值则直接返回
            return $this->container[$k];
        }
        if (isset($this->data[$k])) {
            // 存在，但没有对应对象，new出对象再返回
            if (is_numeric($type)) {
                $this->container[$k] = $this->data[$k];
            } else {
                $this->container[$k] = core_Factory_Model::getModelWithData($type, $this, $k, $this->data[$k]);
            }
            return $this->container[$k];
        }
        //$this->isFieldRead($k); // 读取的时候暂时不检查，只在修改该值的时候进行检查
        // 如果已试图读取，但数据库中不存在该数据
        if (!is_numeric($type)) {
            $this->data[$k] = array();
            $this->container[$k] = core_Factory_Model::getModelWithData($type, $this, $k, $this->data[$k], true);
            return $this->container[$k];
        }
        // 基本数据类型直接返回null
        return null;
    }

    /**
	 * 当对此对象上的一个属性做incr操作时，回调用此方法，同时将操作记录到数据库$inc_arr中
     * 
	 * @param string $k key
     * @param int $v value
	 * @return  增加之后的值
     */
    public function fieldIncr($k, $v)
    {
        $type = $this->_checkschema($k);
        $this->isFieldRead($k);
        $this->updateOriginDataBeforeChange($k);
        if ($type == core_Schema::NUM){
            // 只有数字型的才能进行incr
            $v2 = $this->$k + $v;
            $v3 = $v2;
            if($this->_setdata($k, $v2)) {
                $this->dbinc($k,$v);
                return $v3;
            }
        }
        return false;
    }

	/**
	 * 当对此对象上的一个属性做unset操作时，回调用此方法，同时将操作记录到数据库$unset_arr中
     * 
	 * @param string $k 
	 * @return void
	 */
	public function fieldRemove($k) 
    {
        $this->isFieldRead($k);
        if ($this->_cleandata($k)) {
            $type = $this->_checkschema($k);
            // 更改记录到数据库
            if (is_numeric($type)){
                $this->dbunset($k);
            }else{
                $this->dbunset($k, true);
            }
        }
	}

    /**
     * 修改该对象上的某属性值，并将操作记录在up_arr中
     * @param string $k
     * @param mix $v
     * @return void
     */
    public function fieldSet($k, $v)
    {
        if (is_object($v) || is_array($v)){
            throw new core_Exception_LogicAlertException(
                "key [$k] 's value cannot set to be an php object", 
                core_ErrorCode::logic_error);
        }
        $this->isFieldRead($k);
        $this->updateOriginDataBeforeChange($k);
        if ($this->_setdata($k, $v)) {
            $this->dbset($k, $v);
        }
    }

    /**
     * 变量赋值，只是赋值操作
     *
     * @return bool 操作是否成功
     */
    private function _setdata($k, &$v, $strict = true) 
    {
        $type = $this->_checkschema($k, $strict);
        if ($type == core_Schema::NUM) {
            // 数值
            $v = (int)$v;
        } elseif ($type == core_Schema::STR){
            // 字符串
            $v = (string)$v;
        }

        $this->data[$k] = $v;
        if (isset($this->container[$k])) {
            if (is_numeric($type)) {
                $this->container[$k] = $v;
            } else {
                $this->container[$k]->fillData($v);
            }
        }
        return true;
    }

    /**
     * 清楚数据
     * 
     * @param string $k
     * @return boolean
     */
    private function _cleandata($k) 
    {
        if (isset($this->data[$k])) {
            unset($this->data[$k]);
            if (isset($this->container[$k])) {
                unset($this->container[$k]);
            }
            return true;
        }
        return false;
    }

    /**
     * 检查schema中是否定义了$k
     *
     * @return 返回 $k 对应的schema
     */
    private function _checkschema($k, $strict = true) 
    {
        if ($type = $this->schema->type($k)) {
            return $type;
        }
        if ($strict) {
            $k = $this->dbpath($k);
            throw new core_Exception_LogicAlertException(
                "key [$k] not exist in model,please define it in schema first", 
                core_Config_ErrLogicCode::ERR_PROPERTY_NOT_EXISTS);
        }
    }

    /**
     * 设置ID
     * 
     * @param mix $id
     * @retjrn void
     */
    public function setId($id){
        $this->_id = $id;
    }

    /**
     * 获取ID
     * 
     * @return mix
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * 获取数据库连接，这个是Mongodb的连接
     * 
     * @return mongo
     */
    public function getdb()
    {
        return core_Factory_Db::getMongo($this->dbparam);
    }

    /**
     * 获取model 类型
     * 
     * @return string
     */
    public function modelType()
    {
        return "core_Model_Abstract";
    }

    /**
     * 判断是否存在
     * 
     * @return bool
     */
    public function exists()
    {
        return !$this->is_new;
    }

    /**
     * 获取根路径
     * 
     * @return model
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    /**************************************************
     * ArrayAccess
     * 
    ***************************************************/
    public function offsetSet($k, $v)
    {
        $this->fieldSet($k, $v);
    }

    public function offsetGet($k)
    {
        return $this->fieldGet($k);
    }

    public function offsetExists($k)
    {
        return isset($this->data[$k]);
    }

    public function offsetUnset($k)
    {
        $this->fieldRemove($k);
    }

	public function __get($key)
    {
        return $this->fieldGet($key);
	}

	public function __set($key, $value)
    {
        $this->fieldSet($key,$value);
	}

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->fieldRemove($key);
    }


    // countable
    public function count() 
    {
        return count($this->container);
    }

    // IteratorAggregate
    public function getIterator() 
    {
        return new ArrayIterator($this->container);
    }
	
	/**
	 * 获取本节点容器的所有字段
	 * 
	 * @return array
	 */
	public function getKeys() 
    {
		return array_keys($this->data);
	}
	
	/**
	 * 获取本节点容器的所有数据
	 * 
	 * @return array
	 */
	public function getValues() 
    {
		return array_values($this->container);
	}

	/**
	 * __call
	 * 当调用一个当前对象不存在的方法时，会自动通过链式模型搜索，是否组合中有这样一个方法
     * 
	 * @param $name 方法名， $args 所传参数 
	 */

	public function __call($name, $args)
    {
		if ($this->root) {
			$this->root->$name($args);
		} else {
			throw new core_Exception_LogicAlertException("method $name not exist", 
                core_Config_ErrLogicCode::ERR_METHOD_NOT_EXISTS);
        }
	}
}
