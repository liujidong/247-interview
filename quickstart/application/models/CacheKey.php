<?php
/*
 * API:
 *   - CacheKey::q("db.entity[?attr=value]") : create a completed query, which can generate a cache key
 *   - CacheKey::c("attr=value"): create a condtion (a incompleted query: no db and entity, only condition)
 *   - _and/_or : logic operate between q and c, or simple string
 *   - asc/desc: order by
 *   - limit(page_num, page_size): limit
 *   - cacheKey: generate cache key
 *   - conditionSQL: generate SQL
 *   - test: test a model with the cache key
 *   - match: match with a model/array, return keys(array)
 *   - getDBName/getEntity/getType..
 *   - getOrderInfo/getLimitation
 */

class CacheKey {

    private $key = '';
    private $dbname = '';           // account, store_321 ...
    private $entity = '';           // featured_products, products, store, product ...
    private $type = 'value';        // value/list
    private $condition = array("__type" => "NULL");
    private $orderby = array();          // order by name1, name2, ...
    private $order_direction = ASC;
    private $limitation = array();
    private $parameters = array();   // passed as parameters as Mapper::getCachedObject
    private $label = '';

    public function __construct($key = ''){
        $this->key = $key;
        if(!empty($this->key)){
            $this->parseKey();
        }
    }

    private function parseKey(){
        $reg_id = "[0-9a-zA-Z_]+";
        $reg_cache_key = "/^(($reg_id)\.)?($reg_id)\?(.*)$/";

        $match = array();
        if(preg_match($reg_cache_key, $this->key, $match)){
            $this->dbname = $match[2];
            $this->entity = $match[3];
            $depl_entity = depluralize($this->entity);
            if($depl_entity == $this->entity){
                $this->type = 'value';
            }else{
                $this->entity = $depl_entity;
                $this->type = 'list';
            }
            $this->parseCondition($match[4], 'AND');
        }
    }

    private function appendCondition(&$xcond, $sub_cond, $op){ // with simple reduce
        if(empty($sub_cond) || $sub_cond['__type'] === 'NULL') return;
        if(empty($xcond) || $xcond['__type'] == 'NULL'){
            $xcond = $sub_cond;
        } else if($xcond['__type'] == $op){
            if($sub_cond['__type'] == $op) { // expand sib cond
                foreach($sub_cond as $nk => $nv){
                    if($nk === "__type") continue;
                    $found = false;
                    foreach($xcond as $k => $v){
                        if($k === '__type') continue;
                        if($this->condEqual($v, $nv)) {
                            $found = true;
                            break;
                        }
                    }
                    if(!$found) $xcond[] = $nv;
                }
            } else { // append
                foreach($xcond as $k => $v){
                    if($k === '__type') continue;
                    if($this->condEqual($v, $sub_cond)) return;
                }
                $xcond[] = $sub_cond;
            }
        } else {
            if($this->condEqual($xcond, $sub_cond)) return;
            $old_cond = $xcond;
            $xcond = array("__type" => $op);
            $xcond[] = $old_cond;
            $xcond[] = $sub_cond;
        }
    }

    private function parseCondition($cond_str, $op = 'AND'){
        $sub_cond = $this->parseConditionX($cond_str);
        $this->appendCondition($this->condition, $sub_cond, $op);
    }

    private function parseConditionS($cond_str){
        $reg_id = "[0-9a-zA-Z_]+";
        $reg_op = ">=|<=|!=|->|<-|=|>|<";
        $reg_cond="/^($reg_id)($reg_op)(.*)/";
        $reg_order="/(asc|desc|sort)\[($reg_id(,$reg_id)*)\]/";
        if(preg_match($reg_cond, $cond_str, $match)){
            $this->parameters[$match[1]] = $match[3];
            return array("__type" => 'ATOM', $match[1],$match[2],$match[3]);
        } else if(preg_match($reg_order, $cond_str, $match)) {
            $this->order_direction = $match[1] === 'asc' ? ASC : DESC ;
            $this->orderby = explode(',', $match[2]);
            return NULL;
        }
        return NULL;
    }

    public function parseConditionX($cond_str, $sub = false){
        // ((id=3&status=2)|name=1)&des=1
        // (name=1|(id=3&status=2))&des=1
        if(empty($cond_str)) return NULL;

        if($sub) {
            while($cond_str[0] === '(' && substr($cond_str, -1) === ')') {
                $cond_str = substr($cond_str, 1, -1);
            }
        }

        if(strpos($cond_str, '&') === false && strpos($cond_str, '|') === false){
            return $this->parseConditionS($cond_str);
        }

        $xcond = array();
        $depth = 0;
        $next_index = 0;
        $next_op = ''; // AND/OR
        for($i = 0; $i < strlen($cond_str); $i++){
            if($cond_str[$i] == '('){
                $depth ++;
            } else if($cond_str[$i] == ')'){
                $depth --;
            } else if($cond_str[$i] == '&' || $cond_str[$i] == '|'){
                if($depth != 0) continue;
                $sub_cond = substr($cond_str, $next_index, $i - $next_index);
                $sub_cond_tree = $this->parseConditionX($sub_cond, true);
                if(empty($sub_cond_tree)) continue;
                $this->appendCondition($xcond, $sub_cond_tree, $next_op);
                $next_index = $i + 1;
                $next_op = $cond_str[$i] == '&' ? 'AND' : 'OR';
            }
        }
        // the last cond
        $sub_cond = substr($cond_str, $next_index);
        $sub_cond_tree = $this->parseConditionX($sub_cond, true);
        if(!empty($sub_cond_tree)){
            $this->appendCondition($xcond, $sub_cond_tree, $next_op);
        }
        return $xcond;
    }

    private function condEqual($cond_l, $cond_r){
        if($cond_l['__type'] != $cond_r['__type']) return false;
        if($cond_l['__type'] === "NULL") return true;
        if($cond_l['__type'] === "ATOM") {
            if($cond_l[0] != $cond_r[0]) return false;
            if($cond_l[1] != $cond_r[1]) return false;
            if($cond_l[2] != $cond_r[2]) return false;
            if($cond_l[2] === '_') return false;
            return true;
        }
        if($cond_l['__type'] === "AND" || $cond_l['__type'] === "OR" ) {
            if(count($cond_l) != count($cond_r)) return false;
            foreach($cond_l as $k => $v){
                if($k === '__type') continue;
                if(!isset($cond_r[$k])) return false;
                if(!$this->condEqual($v, $cond_r[$k])) return false;
            }
            return true;
        }
        return false;
    }

    public function mattr($model, $key){
        if(is_array($model)){
            if(isset($model[$key])){
                return $model[$key];
            }
            return "";
        }
        if($model instanceof BaseModel){
            return $model->get($key);
        }
        return "";
    }

    public function addCond($cond, $op = 'AND'){
        $this->parseCondition($cond, $op);
    }

    public function merge($ck, $op = 'AND'){
        $ocond = $ck->getCondition();
        $this->appendCondition($this->condition, $ocond, $op);
        //merge order info
        $order_info = $ck->getOrderInfo();
        if(!empty($order_info['orderby'])){
            $this->orderby = $order_info['orderby'];
            $this->order_direction = $order_info['order_direction'];
        }

        //merge limitation
        $lim = $ck->getLimitation();
        if(!empty($lim['page_num']) && !empty($lim['page_size'])){
            $this->limitation = $lim;
        }
        //merge parameters
        $this->parameters = array_merge($this->parameters, $ck->getParameters());
    }

    private function condCacheKey($cond, $parenthesis = false){
        if($cond['__type'] === 'ATOM') {
            return "$cond[0]$cond[1]$cond[2]";
        } else if($cond['__type'] === 'AND' || $cond['__type'] === 'OR'){
            $sep = $cond['__type'] === 'AND' ? "&" : "|";
            $conds_str = array();
            foreach($cond as $k => $v){
                if($k === '__type') continue;
                $conds_str[] = $this->condCacheKey($v, true);
            }
            sort($conds_str);
            $ret = implode($sep, $conds_str);
            if($parenthesis){
                return "($ret)";
            }
            return $ret;
        }
        //NULL Cond
        return "";
    }

    private function condSQL($cond, $alias = "", $parenthesis = false){
        if($cond['__type'] === 'ATOM') {
            $value = $cond[2];
            if(!is_numeric($value)){
                $value = "\"$value\"";
            }
            $op = $cond[1];
            if($op == "->"){ // operatror in
                $op = "IN";
                $value = "($cond[2])";
            }
            if($op == "<-"){ // operator contains
                $reg_value = "(^$cond[2],)|(,$cond[2]$)|(,$cond[2],)";
                return "($cond[0] = $value OR $cond[0] REGEXP \"$reg_value\")";
            }
            if(empty($alias)){
                return "$cond[0] $op $value";
            }
            return "$alias.$cond[0] $op $value";
        } else if($cond['__type'] === 'AND' || $cond['__type'] === 'OR'){
            $sep = $cond['__type'] === 'AND' ? " and " : " or ";
            $conds_str = array();
            foreach($cond as $k => $v){
                if($k === '__type') continue;
                $conds_str[] = $this->condSQL($v, $alias, true);
            }
            sort($conds_str);
            $ret = implode($sep, $conds_str);
            if($parenthesis){
                return "($ret)";
            }
            return $ret;
        }
    }

    private function _test($model, $cond){
        if($cond['__type'] === 'ATOM') {
            $mv = $this->mattr($model, $cond[0]);
            $ov = $cond[2];
            switch($cond[1]){
            case "=":
                return $mv == $ov;
            case "!=":
                return $mv != $ov;
            case ">":
                return $mv >  $ov;
            case ">=":
                return $mv >= $ov;
            case "<":
                return $mv <  $ov;
            case "<=":
                return $mv <= $ov;
            case "->":
                $ov_array = explode(",", $ov);
                return in_array($mv, $ov_array);
            case "<-":
                $mv_array = explode(",", $mv);
                return in_array($ov, $mv_array);
            default:
                return false;
            }
        } else if($cond['__type'] === 'AND'){
            foreach($cond as $k => $v){
                if($k === '__type') continue;
                $ret = $this->_test($model, $v);
                if(!$ret) return false;
            }
            return true;
        } else if($cond['__type'] === 'OR'){
            foreach($cond as $k => $v){
                if($k === '__type') continue;
                $ret = $this->_test($model, $v);
                if($ret) return true;
            }
            return false;
        }
        // NULL Cond
        return true;
    }

    private function _reduce_match_results($results, $op){
        $new_results = array();
        for($i=0; $i<count($results); $i++){
            $item = array();
            foreach($results[$i] as $k => $v){
                $item[$this->condCacheKey($v)] = $v;
            }
            $item = array_values($item);
            if(count($item) == 1) {
                $new_results[] = $item[0];
            } else if(count($item) > 1) {
                $item['__type'] = $op;
                $new_results[] = $item;
            }
        }
        return $new_results;
    }

    private function _match($model, $cond){
        if($cond['__type'] === 'ATOM') {
            $mv = $this->mattr($model, $cond[0]);
            $ov = $cond[2];
            if($ov === "_" &&  $cond[1] != "<-"){
                $cond[2] = $mv;
                return array($cond);
            }
            $ret = array();
            switch($cond[1]){
            case "=":
                if($mv == $ov) $ret[] = $cond;
                break;
            case "!=":
                if($mv != $ov) $ret[] = $cond;
                break;
            case ">":
                if($mv >  $ov) $ret[] = $cond;
                break;
            case ">=":
                if($mv >= $ov) $ret[] = $cond;
                break;
            case "<":
                if($mv <  $ov) $ret[] = $cond;
                break;
            case "<=":
                if($mv <= $ov) $ret[] = $cond;
                break;
            case "->":
                $ov_array = explode(",", $ov);
                if(in_array($mv, $ov_array)) $ret[] = $cond;
                break;
            case "<-":
                $mv_array = explode(",", $mv);
                if($ov == '_'){
                    foreach($mv_array as $tmv){
                        $tmp_cond = $cond;
                        $tmp_cond[2] = $tmv;
                        $ret[] = $tmp_cond;
                    }
                } else if(in_array($ov, $mv_array)) {
                    $ret[] = $cond;
                }
                break;
            default:
                break;
            }
            return $ret;
        } else if($cond['__type'] === 'AND'){
            $matched_conds = array();
            foreach($cond as $k => $v){
                if($k === '__type') continue;
                $ret = $this->_match($model, $v);
                if(count($ret)<1) return array();
                $matched_conds[] = $ret;
            }
            $conds = array_combinate($matched_conds);
            return $this->_reduce_match_results($conds, 'AND');
        } else if($cond['__type'] === 'OR'){
            $matched_conds = array();
            foreach($cond as $k => $v){
                // trick: use (matched ck, true)|(unmatched ck, false) as value
                if($k === '__type') continue;
                $ret = $this->_match($model, $v);
                if(count($ret)<1) { // unmatched, use origin cond
                    $matched_conds[] = array(array($v), false);
                } else { // matched
                    $matched_conds[] = array($ret, true);
                }
            }
            $real_matched_conds = array();
            $any_match = false;
            foreach($matched_conds as $k => $v){
                $real_matched_conds[] = $v[0];
                if($v[1]) $any_match = true;
            }
            if(!$any_match) return array();
            $conds = array_combinate($real_matched_conds);
            return $this->_reduce_match_results($conds, 'OR');
        }
        // NULL Cond
        return array();
    }

    public function getCondition(){
        return $this->condition;
    }

    public function getParameters() {
        return array_merge($this->parameters, array(
            '_cachekey' => $this
        ));
    }

    // create a query
    public static function q($type){
        $key = $type;
        if(!strpos($key, "?")) $key = $type . "?";
        $ck = new CacheKey($key);
        return $ck;
    }

    // create a condition
    public static function c($cond){
        $ck = new CacheKey("");
        $ck->addCond($cond);
        return $ck;
    }

    public function _and($ock){
        if($ock instanceof self){
            $this->merge($ock, 'AND');
        } else {
            $this->addCond($ock, 'AND');
        }
        return $this;
    }

    public function _or($ock){
        if($ock instanceof self){
            $this->merge($ock, 'OR');
        } else {
            $this->addCond($ock, 'OR');
        }
        return $this;
    }

    public function asc($cond){
        $this->order_direction = ASC;
        $this->orderby = explode(',', $cond);
        return $this;
    }

    public function desc($cond){
        $this->order_direction = DESC;
        $this->orderby = explode(',', $cond);
        return $this;
    }

    public function sort($cond){
        return $this->desc($cond);
    }

    public function limit($page_num, $page_size){
        if($page_num<1) $page_num = 1 ;
        $this->limitation['page_num'] = $page_num;
        $this->limitation['page_size'] = $page_size;
        return $this;
    }

    private function realDBName(&$dbname_map){
        $dbname = $this->dbname;
        if(isset($dbname_map[$dbname])){
            $dbname = $dbname_map[$dbname];
        }
        if(!empty($dbname)){
            $dbname = $dbname . ".";
        }
        return $dbname;
    }

    public function cacheKey($dbname_map=array()){
        $ck = $this->condCacheKey($this->condition);
        if(!empty($this->orderby)){
            //$ckname = $this->order_direction === "ASC" ? "ascby" : "descby";
            $ckname = "sort";
            $attrs = implode(",", $this->orderby);
            if(!empty($ck)){
                $ck =  $ck . "&";
            }
            $ck .= "$ckname" . "[$attrs]";
        }
        $entity = $this->entity;
        if($this->type === "list") {
            $entity = to_plural($entity);
        }
        $dbname = $this->realDBName($dbname_map);
        if(!empty($ck) && !empty($entity)){
            $ck = "?" . $ck;
        }
        return $dbname.$entity.$ck;
    }

    public function orderSQL($alias=''){
        if(empty($this->orderby)) return "";
        $DIRECT = $this->order_direction;
        if(empty($alias)){
            $attrs = implode(",", $this->orderby);
        } else {
            $func = function($a) use ($alias) { return $alias . "." . $a;};
            $attrs = array_map($func, $this->orderby);
            $attrs = implode(",", $attrs);
        }
        return " ORDER BY $attrs $DIRECT";
    }

    public function limitSQL(){
        if(empty($this->limitation['page_num']) || empty($this->limitation['page_size'])){
            return "";
        }
        $size = $this->limitation['page_size'];
        $start = ($this->limitation['page_num'] - 1) * $size;
        return " LIMIT $start, $size";
    }

    public function conditionSQL($alias='', $orderby=TRUE, $limit=TRUE){
        $sql = $this->condSQL($this->condition, $alias);
        if($orderby) $sql .= $this->orderSQL($alias);
        if($limit) $sql .= $this->limitSQL();
        return $sql;
    }

    public function test($model){
        if(empty($model)) return false;
        //$id = $this->mattr($model, 'id');
        //if(empty($id)) return false;
        return $this->_test($model, $this->condition);
    }

    public function match($model, $dbname_map=array()){
        if(empty($model)) return array();
        //$id = $this->mattr($model, 'id');
        //if(empty($id)) return array();
        $conds = $this->_match($model, $this->condition);
        $ret = array();
        $dbname = $this->realDBName($dbname_map);
        foreach($conds as $cond){
            $ck = $this->condCacheKey($cond);
            if(!empty($this->orderby)){
                //$ckname = $this->order_direction === "ASC" ? "ascby" : "descby";
                $ckname = "sort";
                $attrs = implode(",", $this->orderby);
                $ck .= "&$ckname" . "[$attrs]";
            }
            $entity = $this->entity;
            if($this->type === "list") {
                $entity = to_plural($entity);
            }
            $ret["$dbname$entity?" . $ck] = 1;
        }
        return array_keys($ret);
    }

    public function getDBName(){
        return $this->dbname;
    }

    public function setDBName($dbname) {
        $this->dbname = $dbname;
        return $this;
    }

    public function getEntity(){
        return $this->entity;
    }

    public function getType(){
        return $this->type;
    }

    public function getOrderInfo(){
        return array('orderby' => $this->orderby, 'order_direction' => $this->order_direction);
    }

    public function getLimitation(){
        return $this->limitation;
    }

    public function copy(){
        $key = $this->cacheKey();
        if(strpos($key, "?")) {
            $ret = self::q($key);
        } else {
            $ret = self::c($key);
        }
        $limit = $this->getLimitation();
        if(count($limit)>0){
            $ret->limit($limit['page_num'], $limit['page_size']);
        }
        $ret->label($this->getLabel());
        return $ret;
    }

    public function label($l){
        if(!empty($l)){
            $this->label = $l;
        }
        return $this;
    }

    public function getLabel(){
        return $this->label;
    }
}
