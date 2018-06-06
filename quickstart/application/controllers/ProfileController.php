<?php

class ProfileController extends BaseController {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {

        global $shopinterest_config, $site_domain, $redis;
        global $currencies;

        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $keys = array(
            "store:$store_id:name",
            "store:$store_id:shipping",
            "store:$store_id:additional_shipping",
            "store:$store_id:tax",
            "store:$store_id:external_website",
            "store:$store_id:return_policy",
            "store:$store_id:status",
            "store:$store_id:logo",
            "store:$store_id:converted_logo",
            "store:$store_id:tags",
            "store:$store_id:description",
            "store:$store_id:optin_salesnetwork",
            "store:$store_id:subdomain",
            "store:$store_id:country",
            "store:$store_id:currency",
            "merchant:$merchant_id:pinterest_account_id"
        );

        $values = $redis->mget2($keys);

        // initialize the view object using the redis caching data
        $this->view->store_name = $values["store:$store_id:name"] ? $values["store:$store_id:name"] : '';
        $this->view->store_shipping = $values["store:$store_id:shipping"] ? $values["store:$store_id:shipping"] : 0;
        $this->view->store_additional_shipping = $values["store:$store_id:additional_shipping"] ? $values["store:$store_id:additional_shipping"] : 0;
        $this->view->store_tax = $values["store:$store_id:tax"] ? $values["store:$store_id:tax"] : 0 ;
        $this->view->store_external_website = $values["store:$store_id:external_website"] ? $values["store:$store_id:external_website"] : '';
        $this->view->store_return_policy = $values["store:$store_id:return_policy"] ? $values["store:$store_id:return_policy"] : '';
        $this->view->site_domain = $site_domain;
        $this->view->pinterest_username = '';
        if($pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")) {
            $this->view->pinterest_username = $redis->get("pinterest_account:$pinterest_account_id:username")?
                    $redis->get("pinterest_account:$pinterest_account_id:username"):'';
        }

        $this->view->store_status = $values["store:$store_id:status"] ? $values["store:$store_id:status"] : '';
        $this->view->store_tags = $values["store:$store_id:tags"] ? $values["store:$store_id:tags"] : '' ;
        $this->view->store_description = $values["store:$store_id:description"] ? $values["store:$store_id:description"] : '';
        $this->view->store_optin_salesnetwork = $values["store:$store_id:optin_salesnetwork"] ? $values["store:$store_id:optin_salesnetwork"] : 0;
        $this->view->store_subdomain = $values["store:$store_id:subdomain"] ?  : '';
        $this->view->store_country = $values["store:$store_id:country"] ?  : 'US';
        $this->view->store_currency = $values["store:$store_id:currency"] ?  : 'USD';

        $store_logo = $values["store:$store_id:converted_logo"];
        if(empty($store_logo)) {
            $store_logo = $values["store:$store_id:logo"] ? $values["store:$store_id:logo"] : $shopinterest_config->store->logo->default;
        }
        $this->view->store_logo = $store_logo;

        $this->view->countries = CountriesMapper::getAllCountryInfo($this->account_dbobj);
        $this->view->currencies = $currencies;

        if(!empty($_REQUEST['submit'])) {

            $prev_pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")?
                $redis->get("merchant:$merchant_id:pinterest_account_id"):0;
            $prev_store_tags = $redis->get("store:$store_id:tags")?$redis->get("store:$store_id:tags"):'';

            // input: merchant_id, pinterest_account_id, pinterest_username, store_id, store_tags,
            // store_name, store_subdomain, store_shipping, store_additional_shipping,
            // store_tax, store_external_website, store_description,
            // store_return_policy, store_optin_salesnetwork, account_dbobj
            $service = AccountsService::getInstance();
            $service->setMethod('update_merchant_profile');
            $service->setParams(array(
                'merchant_id' => $merchant_id,
                'store_id' => $store_id,
                'store_name' => $_REQUEST['store_name'],
                'store_subdomain' => $_REQUEST['store_subdomain'],
                'store_country' => $_REQUEST['store_country'],
                'store_currency' => $_REQUEST['store_currency'],
                'pinterest_account_id' => $prev_pinterest_account_id,
                'pinterest_username' => $_REQUEST['pinterest_username'],
                'store_tax' => $_REQUEST['store_tax'],
                'store_shipping' => $_REQUEST['store_shipping'],
                'store_additional_shipping' => $_REQUEST['store_additional_shipping'],
                'prev_store_tags' => $prev_store_tags,
                'store_tags' => $_REQUEST['store_tags'],
                'store_external_website'=> $_REQUEST['store_external_website'],
                'store_description'=> $_REQUEST['store_description'],
                'store_return_policy'=> $_REQUEST['store_return_policy'],
                'account_dbobj' => $this->account_dbobj
            ));

            $service->call();
            $status = $service->getStatus();
            $this->view->errnos = $service->getErrnos();
            $response = $service->getResponse();
            // overwrite some of the view data using the data returned from service
            $this->view->store_name = $response['store_name'];
            $this->view->store_shipping = $response['store_shipping'];
            $this->view->store_additional_shipping = $response['store_additional_shipping'];
            $this->view->store_tax = $response['store_tax'];
            $this->view->store_external_website = $response['store_external_website'];
            $this->view->store_tags = $response['store_tags'];
            $this->view->store_description = $response['store_description'];
            $this->view->store_return_policy = $response['store_return_policy'];
            $this->view->store_subdomain = $response['store_subdomain'];
            $this->view->store_country = $response['store_country'];
            $this->view->store_currency = $response['store_currency'];

            if($pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")) {
                $this->view->pinterest_username = $redis->get("pinterest_account:$pinterest_account_id:username")?
                    $redis->get("pinterest_account:$pinterest_account_id:username"):'';
            }
        }
    }
}
