<?php

class AnalyticsDatatable extends BaseDatatable {

    public function getDataRows() {

        global $site_domain, $dbconfig;

        $store_url = $this->action_params['store_url'];
        $store_id = $this->action_params['store_id'];
        $day_from = $this->action_params['from'];
        $day_to = $this->action_params['to'];
        //$store_url = '/store/splurge316/';
        $filters = "ga:pagePath=@$store_url";

        $service = new GoogleService();
        $service->setMethod('get_analytics_data');
        $service->setParams(array(
            'day_from' => $day_from,
            'day_to' => $day_to,
            'opt_params' => array(
                'filters' => $filters
            )
        ));
        $service->call();

        $homepage_reg = "#^\Q" . $store_url ."\E/?(\?(.+))?#";
        $product_reg = "#^\Q" . $store_url ."\E/products/item\?(ASIN|id)=([_0-9a-zA-Z]+)#";
        $data_rows = $service->getResponse();
        $formatted_data_rows = array();
        foreach($data_rows as $row){
            $nrow = array();
            $url = getSiteMerchantUrl($row[0]);
            $matches = array();
            if(preg_match($product_reg, $row[0], $matches)){
                $type = $matches[1];
                $id = $matches[2];
                if($type === 'ASIN'){
                    $nrow[0] = '<a href="' . $url .'" target="_blank">' . $id . '</a>';
                } else {
                    $parts = explode('_', $id);
                    if(sizeof($parts) ===  2) {
                        $aid = array_shift($parts);
                    }
                    $id = array_shift($parts);
                    $pk = CacheKey::q($dbconfig->store->name . "_" . $store_id . ".product?id=" . $id);
                    $p = BaseModel::findCachedOne($pk);
                    if(empty($p) || $p['status'] == DELETED) { continue; }
                    $p_img = reset($p['pictures']['45']);
                    $img_src = '<img src="' . $p_img . '">';
                    $nrow[0] = '<a href="' . $url .'" target="_blank">' . $img_src . shorten_description($p['name'], NULL, 90) . '</a>';
                }
            } else if(preg_match($homepage_reg, $row[0], $matches)){
                $text = "Store Homepage";
                if(isset($matches[2])){
                    $c = CacheKey::c($matches[2]);
                    $text = $text . "(" . $c->conditionSQL(NULL, FALSE, FALSE) . ")";
                }
                $nrow[0] = '<a href="' . $url .'" target="_blank">' . $text . '</a>';
            } else {
                $nrow[0] = $row[0];
                continue;
            }

            $nrow[1] = (int)($row[1]);
            $nrow[2] = (int)($row[2]);
            $nrow[3] = (int)($row[3]);
            $nrow[4] = (int)($row[4]);
            $formatted_data_rows[] = $nrow;
        }

        return array(
            'data' => $formatted_data_rows,
            'total_rows' => 4,
            'current_page' => 1,
        );
    }

    public function formatRows($data) {
        return $data;
    }

}