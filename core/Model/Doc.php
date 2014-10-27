<?php

/*******************************************************
 * 根据业务进行拆分的model底层类, 主要作用是使操作model的时候像是在操作数组一样，并自动记录业务逻辑执行过程中
 * 对数据库所做的修改操作，最后一次性将修改提交到数据库
 *
 * 一个collection 一个基层Model
 *      根据业务拆分的功能Model继承自Embedded，嵌入进该Model中
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

abstract class core_Model_Doc extends core_Model_Abstract
{
    public  $dbparam = array ();// 数据库参数
    
    // 纪录在业务逻辑执行过程中所有需要时 $set ，$inc , $unset 到数据库中的字段
    public  $up_arr    = array();
    public  $inc_arr   = array();
    public  $unset_arr = array();

    public  $_suspendDbWrite = false;       // 暂停数据库写入

    private $last_query_fields = array();   // 上次查询数据库的字段

    private $_last_db_change = array();     // 上次数据更改
    private $_origin_data_before = array(); // 修改前的数据情况

    /**
     * 构造函数
     * 
     * @param string $schema
     * @param modle $root
     * @return void
     */
    public function __construct($schema, $root = null)
    {
        parent::__construct($schema, $root);
        $this->dbparam['dbkey']      = $this->schema->db;
        $this->dbparam['collection'] = $this->schema->coll;
    }

    /**
     * 获取填充数据
     * 主要作用是将从数据库中取出来的数据填充道model的容器里面，并在此过程中构建模型
     * @param array $fields 要从数据库中取出来的值
     * @index mix   $index 使用的查找条件，默认使用_id
     * @param bool  $first 是否为第一次读取数据
     * @return array
     */
    public function getFields($fields, $index = null, $first = true)
    {
        // 一个收集fields的接口，获取所必须的字段信息
        if (empty($this->last_query_fields)) {
            // 第一次进行查找的时候才会触发
            $this->_whenFirstGetFields($fields);
        }
        //
        if ($index == null){
            $index = array("_id" => $this->_id);
        }
        $query_fields = array2path('', $fields);
        $query        = array_diff($query_fields, $this->last_query_fields);
        if (count($query) == 0){
            return;
        }
        $this->last_query_fields = array_unique(array_merge($query_fields, 
                $this->last_query_fields));
        // 如果尚未初始化，去数据库查询
        $data = $this->getdb()->findOne($index, $query);
        if ($first){
            $this->initWithData($data);
        }else{
            $this->fillData($data);
        }
        return $data;
    }

    /**
     * 书用数据初始化model
     * 
     * @param array $data
     * @param boolean $forceNew
     * @return mix
     */
    public function initWithData(&$data, $forceNew = false) 
    {
        if (!$forceNew && empty($data)) {
            // 如果一个docmodel的数据为空，则认为该model为新
            $forceNew = true;
        }
        return parent::initWithData($data, $forceNew);
    }

    /**
     * 检查某个字段是否已经读取
     * 
     * @param string $k example: vip.lvl
     * @return bool
     */
    public function isFieldRead($k) 
    {
        if (count($this->last_query_fields) == 0){
            // 没指定fields，则认为是全部已经获取
            return true;
        }
        // 检查是否为 last_query_fields 中存的是 $k.v1.v2 的格式
        foreach( $this->last_query_fields as $key) {
            if ($k == substr($key, 0, strlen($k))) {
                return true;
            }
        }
        // 检查 $k 是否为 last_query_fields 中的某个值 $key.v1.v2 的格式
        if ($this->checkFieldRead($k . '.')) {
            return true;
        }
        // throw exception
        throw new core_Exception_LogicAlertException("key $k not read from database, cannot use now!", 
            core_Config_ErrLogicCode::ERR_PROPERTY_NOT_EXISTS);
    }

    /**
     * 检查字段被读的情况
     * 
     * @param type $k
     * @return boolean
     */
    private function checkFieldRead($k)
    {
        if ($k == null || $k == '.' || $k == ''){
            return false;
        }
        if (in_array($k, $this->last_query_fields)) {
            return true;
        }
        $idx = strrpos($k, '.', -2);
        if ($idx === false) {
            return false;
        }
        return $this->checkFieldRead(substr($k, 0, $idx + 1));
    }

    /**
     * inc
     * 当对数据库中的某个字段做自增操作时调用此方法 
     * @access  所有更新数据的自增操作都必须调用此方法
     * 
     * @param string $k
     * @param mix    $v
     * @return void
     */
	public function dbinc($k, $v = 1) 
    {
        // 如果之前存在别的修改，去除该修改
        if (isset($this->unset_arr[$k])) {
            unset($this->unset_arr[$k]);
        }
        if (isset($this->up_arr[$k])) {
            // 如果之前有set修改，则之后所有的inc都转变为set
            $this->up_arr[$k] += $v;
        }else{
            $this->inc_arr[$k] += $v;
        }
	}

    /**
     * set
     * 当对数据库中的某个字段做赋值操作时调用此方法 
     * @access  所有更新数据的赋值操作都必须调用此方法
     * 
     * @param string $k
     * @param mix    $v
     * @return void
     */
    public function dbset($k, $v)
    {
        // 如果之前存在别的修改，去除该修改
        if (isset($this->inc_arr[$k])) {
            unset($this->inc_arr[$k]);
        }
        if (isset($this->unset_arr[$k])) {
            unset($this->unset_arr[$k]);
        }
        $this->up_arr[$k] = $v;
    }

	 /**
	  * unset
	  * 当对数据库中的某个字段做删除操作时调用此方法 
	  * @access  所有更新数据的删除操作都必须调用此方法
      *
      * @param string $k
      * @param bool $check 是否检查与$k相关的更改
      * @return void
	  */
    public function dbunset($k, $check = false)
    {
        // 如果之前存在别的修改，去除该修改
        if (isset($this->inc_arr[$k])) {
            unset($this->inc_arr[$k]);
        }
        if (isset($this->up_arr[$k])) {
            unset($this->up_arr[$k]);
        }
        if ($check) {
            $this->dbunsetCheck($k);
        }
        $this->unset_arr[$k] = 1;
    }

    /**
     * 返回mongo的key字段
     * 
     * @param string $k
     * @return string
     */
    public function dbpath($k) 
    {
        return $k;
    }

    /**
     * 检查数据的修改
     * 
     * @return void
     */
    private function dbunsetCheck($key) 
    {
        foreach ($this->inc_arr as $k=>$v) {
            if (substr($k, 0, strlen($key)) == $key) {
                unset($this->inc_arr[$k]);
            }
        }
        foreach ($this->up_arr as $k=>$v) {
            if (substr($k, 0, strlen($key)) == $key) {
                unset($this->up_arr[$k]);
            }
        }
        foreach ($this->unset_arr as $k=>$v) {
            if (substr($k, 0, strlen($key)) == $key) {
                unset($this->unset_arr[$k]);
            }
        }
    }


	/**
	 * _genCommition
	 * 生成此次业务逻辑操作，所要更新到数据库中的所有数据列表和操作类型
	 *   
	 * @param  $commition 须是引用结构
	 * @example  生成的数据格式如下
	 * Array
	 * (
     *      [$set] => Array
     *      (
     *          [ls] => s1
     *          [_at] => 1377070109
     *          [mac_address] => 123434455
     *          [secs.s1] => 1377070109
     *          [extra.q] => 1
     *          [extra.vv] => Array
     *         (
     *              [i] => 3
     *              [t] => 4
     *          )
     *          [macs.123434455] => 1377070109
     *      )
     * )
     * 
     * @return array
	 */
	private function _genCommition(){
        $commition = array();
        if ($this->is_new){
            $this->genResult($result);
            $commition = $result['d'];
            return;
        }
		foreach($this->up_arr as  $key=>$v){
			$commition['$set'][$key] = $v;
		}

		foreach($this->inc_arr as  $key=>$v){
			$commition['$inc'][$key] = $v;
		}

		foreach($this->unset_arr as $key=>$v){
			$commition['$unset'][$key] = 1;
		}
        foreach($commition as $key=>$value){
            if (count($value) == 0){
                unset($commition[$key]);
            }
        }
        return $commition;
	}

    /**
     * 生成数据变更情况
     * 
     * @param array $result
     * @return void
     */
    public function genResult(&$result)
    {
        $data = &$result['d'];
        foreach($this->inc_arr as $key=>$v){
            $keys = explode(".",$key);
            $d = &$data;
            $m = &$this;
            foreach($keys as $k){
                $d = &$d[$k];
                $m = &$m[$k];
            }
            $d = $m;
        }
        foreach($this->up_arr as $key => $v){
            $keys = explode(".",$key);
            $d = &$data;
            foreach($keys as $k){
                $d = &$d[$k];
            }
            $d = $v;
        }
		foreach($this->unset_arr as $key => $value){
            $result['unset'][$key] = 1;
		}
        $data['_id'] = $this->_id;
    }
    
    /**
     * 将所做的修改提交到数据库
     *
     * @return
     *      data
     *          lvl
     *          exp
     *          vip
     *              lvl
     */
    public function commit() 
    {
        $this->_beforeDocCommit(); // 开始数据更新
        $com = $this->_genCommition();
        if (empty($com)){
            return array('s'=>"OK");
        }

        if ($this->_suspendDbWrite == false) {
            
            try {
                // 默认在同一个Model中不会改变collection 
                if ($this->is_new) {
                    if (isset($this->_id)) {
                        $com['_id'] = $this->_id;
                    }
                    if (empty($com['_id'])) {
                        unset($com['_id']);
                    }
                    $ret = $this->getdb()->insert($com, array('safe' => true));
                }else{
                    $ret = $this->getdb()->update(array("_id" => $this->_id), 
                            $com, array('upsert' => 1,'safe' => true));
                }
                if($ret['ok']==1){
                    // 数据库更新ok
                }else{
                    throw new core_Exception_LogicAlertException(
                        json_encode($ret), core_Config_ErrLogicCode::ERR_MONGO_ERROR);
                }
            } catch (MongoException $ex) {
                throw new core_Exception_LogicAlertException(
                    $ex->getMessage() . "\n" . json_encode($com) . "\n" . json_encode($ret),
                    core_Config_ErrLogicCode::ERR_MONGO_ERROR);
            }
        }

        if (!$this->is_new) {
            $this->genResult($re);
        } else {
            $re['d'] = $com;
        }
        $re['s'] = "OK";
        // 需要清空之前的up_arr的内容
        if ($this->_suspendDbWrite == false) {
            $this->up_arr    = array();
            $this->inc_arr   = array();
            $this->unset_arr = array();
        }

        $this->_origin_data_before = array(); // 已经塞入数据库，清空变化前的数据
        $this->_last_db_change = $re['d']; // 保存上次发生的数据变更情况
        $this->_afterDocCommit(); // 数据变更结束

        return $re;
    }

    /**
     * 清空该model并从数据库中抹去
     * 
     * @return void
     */
    public function destroy()
    {
        $this->getdb()->remove(array("_id"=>$this->_id));
        $this->cleanModel();
    }

    /**
     * 建立mongodb的索引
     *
     * @param mix $keys 索引字段
     * @param array $options 是否为降序
     * @return void
     */
    public function ensureIndex($keys, $options = array()) 
    {
        $this->getdb()->ensureIndex($keys, $options);
    }

    /**
     * 清空数组数据
     * 
     * @return void
     */
    public function cleanData()
    {
        $this->up_arr    = array();
        $this->inc_arr   = array();
        $this->unset_arr = array();
    }
    
    /**
     * 设置数据库的参数
     *
     * @param str $k
     * @param mix $v
     * @return void
     */
    public function setDbParam($k, $v)
    {
        $this->dbparam[$k] = $v;
    }

    /**
     * 设置是否暂停数据库提交更改
     *
     * @param bool $bool
     * @return void
     */
    public function suspendDbWrite($bool) 
    {
        $this->_suspendDbWrite = (bool)$bool;
    }

    /**
     * 数据提交之前可执行的函数
     * 
     * @return mix
     */
    protected function _beforeDocCommit() 
    {
    }

    /**
     * 数据提交之后会执行的函数
     * 
     * @return mix
     */
    protected function _afterDocCommit() 
    {
    }

    /**
     * 表面该类的节点类型，不可继承重写该函数
     * 
     * @return string
     */
    final public function modelType()
    {
        return "core_Model_Doc";
    }

    /**
     * 调用getFields 之前所必须的
     * 且只在第一次调用getFields的时候执行
     * 
     * @return void
     */
    protected function _whenFirstGetFields(&$fields) 
    {
        return $fields;
    }

    /**
     * 获取上次变更字段
     * 
     * @return array
     */
    public function getLastDbChange()
    {
        return $this->_last_db_change;
    }

    /**
     * 获取历史数据
     * 
     * @return array
     */
    public function getOriginChangedData()
    {
        return $this->_origin_data_before;
    }

    /**
     * 在数据发生变化前，记录下原始的数据
     *
     * @param str $k
     * @return void
     */
    public function updateOriginDataBeforeChange($k)
    {
        if (arrayvalbypath($this->_origin_data_before, $k) == null) {
            $d = arrayvalbypath($this->data, $k);
            if ($d == null) {
                $d = self::DB_NULL_MARK;
            }
            setarrayvalbypath($this->_origin_data_before, $k, $d);
        }
    }
}
