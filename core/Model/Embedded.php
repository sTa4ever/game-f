<?php

/*******************************************************
 * 嵌入到Model中，为其子Model,自身不会去更新数据库，调用root的去更新
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

class core_Model_Embedded extends core_Model_Abstract
{
    public $_key; // 该model对应的在上层结构中的key

    /**
     * 提交数据
     * 
     * @param bool $force 是否强制提交
     * @return mix
     */
    public function commit($force = false)
    {
        if ($force){
            // 正常情况下不在Embedded中做提交操作
            return;
        }
        // 调用父节点的去做
        return $this->root->commit();
    }

    /**
     * 建立索引
     * 
     * @param mix $index
     * @return void
     */
    public function setIndex($index = null)
    {
        // setindex中去 给_id赋值
        parent::setIndex($index);
        $this->_key = $this->_id;
    }

    /**
     * 设置该model对象为全新
     * 
     * @return void
     */
    public function setIsNew() 
    {
        $this->cleanModel();
        $this->is_new = true;
        $this->root->dbunset($this->_key, true);
    }

    /**
     * 数据inc
     * 
     * @param string $k
     * @param mix $v
     * @return void
     */
    public function dbinc($k, $v)
    {
        if ($this->is_new){
            $this->root->dbset($this->_key,$this->_realData());
        }else{
            $k = $this->_key. ".$k";
            $this->root->dbinc($k,$v);
        }
    }
    
    /**
     * 数据set操作
     * 
     * @param string $k
     * @param mix $v
     * @return void
     */
    public function dbset($k, $v)
    {
        if ($this->is_new){
            $this->root->dbset($this->_key, $this->_realData());
        }else{
            $k = $this->_key. ".$k";
            $this->root->dbset($k, $v);
        }
    }

    /**
     * 数据unset操作
     * 
     * @param string $k
     * @param bool $check
     * @return void
     */
    public function dbunset($k, $check = false)
    {
        if ($this->is_new) {
            $realData = $this->_realData();
            if (!empty($realData)) {
                $this->root->dbset($this->_key,$realData);
            } else {
                $this->root->dbunset($this->_key);
            }
        } else {
            $k = $this->_key. ".$k";
            $this->root->dbunset($k, $check);
        }
    }

    /**
     * 更新原始数据
     * 
     * @param string $k
     * @return void
     */
    public function updateOriginDataBeforeChange($k)
    {
        $k = $this->_key . ".$k";
        $this->root->updateOriginDataBeforeChange($k);
    }

    /**
     * 获取真实数据
     * 
     * @return mix
     */
    private function _realData() 
    {
        $data = array();
        foreach($this->data as $k=>$v) {
            if (is_array($this->data[$k]) && count($this->data[$k]) == 0) {
                continue;
            }
            $data[$k] = $v;
        }
        return $data;
    }

    /**
     * 获取数据库存储字段key
     * 
     * @param string $k
     * @return string
     */
    public function dbpath($k)
    {
        return $this->root->dbpath($this->_key . '.' . $k);
    }

    /**
     * 是否已读
     * 
     * @param string $k
     * @return bool
     */
    public function isFieldRead($k) 
    {
        $k = $this->_key. ".$k";
        return $this->root->isFieldRead($k);
    }

    /**
     * 获取model类型
     * 
     * @return string
     */
    public function modelType()
    {
        return "core_Model_Embedded";
    }
}
