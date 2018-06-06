<?php

class AmazonProductsDatatable extends BaseDatatable {

    public static function url2asin($url){
        $regexp_asin_0 = "#/(dp|gp|product)/([0-9-A-Z]+)#";
        $regexp_asin_1 = "#^([0-9-A-Z]+)$#";
        $matches = array();
        $asin = FALSE;
        if(preg_match($regexp_asin_0, $url, $matches)){
            $asin = $matches[2];
        } else if(preg_match($regexp_asin_1, $url, $matches)){
            $asin = $url;
        }
        return $asin;
    }

    protected function _create(){
        if(checkIsSet($this->action_params, 'url', 'featured-value', 'featured-score', 'global_category')) {
            $url = $this->action_params['url'];
            $asin = self::url2asin($url);
            if(empty($asin)) {
                // error, no ASIN
                $this->errors[] = "Bad ASIN";
                return false;
            }
            $service = AmazonSearchService::getInstance();
            $service->setParams(array(
                'ASIN' => $asin,
                'save_to_db' => TRUE,
                'db_data' => array(
                    'featured' => $this->action_params['featured-value'],
                    'featured_score' => $this->action_params['featured-score'],
                    'global_category_id' => $this->action_params['global_category'],
                ),
            ));
            $service->setMethod('lookup');
            try{
                $service->call();
            } catch(Exception $e) {
                error_log($e);
                $this->errors[] = "Amazon Prodduct Lookup error";
                return;
            }
            if($service->getStatus() != 0){
                $this->errors[] = "Amazon Prodduct Lookup error";
                return;
            }
        }
    }

    protected function _update(){
        global $account_dbobj;

        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        //dddd($this->action_params);
        $id = $parameters['id'];

        if(checkIsSet($this->action_params, 'url', 'featured-value', 'featured-score', 'amazon_update', 'global_category')) {
            $force_amazon_update = (bool)($this->action_params['amazon_update']);
            $url = $this->action_params['url'];
            $asin = self::url2asin($url);
            if(empty($asin)) {
                // error, no ASIN
                $this->errors[] = "Bad ASIN";
                return false;
            }
            $ap = new AmazonProduct($account_dbobj, $id);
            if($force_amazon_update || $ap->getAsin() != $asin){
                $service = AmazonSearchService::getInstance();
                $service->setParams(array(
                    'ASIN' => $asin,
                    'save_to_db' => TRUE,
                    'db_data' => array(
                        'featured' => $this->action_params['featured-value'],
                        'featured_score' => $this->action_params['featured-score'],
                        'global_category_id' => $this->action_params['global_category'],
                    ),
                ));
                $service->setMethod('lookup');
                try{
                    $service->call();
                } catch(Exception $e) {
                    error_log($e);
                    $this->errors[] = "Amazon Prodduct Lookup error";
                    return;
                }
                if($service->getStatus() != 0){
                    $this->errors[] = "Amazon Prodduct Lookup error";
                    return;
                }
            } else {
                $ap->setFeatured($this->action_params['featured-value']);
                $ap->setFeaturedScore($this->action_params['featured-score']);
                $ap->setGlobalCategoryId($this->action_params['global_category']);
                $ap->save();
            }
        }
    }

    protected function _delete() {
        global $account_dbobj;
        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        $id = $parameters['id'];
        $obj = new AmazonProduct($account_dbobj, $id);
        $obj->setStatus(DELETED);
        $obj->save();
    }
}
