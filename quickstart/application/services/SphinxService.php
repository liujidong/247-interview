<?php

class SphinxService extends BaseService {

    private $_sphinx = null;
    private $_host;
    private $_port;
    private $_index = 'search_products';
    private $_limit = PRODUCT_NUM_PER_PAGE;
    private $_offset = 0;
    private $_sortby = 'search_product_id';
    private $_total_found = 0;

    public function __construct() {
        global $sphinx_config;
        $this->_host = $sphinx_config->api->search->host;
        $this->_port = $sphinx_config->api->search->port;
        if (!$this->_sphinx)
            $this->_sphinx = new SphinxClient();
    }

    //input query ,account_dbobj [index,limit,offset,attribute,min,max]
    //output matches
    public function query() {

        $this->response = array(
            'products' => array(),
            'total_found' => 0
        );
        if (empty($this->params['query'])) {
            $this->status = 1;
            return;
        }
        $query = $this->params['query'];
        $account_dbobj = $this->params['account_dbobj'];
        if (isset($this->params['limit']))
            $this->_limit = (int) $this->params['limit'];
        if (isset($this->params['offset']))
            $this->_offset = (int) $this->params['offset'];
        $limit = $this->_limit;
        $offset = $this->_offset * PRODUCT_NUM_PER_PAGE;
        $index = $this->_index;
        $host = $this->_host;
        $port = $this->_port;
        //match sorting mode (default is SPH_SORT_RELEVANCE)
        //$this->_sphinx->SetSortMode ( SPH_SORT_ATTR_DESC, $this->_sortby );    

        $this->_sphinx->SetServer($host, $port);
        if ($this->_sphinx->IsConnectError()) {
            $this->status = 1;
            return;
        }
        $this->_sphinx->SetLimits($offset, $limit, ($limit > 1000) ? $limit : 1000);
        $this->_sphinx->SetSortMode(SPH_SORT_ATTR_DESC, $this->_sortby);
        $res = $this->_sphinx->Query($query, $index);

        if($res === false) {
            Log::write(INFO, "Query failed: " . $this->_sphinx->GetLastError() . ".\n");
            $this->status = 1;
        } else if ($res['total_found'] != 0) {
            if (array_key_exists('matches', $res) && is_array($res['matches'])) {
                $this->_total_found = $res['total_found'];
                foreach ($res['matches'] as $doc_id => $docinfo) {
                    $result = array();
                    $search_product = new SearchProduct($account_dbobj);
                    $search_product->findOne('id=' . $doc_id);

                    $search_product_id = $search_product->getId();
                    if (!empty($search_product_id)) {
                        $result['search_product_id'] = $search_product->getId();
                        $result['search_product_status'] = $search_product->getStatus();
                        $result['product_id'] = $search_product->getProductId();
                        $result['product_status'] = $search_product->getProductStatus();
                        $result['product_name'] = $search_product->getProductName();
                        $result['product_size'] = $search_product->getProductSize();
                        $result['product_quantity'] = $search_product->getProductQuantity();
                        $result['product_price'] = $search_product->getProductPrice();
                        $result['product_shipping'] = $search_product->getProductShipping();
                        $result['product_pinterest_pin_id'] = $search_product->getProductPinterestPinId();
                        $result['product_ext_ref_id'] = $search_product->getProductExtRefId();
                        $result['product_ext_ref_url'] = $search_product->getProductExtRefUrl();
                        $result['product_brand'] = $search_product->getProductBrand();
                        $result['product_misc'] = $search_product->getProductMisc();
                        $result['product_start_date'] = $search_product->getProductStartDate();
                        $result['product_end_date'] = $search_product->getProductEndDate();
                        $result['category'] = $search_product->getCategory();
                        $result['category_description'] = $search_product->getCategoryDescription();
                        $result['pic_ids'] = $search_product->getPicIds();
                        $result['pic_types'] = $search_product->getPicTypes();
                        $result['pic_sources'] = $search_product->getPicSources();
                        $result['pic_urls'] = $search_product->getPicUrls();
                        $store_obj = new Store($account_dbobj, $search_product->getStoreId());
                        $result['store_id'] = $store_obj->getId();
                        $result['store_status'] = $store_obj->getStatus();
                        $result['store_subdomain'] = $store_obj->getSubdomain();
                        $result['store_name'] = $store_obj->getName();
                        $result['store_featured'] = $store_obj->getFeatured();
                        $store_logo = $store_obj->getLogo();
                        if (empty($store_logo))
                            $store_logo = get_store_logo($store_obj->getId());

                        $result['store_logo'] = $store_logo;
                        $result['store_tax'] = $store_obj->getTax();
                        $result['store_shipping'] = $store_obj->getShipping();
                        $result['store_additional_shipping'] = $store_obj->getAdditionalShipping();
                       
                        $converted_pictures = SearchProductsMapper::getConvertedPictures($account_dbobj, $search_product->getId());
                        $result = array_merge($result, $converted_pictures);
                        $result['product_url'] = getStoreUrl($result['store_subdomain']) . "/products/item?id=" . $result['product_id'];
                        $result['store_url'] = getStoreUrl($result['store_subdomain']);

                        $store = new Store($account_dbobj);
                        $store->findOne('id=' . $result['store_id']);
                        if($store->getId()>0){
                            $result['currency'] = $store->getCurrency();
                        }
                        $result['currency_symbol'] = currency_symbol($result['currency']);

                        $this->response['products'][] = $result;
                    }
                }
                $this->response['total_found'] = $this->_total_found;
            }
        }
    }

}
