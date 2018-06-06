<?php

class GlobalCategoriesMapper {

    public static function deleteCategory($id, $dbobj) {
        $account_dbname = $dbobj->getDBName();
        $sql1 = "select count(1) as cnt from global_categories where parent_id = $id";
        $has_sub_category = false;

        if($res = $dbobj->query($sql1, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $has_sub_category = $record['cnt'] > 0 ; 
            }
        }

        if($has_sub_category) {
            // can't delete this category
            return false;
        }

        $category_obj = new GlobalCategory($dbobj, $id);
        if(!empty2($category_obj->getId())) {
            $category_obj->setStatus(DELETED);
            $category_obj->save();
        }
        return true;
    }

    public static function getCachedObjectList($params, $dbobj){
        $ck = $params['_cachekey'];
        $condition = $ck->conditionSQL();
        $dbname = $ck->getDBName();
        $sql = "select id, rank from global_categories where ".$condition;
        $return = array();

        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $key = $dbname.".global_category?id=".$record['id'];
                $return[$key] = $record['rank'];
            }
        };
        return $return;
    }

    public static function saveCategory($category_string, $account_dbobj) {
        
        $parts = trim2(explode('>', $category_string));
        $levels = sizeof($parts);
        
        $parent_id = 0;
        $path = '';

        // loop through each category
        foreach($parts as $depth=>$category) {
            $path = empty($path)? $category : $path.' > '.$category;
            $global_category = new GlobalCategory($account_dbobj);
            $global_category->findOne("depth =  $depth and path = '" . $path . "'");
            if($global_category->getId()<1) {
                $global_category->setDepth($depth);
                $global_category->setPath($path);
                $global_category->setName($category);
                $global_category->setParentId($parent_id);
                $global_category->save();
            }
            $parent_id = $global_category->getId();
            if(empty2($global_category->getRank())) {
                $global_category->setRank($global_category->getId());
                $global_category->save();
            }
        }
        return true;
    }
        
    // exchange two categories' rank
    public static function exchangeCategoryRank($categories, $account_dbobj) {
        BaseModel::saveObjects($account_dbobj, $categories, 'global_categories');
        return true;
    }  
    
    public static function isSubCategory($parent_id, $child_id) {
        global $dbconfig;
        $account_dbname = $dbconfig->account->name;
        $parent_key = CacheKey::q($account_dbname.'.global_category?id='.$parent_id);
        $child_key = CacheKey::q($account_dbname.'.global_category?id='.$child_id);
        
        $parent = BaseModel::findCachedOne($parent_key);
        $child = BaseModel::findCachedOne($child_key);
        
        $parent_path = $parent['path'];
        $child_path = $child['path'];
        if(strpos($child_path, $parent_path) === false) {
            return false;
        } else {
            return true;
        }
        
    }
    
    public static function getChildCategoryIds($dbobj, $parent_id) {
        $sql = 'select id from global_categories where parent_id='.$parent_id;
        $return = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
        }
        return $return;
    }
    
}
