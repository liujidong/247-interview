<?php

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\CartCreate;
use ApaiIO\ApaiIO;

class AmazonSearchService extends BaseService {


    // input: keywords, searchIndex (optional)
    public function search() {

        global $amazonconfig, $redis;

        $keywords = $this->params['keywords'];
        $searchIndex = default2String($this->params['searchIndex'], \AmazonSearchIndex::All);
        $itemPage = default2Int($this->params['page'], 1);
        
        $lck = lck_amazon_products($keywords, $searchIndex, $itemPage);
        if($this->response = $redis->get($lck->cacheKey())) {
            $this->response = json_decode($this->response, true);
        } else {
            $conf = new GenericConfiguration();
            $conf
                ->setCountry('com')
                ->setAccessKey($amazonconfig->api->access_key)
                ->setSecretKey($amazonconfig->api->secret)
                ->setAssociateTag($amazonconfig->api->affiliate_id);
            $apaiIO = new ApaiIO($conf);

            $search = new Search();
            $search->setCategory($searchIndex);
            $search->setKeywords($keywords);
            $search->setResponseGroup(array(
                \AmazonResponseGroups::Images,
                \AmazonResponseGroups::ItemAttributes,
                \AmazonResponseGroups::Offers,
            ));
            $search->setItemPage($itemPage);
            if($searchIndex != 'All') {
                $search->setSort('salesrank');
            }

            $formattedResponse = $apaiIO->runOperation($search);
            $xml = simplexml_load_string($formattedResponse);
            $json = json_decode(json_encode($xml), true);
            if(isset($json['Items']['Request']['Errors'])){
                $this->response = array(
                    'savable_objects' => array(),
                    'view_objects' => array(),
                    'total_rows' => 0,
                    'current_page' => 1,
                );
                return $this->response;
            }

            $total_rows = $json['Items']['TotalResults'];
            // dirty fix
            if($total_rows>100) $total_rows = 100;
            $current_page = $itemPage;
            //$current_page = 1;
            $products = $json['Items']['TotalResults']>0?$json['Items']['Item']:array();
            $savable_objects = array();
            $view_objects = array();
            $i=0;
            foreach($products as $product) {
                if($json['Items']['TotalResults'] == 1) $product = $products;
                if(!isset($product['ImageSets']['ImageSet'])) {
                    continue;
                }
                $savable_objects[$i]['external_ref_id'] = $product['ASIN'];
                $savable_objects[$i]['purchase_url'] = $product['DetailPageURL'];
                $savable_objects[$i]['name'] = $product['ItemAttributes']['Title'];
                $savable_objects[$i]['description'] = '';
                if(!empty($product['ItemAttributes']['ListPrice'])){
                    $savable_objects[$i]['price'] = $product['ItemAttributes']['ListPrice']['Amount']/100;
                } else if(!empty($product['OfferSummary']['LowestNewPrice'])){
                    if(isset($product['OfferSummary']['LowestNewPrice']['Amount'])){
                        $savable_objects[$i]['price'] = $product['OfferSummary']['LowestNewPrice']['Amount']/100;
                    } else {
                        $savable_objects[$i]['price'] = " -- ";
                    }
                } else {
                    $savable_objects[$i]['price'] = " -- ";
                }

                if(isset($product['ItemAttributes']['Feature'])) {
                    $savable_objects[$i]['description'] = is_array($product['ItemAttributes']['Feature']) ?
                        join('. ', $product['ItemAttributes']['Feature']) :
                        $product['ItemAttributes']['Feature'];
                }

                $view_objects[$i]['ASIN'] = $savable_objects[$i]['external_ref_id'];
                $view_objects[$i]['purchase_url'] = $savable_objects[$i]['purchase_url'];
                $view_objects[$i]['name'] = $savable_objects[$i]['name'];
                $view_objects[$i]['description'] = $savable_objects[$i]['description'];

                if(!empty($product['ItemAttributes']['ListPrice'])){
                    $view_objects[$i]['price'] = $product['ItemAttributes']['ListPrice']['FormattedPrice'];
                } else if(!empty($product['OfferSummary']['LowestNewPrice'])){
                    $view_objects[$i]['price'] = $product['OfferSummary']['LowestNewPrice']['FormattedPrice'];
                } else {
                    $view_objects[$i]['price'] = '';
                }


                if(isset($product['ImageSets']['ImageSet'])) {
                    $images = $product['ImageSets']['ImageSet'];
                    if(!isset($images[0])) {
                        $images = array($images);
                    }
                    foreach($images as $j => $image) {
                        if(isset($image['LargeImage']['URL'])) {
                            $savable_objects[$i]['pictures'][$j]['original_url'] = $image['LargeImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['url'] = $image['LargeImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['orderby'] = $j;

                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][0]['type'] = CONVERTED736;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][0]['url'] = $image['LargeImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][1]['type'] = CONVERTED550;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][1]['url'] = $image['LargeImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][2]['type'] = CONVERTED236;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][2]['url'] = $image['LargeImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][3]['type'] = CONVERTED192;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][3]['url'] = $image['MediumImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][4]['type'] = CONVERTED70;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][4]['url'] = $image['TinyImage']['URL'];
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][5]['type'] = CONVERTED45;
                            $savable_objects[$i]['pictures'][$j]['converted_pictures'][5]['url'] = $image['ThumbnailImage']['URL'];

                            $view_objects[$i]['pictures']['736'][$j] = $image['LargeImage']['URL'];
                            $view_objects[$i]['pictures']['550'][$j] = $image['LargeImage']['URL'];
                            $view_objects[$i]['pictures']['236'][$j] = $image['LargeImage']['URL'];
                            $view_objects[$i]['pictures']['192'][$j] = $image['MediumImage']['URL'];
                            $view_objects[$i]['pictures']['70'][$j] = $image['TinyImage']['URL'];
                            $view_objects[$i]['pictures']['45'][$j] = $image['ThumbnailImage']['URL'];
                            $view_objects[$i]['store_name'] = 'Marketplace';
                            $view_objects[$i]['product_url'] = '/store/marketplace/products/item?ASIN='.$view_objects[$i]['ASIN'];
                            $view_objects[$i]['store_url'] = getStoreUrl('marketplace');
                        }
                    }
                }
                if($json['Items']['TotalResults'] > 1) {
                    $i++;
                }
            }

            $this->response = array(
                'savable_objects' => $savable_objects,
                'view_objects' => $view_objects,
                'total_rows' => empty($view_objects) ? 0 : $total_rows,
                'current_page' => $current_page
            );
            if($searchIndex != 'All') {
                $redis->set($lck->cacheKey(), json_encode($this->response), 60*60*24);
            }
        }
        return $this->response;
        
    }


    public function lookup() {

        global $amazonconfig, $account_dbobj;

        $conf = new GenericConfiguration();
        $conf
            ->setCountry('com')
            ->setAccessKey($amazonconfig->api->access_key)
            ->setSecretKey($amazonconfig->api->secret)
            ->setAssociateTag($amazonconfig->api->affiliate_id);

        $apaiIO = new ApaiIO($conf);

        $lookup = new Lookup();
        $ASIN = $this->params['ASIN'];
        $save_to_db = isset($this->params['save_to_db']) ? (bool)($this->params['save_to_db']) : FALSE;
        $need_format = isset($this->params['format']) ? (bool)($this->params['format']) : TRUE;

        $lookup->setItemId($ASIN);
        //http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
        $lookup->setResponseGroup(array(
            \AmazonResponseGroups::Images,
            \AmazonResponseGroups::ItemAttributes,
            \AmazonResponseGroups::Offers,
            //\AmazonResponseGroups::BrowseNodes,
        ));

        $formattedResponse = $apaiIO->runOperation($lookup);
        $xml = simplexml_load_string($formattedResponse);
        $json = json_decode(json_encode($xml), true);
        $data = $json['Items']['Item'];

        if(!$need_format && !$save_to_db){
            $this->response = $data;
            return $data;
        }

        if($save_to_db){
            $ap = new AmazonProduct($account_dbobj);
            $ap->findOne("asin = '" . $ASIN . "'");
            if($ap->getId() < 1){
                $ap->setAsin($ASIN);
            }
            $db_data = isset($this->params['db_data']) ? $this->params['db_data'] : array();
            foreach($db_data as $k => $v){
                $ap->set($k, $v);
            }
        }
        //if($this->params['format']){
        if(true){
            $product = $data;
            $data = array(
                "resell" => 1,
                "store_subdomain" => 'marketplace',
                "store_name" =>  "Market Place",
                "custom_fields" => array(),
                "store_url" => "/store/marketplace",
                "commission" => 0,
                "shipping" => 0,
            );
            $data['external_ref_id'] = $product['ASIN'];
            $data['purchase_url'] = $product['DetailPageURL'];
            if($save_to_db){
                $ap->set('purchase_url', $data['purchase_url']);
            }
            //$data['product_url'] = $product['DetailPageURL'];
            $data['name'] = $product['ItemAttributes']['Title'];
            if($save_to_db){
                $ap->set('name', $data['name']);
            }
            $data['description'] = '';
            if(!empty($product['ItemAttributes']['ListPrice'])){
                $data['price'] = $product['ItemAttributes']['ListPrice']['Amount']/100;
            } else if(!empty($product['OfferSummary']['LowestNewPrice'])){
                $data['price'] = $product['OfferSummary']['LowestNewPrice']['Amount']/100;
            } else {
                $data['price'] = " -- ";
            }
            if($save_to_db){
                $ap->set('price', $data['price']);
            }
            if(isset($product['ItemAttributes']['Feature'])) {
                $data['description'] = is_array($product['ItemAttributes']['Feature']) ?
                    join('. ', $product['ItemAttributes']['Feature']) :
                    $product['ItemAttributes']['Feature'];
                if($save_to_db){
                    $ap->set('description', $data['description']);
                }
            }

            if(isset($product['ImageSets']['ImageSet'])) {
                $images = $product['ImageSets']['ImageSet'];
                if(!isset($images[0])) {
                    $images = array($images);
                }
                foreach($images as $j => $image) {
                    if(isset($image['LargeImage']['URL'])) {
                        $data['pictures'][CONVERTED736][$j] = $image['LargeImage']['URL'];
                        $data['pictures'][CONVERTED550][$j] = $image['LargeImage']['URL'];
                        $data['pictures'][CONVERTED236][$j] = $image['LargeImage']['URL'];
                        $data['pictures'][CONVERTED192][$j] = $image['MediumImage']['URL'];
                        $data['pictures'][CONVERTED70][$j] = $image['TinyImage']['URL'];
                        $data['pictures'][CONVERTED45][$j] = $image['ThumbnailImage']['URL'];
                    }
                }
            }
            if(empty($data['pictures'])){
                $data['pictures'][CONVERTED736] = array();
                $data['pictures'][CONVERTED550] = array();
                $data['pictures'][CONVERTED236] = array();
                $data['pictures'][CONVERTED192] = array();
                $data['pictures'][CONVERTED70]  = array();
                $data['pictures'][CONVERTED45]  = array();
            }
            if($save_to_db){
                $ap->set('pictures', json_encode($data['pictures']));
            }
        }
        if($save_to_db){
            $ap->save();
            $data['id'] = $ap->getId();
            DAL::delete($ap->getCacheKey());
        }
        $this->response = $data;
        return $data;
    }

    public static function getFeaturedProducts($keyword = 'electronics', $searchIndex = AmazonSearchIndex::Electronics) {
        $service = AmazonSearchService::getInstance();
        $service->setMethod('search');
        $service->setParams(array(
            'keywords' => $keyword,
            'searchIndex' => $searchIndex
        ));
        $service->call();
        $response = $service->getResponse();
        return array(
            'data' => $response['view_objects'],
            'total_rows' => $response['total_rows'],
            'current_page' => $response['current_page']
        );
    }

    public static function cart($products){
        global $amazonconfig, $account_dbobj;

        $conf = new GenericConfiguration();
        $conf
            ->setCountry('com')
            ->setAccessKey($amazonconfig->api->access_key)
            ->setSecretKey($amazonconfig->api->secret)
            ->setAssociateTag($amazonconfig->api->affiliate_id);

        $apaiIO = new ApaiIO($conf);

        $cart = new CartCreate();
        foreach($products as $p){
            $cart->addItem($p['external_id'], $p['quantity'], TRUE);
        }
        //http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
        $cart->setResponseGroup(array(
            \AmazonResponseGroups::Cart
        ));

        $formattedResponse = $apaiIO->runOperation($cart);
        $xml = simplexml_load_string($formattedResponse);
        $json = json_decode(json_encode($xml), true);
        return $json['Cart']['PurchaseURL'];
    }
}
