<?php



class BaseModel {

    private $_called_class = '';
    private $_table_name = '';
    private $_attributes = array();
    private $_unique_keys = array();
    private $_dirty_keys = array();
    private $_values = array();
    private $_properties = array();
    protected $_dbobj = null;

    public function __construct($dbobj, $id=0) {

        $this->_called_class = get_called_class();
        $this->_table_name = $this->getTableNameFromClassName();
        $this->_attributes = $this->_getClassAttributes();
        $this->_dbobj = $dbobj;
        
        if($id !== 0) {
            $this->findOne('id='.$id);
        } else {
            $this->_init_attributes();
        }
    }

    private function _init_attributes() {
        foreach($this->_attributes as $attribute) {
            $key = '_'.$attribute['name'];
            $type = $attribute['type'];
            if($type === 'int' || $type === 'tinyint' || $type === 'smallint' || $type === 'mediumint' ||
            $type === 'bigint' || $type === 'float' || $type === 'double' || $type === 'decimal') {
                $this->_values[$key] = 0;
            } else {
                $this->_values[$key] = '';
            }
        }
    }

    public function getCacheKey(){
        $entity = depluralize($this->_table_name);
        $dbname = $this->_dbobj->getDBName();
        return CacheKey::q("$dbname.$entity?id=" . $this->_values['_id']);
    }

    public function get($name){ // get a db field
        $name = "_" . $name;
        return $this->_values[$name];
    }

    public function set($name, $value){ // set a db field
        if($this->_properties != null && in_array($name, $this->_properties)){ // a db field
            $name = "_" . $name;
            if(isset($this->_values[$name]) && $this->_values[$name] != $value){ // change value
                $this->_dirty_keys[$name] = 1;
            }
            $this->_values[$name] = $value;            
            return true;
        }
        return false;
    }

    public function __set($name, $value){
        if($name[0] === '_'){ // maybe a db field: $this->_id=2
            if(!$this->set(substr($name, 1), $value)){ // not a db field
                $this->$name = $value;
            }
        } else {
            $this->$name = $value;
        }
    }

    public function __get($name){
        if($name[0] === '_'){ // maybe a db field: $id = $this->_id;
            $property_name = substr($name, 1);
            if($this->_properties != null && in_array($property_name, $this->_properties)){ // a db field
                return $this->_values[$name];
            }
        }
        return $this->$name;
    }

    public function data(){
        return $this->_toArray();
    }

    // condition:
    // "email='xxx@yahoo.com and password='asfda'"
    // if use "id=num", we basically can refresh this object
    public function findOne($condition) {
        $record = BaseMapper::findOne($condition, $this->_table_name, $this->_dbobj);

        if($record['id'] == 0) {
            $this->_init_attributes();
        } else {
            foreach($record as $key=>$value) {
                $key = '_'.$key;
                $this->_values[$key] = $value;
            }
        }
    }

    public static function findCachedOne($ck, $opt = array()) {
        if(!($ck instanceof CacheKey)){
            $ck = CacheKey::q($ck);
        }
        $ret = DAL::get($ck, $opt);
        if(empty($ret)) return NULL;
        $entity = $ck->getEntity();
        $clazz = to_camel_case($entity, true);
        if(method_exists($clazz, 'format')) {
            $ret = $clazz::format($ret, $ck);
        }
        $ret['row_id'] = $ck->cacheKey(); // for datatable
        return $ret;
    }

    // get & set functions
    public function __call($name, $arguments) {
        $op_data = from_camel_case($name);
        $items = explode('_', $op_data);
        $op = $items[0];
        if($op === 'get' || $op === 'set') {
            $data = substr($op_data, 3);
            if($op === 'get') {
                return $this->_values[$data];
            } else if($op === 'set') {
                if(is_string($arguments[0])) {
                    $arguments[0] = trim($arguments[0]);
                }
                if($this->_values[$data] != $arguments[0]){ // change value
                    $this->_values[$data] = $arguments[0];                    
                    $this->_dirty_keys[$data] = 1;
                }
            }
        }
    }

    public function save($auto_sync_list = True) {
        global $nocache_models;
        if(in_array($this->_table_name, $nocache_models)){
            $this->_internal_save(NULL, FALSE);
        } else {
            $this->_internal_save(NULL, $auto_sync_list);
        }
    }

    public function _internal_save($old_data = NULL, $auto_sync_list = True) {
        // ways to save models:
        // 1. $m->save()
        // 1. BaseModel::saveObjects()
        // 1. BaseMapper::saveAssociation()
        // 1. Raw SQL insert/update x
        $updated = ($old_data !== NULL);
        $id = $this->_id;
        $entity = depluralize($this->_table_name);
        $cachekey = CacheKey::q($this->_dbobj->getDBName() . '.' . $entity ."?id=".$id);

        if(empty($id)) {
            $this->_id = BaseMapper::save($this->_toArray(array('id', 'created', 'updated'), true), $this->_table_name, $this->_dbobj);
            $old_data = array();
            $updated = True;
            $cachekey = CacheKey::q($this->_dbobj->getDBName() . '.' . $entity ."?id=".$this->_id);
        } else {
            if(count($this->_dirty_keys) > 0) {
                $this->_dirty_keys['_id'] = 1;
                if($auto_sync_list && $old_data === NULL){
                    $old_data = DAL::get($cachekey);
                }
                BaseMapper::save($this->_toArray(array('created', 'updated'), true), $this->_table_name, $this->_dbobj);
                $updated = True;
            }
        }

        // reset dirty keys
        $this->_dirty_keys = array();
        if($auto_sync_list && $updated){
            DAL::s($cachekey, $old_data);
        } else if($updated){ // del KV cached by id
            DAL::delete($cachekey);
        }
        if($updated && !empty($old_data)){ // delete all KV cached by unique_key
            foreach($this->_unique_keys as $uk) {
                $uck = CacheKey::q($this->_dbobj->getDBName() . '.' . $entity ."?");
                foreach($uk as $f){
                    $uck->_and($f . '=' . $old_data[$f]);
                }
                DAL::delete($uck);
            }
        }
        return $updated;
    }

    public function _toArray($fields_excluded=array(), $dirty_only = false) {
        $result = array();

        foreach($this->_attributes as $attribute) {
            if(!in_array($attribute['name'], $fields_excluded)) {
                $key = '_'.$attribute['name'];
                if($dirty_only && !isset($this->_dirty_keys[$key])){
                    continue;
                }
                $result[$attribute['name']] = $this->_values[$key];
            }
        }
        return $result;
    }

    private function _getClassAttributes() {
        $attributes = $this->_getTableAttributes();
        return $attributes;
    }

    public function getTableProperties() {
        return $this->_properties;
    }

    private function _getTableAttributes() {
        $db_tables = get_table_info();
        $table = $this->_table_name;
        $table_info = $db_tables[$table];
        //$table_name = $table_info["__name"];
        //$db_type = $table_info["__db_type"];
        $this->_unique_keys = $table_info["__unique_keys"];
        unset($table_info["__name"]);
        unset($table_info["__db_type"]);
        unset($table_info["__unique_keys"]);

        $this->_properties = array();
        $attributes = array();
        foreach($table_info as $name => $type) {
            $this->_properties[] = $name;
            $attributes[] = array(
                "name" => $name,
                "type" => $type,
            );
        }
        return $attributes;
    }

    public function getTableNameFromClassName() {
        return to_plural(from_camel_case($this->_called_class));
    }

    // parent    -- parent
    //           -- current
    // associate -- child
    public static function saveObjects(
        $dbobj,
        &$array_objects, $object_name,
        $parent_id = 0, $parent_object_name = '',
        &$errors = array(), &$saved_objs = array(),
        $auto_sync_list = True) {

        $ids = array();
        if(!empty($errors)) {
            return $ids;
        }

        $singular_object_name = depluralize($object_name);
        if($singular_object_name === $object_name) {
            if(!empty($array_objects[0])) {
                $errors[] = 'singular object name should only have one object';
                return $ids;
            }
            $array_objects = array($array_objects);
        } else {
            if(empty($array_objects[0])) {
                $errors[] = 'plural object name should have a set of objects';
                return $ids;
            }
        }

        $class_name = ucfirst(to_camel_case($singular_object_name));
        foreach($array_objects as  $p => $array_object) {
            $old_data = array();
            $save_sub_objs = array();
            $object_id = isset($array_object['id'])?$array_object['id']:0;
            if($object_id > 0){
                $ck = CacheKey::q($dbobj->getDBName().".$singular_object_name?id=$object_id");
                $old_data = DAL::get($ck);
            }
            $object = new $class_name($dbobj, $object_id);
            $object_properties = $object->getTableProperties();

            $my_properties = array();
            $associate_properties = array();
            $associate_ids = array();

            $i = 0;
            foreach($array_object as $key=>$val) {
                if(in_array($key, $object_properties)) {
                    $my_properties[] = $key;
                } else {
                    if(is_array($val)) {
                        if($associate_info = valid_associate_objects($object_name, $key)) {
                            if($associate_info['where'] === 'parent' && !empty($val[0])) {
                                $errors[] = 'associate id in parent but child array have index 0';
                                return $ids;
                            } else if(
                                ($associate_info['where'] === 'child' || $associate_info['where'] === 'associate') &&
                                empty($val[0])) {
                                $errors[] = 'associate id in child or associate mapping but child array have no index 0';
                                return $ids;
                            }
                            $associate_properties[$i]['object_name'] = $key;
                            $associate_properties[$i]['info'] = $associate_info;

                            if(empty($val[0])) {
                                $val = array($val);
                            }
                            $associate_properties[$i]['val'] = $val;
                            $i++;
                        }
                    }
                }
            }

            foreach($associate_properties as $m => $associate_property) {
                $associate_object_name = $associate_property['object_name'];
                $associate_info = $associate_property['info'];
                $associate_vals = $associate_property['val'];

                foreach($associate_vals as $associate_val) {
                    $associate_fields = array_keys($associate_val);
                    if($associate_info['where'] === 'parent' && in_array($associate_info['field'], $my_properties)) {
                        $errors[] = 'associate field appears in the parent object which is not allowed';
                        return $ids;
                    } else if($associate_info['where'] === 'child' && in_array($associate_info['field'], $associate_fields)) {
                        $errors[] = 'parent field appears in the associate object which is not allowed';
                        return $ids;
                    }
                }

                $associate_ids[$m]['ids'] = self::saveObjects(
                    $dbobj,
                    $array_objects[$p][$associate_object_name], $associate_object_name,
                    $object_id, $object_name,
                    $errors, $save_sub_objs,
                    $auto_sync_list
                );
                if(!empty($errors)){
                    return $ids;
                }
                $associate_ids[$m]['properties'] = $associate_property;

            }

            // save objects level 1
            foreach($my_properties as $property) {
                $func = 'set'.  ucfirst(to_camel_case($property));
                if($object->$func($array_object[$property]) ===  false) {
                    $errors[] = "$object_name $func($array_object[$property]) return false";
                    return $ids;
                }
            }

            $object->_internal_save(NULL, TRUE);
            $object_id = $object->getId();
            if($object_id < 1){
                $errors[] = "save $object_name error";
                return $ids;
            }
            $ids[] = $object_id;
            array_push2($array_objects[$p], array('id'=>$object_id));

            foreach($associate_ids as $associate_id) {
                $associate_property = $associate_id['properties'];
                $associate_object_name = $associate_property['object_name'];
                $associate_class = ucfirst(to_camel_case(depluralize($associate_object_name)));
                $associate_info = $associate_property['info'];
                $associate_array = $array_object[$associate_object_name];
                if(depluralize($associate_object_name) === $associate_object_name) {
                    $associate_array = array($associate_array);
                }
                foreach($associate_id['ids'] as $n=>$id) {
                    if(!empty($array_object['id']) && !empty($associate_array[$n]['id'])) {
                        continue;
                    }
                    if($associate_info['where'] === 'parent') {
                        $object->set($associate_info['field'], $id);
                        $object->save();
                    } else if($associate_info['where'] === 'child') {
                        $associate_object = new $associate_class($dbobj, $id);
                        $associate_object->set($associate_info['field'], $object_id);
                        $associate_object->_internal_save(NULL, FALSE);
                    } else if($associate_info['where'] === 'associate') {
                        $associate_object = new $associate_class($dbobj);
                        $associate_object->setId($id);
                        BaseMapper::_internal_save_assoc($object, $associate_object, $dbobj);
                    }
                }
            }
            $ck = CacheKey::q($dbobj->getDBName().".$singular_object_name?id=$object_id");
            DAL::s($ck, $old_data);
            if(is_array($saved_objs)){
                $saved_objs[$ck->cacheKey()] = DAL::get($ck);
            }
            foreach($save_sub_objs as $sub_ck => $old_sub_data){ // sync after assco updated!
                DAL::s(CacheKey::q($sub_ck), $old_sub_data);
            }
        }
        return $ids;
    }

}
