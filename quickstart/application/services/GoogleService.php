<?php

class GoogleService extends BaseService {
    
    public function get_analytics_data() {
        
        global $googleconfig;

        $client_id = $googleconfig->api->client_id;
        $account_name = $googleconfig->api->account_name;
        $application_name = $googleconfig->api->application_name;
        $profile_id = $googleconfig->api->profile_id;    
        $analytics_scope = $googleconfig->api->analytics_scope;
        $key_file_path = APPLICATION_PATH.$googleconfig->api->key_file;
        $key = file_get_contents($key_file_path);
        $max_results = 999;
        $start_index = 1;
        
        $metics = array(
            'ga:visitors', 
            'ga:pageviews',
            'ga:avgTimeOnPage',
            'ga:visitBounceRate'
        );
        $optParams = array(
            'dimensions' => 'ga:pagePath',
            'max-results' => $max_results,
            'start-index' => $start_index
        );

        $day_from = $this->params['day_from'];
        $day_to = $this->params['day_to'];
        $metics_options = isset($this->params['metics']) ? $this->params['metics'] : array(); 
        $params_options = isset($this->params['opt_params']) ? $this->params['opt_params'] : array(); 
        
        $metics = array_merge($metics, $metics_options);
        $optParams = array_merge($optParams, $params_options);

        $client = new Google_Client();
        $client->setApplicationName($application_name);
            $client->setAssertionCredentials(new Google_AssertionCredentials(
            $account_name,
            $analytics_scope,
            $key)
        );
        
        $client->setUseObjects(true);

        $service = new Google_AnalyticsService($client);
        $data_rows = array();
        
        do{
            $data_obj = $service->data_ga->get(
                    "ga:$profile_id", 
                    $day_from,
                    $day_to,
                    implode(',', $metics),
                    $optParams
            ); 
            $temp_array = $data_obj->getrows() ?: array();
            $data_rows = array_merge($data_rows, $temp_array);
            $nextLink = $data_obj->getnextLink();
            $optParams['start-index'] += $max_results;
        } while (!empty($nextLink));

        $this->response = $data_rows;
    }
}