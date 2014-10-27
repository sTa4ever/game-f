<?php

/*******************************************************
 * core_Facoty_Model.php
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

class core_Factory_Model
{
    // 进程中缓存的schema的实例
    private static $_schemas = array(); 
    
    /**
     * 获取一个model
     * 
     * @param string name model的名字
     * @param model  root 上级model
     *
     * @return model
     */
    public static function getModel($name, $root = null)
    {
        $name       = ucfirst($name);
        $model_name = "model_" . $name;
        if (!class_exists($model_name)) {
            $model_name = "core_Model_Embedded";
        }
        return new $model_name(self::getSchema($name), $root);     
    }

    /**
     * 获取docModel的实例，并已经从数据库中获得所需要的数据
     * 
     * @param string $name   Model名字
     * @param mix    $index  索引 
     * @param array  $fields 数据字段
     * @return model
     */
    public static function getDocModel($name, $index, $fields = array())
    {
        // DocModel只能作为最外层的root，不能内嵌
        $mod = self::getModel($name); 
        $mod->setIndex($index);
        if ($mod->modelType() == "core_Model_Doc") {
            if (count($fields) == 0){
                foreach ($mod->schema->fields as $key => $v){
                    $fields[] = $key;
                }
            }
            $mod->getFields($fields);
            return $mod;
        }else{
            throw new core_Exception_LogicAlertException(
                "getDocModel name $name need to be docmodel", 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }
    }

    /**
     * 获取DocModel的对象，读数据库内容来自动初始化
     * 
     * @param string $name
     * @param mix    $index
     * @return model
     */
    public static function getEntireDocModel($name, $index)
    {
        // DocModel只能作为最外层的root，不能内嵌
        $mod = self::getModel($name); 
        $mod->setIndex($index);
        if ($mod->modelType() == "core_Model_Doc"){
            $mod->getFields(array());
            return $mod;
        }else{
            throw new core_Exception_LogicAlertException(
                "getDocModel name $name need to be docmodel", 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }
    }

    /**
     * 通过data来初始化一个model
     * 
     * @param string $name model名字
     * @param model  $root 上级model
     * @param string $prikey id
     * @param mix    $data  数据
     * @param bool   $new   是否新model
     * @return model
     */
    public static function getModelWithData($name, $root, $prikey, &$data, $new = false)
    {
        $mod = self::getModel($name, $root);
        $mod->setIndex(array("_id" => $prikey));
        $mod->initWithData($data, $new);
        return $mod;
    }

    /**
     * 根据index中的ids数组来获取对应的DocModel
     * 
     * @param string name 
     * @param array index
     *          ids
     *          sec
     * @param array $fields
     * @return modles
     */
    public static function getDocModelsByIds($name, $index, $fields = array())
    {
        if (!isset($index['ids'])){
            throw new core_Exception_LogicAlertException("ids is necessary when getDocModelsByIds", 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }
        $query_fields = array2path('', $fields);
        $models = array();
        $schema = self::getSchema($name);
        $sec = $index['sec'];
        $dbparam = array(
            'dbkey'      => $schema->db, 
            'sec'        => $sec, 
            'collection' => $schema->coll
        );
        $db = core_Factory_Db::getMongo($dbparam);
        $query = array(
            '_id'=>array(
                '$in' => $index['ids'],
            )
        );
        $datas = iterator_to_array($db->find($query, $query_fields));
        foreach($datas as $k => $v){
            $model = self::getModel($name);
            $model->initWithData($datas[$k]);

            $models[] = $model;
		}
        return $models;
    }

    /**
     * 根据条件获取model
     * 
     * @param string $name
     * @param mix    $index
     * @param array  $fields
     * @return model
     */
    public static function getDocModelWithCond($name, $index, $fields = array())
    {

        $query_fields = array2path('',$fields);
        $schema       = self::getSchema($name);
        $sec          = $index['sec'];
        unset($index['sec']);

        $dbparam = array('dbkey'=>$schema->db, 'sec'=>$sec, 'collection'=>$schema->coll);
        $db      = core_Factory_Db::getMongo($dbparam);
        // read from db  todo 不想在这个地方去操作数据库
        $data   = $db->findOne($index, $query_fields);

        $model = self::getModel($name);
        $model->initWithData($data);

        return $model;
    }

    /**
     * 获取模型列表
     *
     * @param array $options 选项
     *                  sort
     *                  limit
     *                  skip
     */
    public static function getDocModelsWithCond($name, $index, $fields = array(), $options = array()){

        $query_fields = array2path('',$fields);
        $models       = array();
        $schema       = self::getSchema($name);
        $sec          = $index['sec'];

        unset($index['sec']);

        $dbparam = array('dbkey'=>$schema->db, 'sec'=>$sec, 'collection'=>$schema->coll);
        $db      = core_Factory_Db::getMongo($dbparam);
        // read from db  todo 不想在这个地方去操作数据库
        //

        $iterator = $db->find($index, $query_fields);
        if (isset($options['sort'])) {
            // 排序
            $iterator = $iterator->sort($options['sort']);
        }
        if (isset($options['skip'])) {
            // 跳过
            $iterator = $iterator->skip($options['skip']);
        }
        if (isset($options['limit'])) {
            // 限制
            $iterator = $iterator->limit($options['limit']);
        }

        $datas   = iterator_to_array($iterator);

        foreach($datas as  $k=>$v){
            $model = self::getModel($name);
            $model->initWithData($datas[$k]);

            $models[] = $model;
		}

        return $models;
    }

    /**
     * 获取符合条件的结果条数
     */
    public static function getDocModelCount($name, $index) 
    {
        $schema       = self::getSchema($name);
        $sec          = $index['sec'];

        unset($index['sec']);

        $dbparam = array('dbkey'=>$schema->db, 'sec'=>$sec, 'collection'=>$schema->coll);
        $db      = core_Factory_Db::getMongo($dbparam);

        return $db->find($index)->count();
    }

    public static function getSchema($name){
        if (substr($name, 0, 7)  != 'schema_'){
            $name = ucfirst($name);
            $name = "schema_" . $name;
        }
        if (!class_exists($name)){
            throw new core_Exception_LogicAlertException("schema $name not exists, please define it first",core_ErrorCode::schema_not_found);
        }
        if (!isset(self::$_schemas[$name])){
            self::$_schemas[$name] = new $name();
        }
        return self::$_schemas[$name];
    }

    public static function getKV($name, $sec = null){
        $param = array(
            'dbkey'=>$name,
            'sec'=>$sec,
        );
        $redis = core_Factory_Db::getRedis($param);
        return new core_Redis_KV($redis);
    }
}
