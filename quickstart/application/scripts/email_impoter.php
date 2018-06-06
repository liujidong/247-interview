<?php

require_once('includes.php');

function data_file($f){
    return APPLICATION_PATH . "../../data/" . $f;
}

function import_email_from_mysql_result($file, $src, $tags = NULL){
    global $account_dbobj;
    $data_str = file_get_contents($file);
    $data_emails = explode("\n", $data_str);
    $match = array();
    $reg = "/\s*(.+@.+?)\s+/";
    foreach($data_emails as $email) {
        if(preg_match($reg, $email, $match)){
            $data = array();
            $data['source'] = $src;
            $data['email'] = $match[1];
            if(!empty($tags)){
                $data['tags'] = $tags;
            }
            EmailsMapper::update_email($account_dbobj, $data);
        }
    }
}

function import_email_from_csv($file, $src, $field_map, $tags = NULL){
    global $account_dbobj;
    $file = fopen($file,"r");
    $header = fgetcsv($file);
    while($data = fgetcsv($file)){
        $email_data = array();
        $email_data['source'] = $src;
        if(!empty($tags)){
            $email_data['tags'] = $tags;
        }
        foreach($field_map as $name => $index){
            $email_data[$name] = $data[$index];
        }
        EmailsMapper::update_email($account_dbobj, $email_data);
    }
    fclose($file);
}

function import_email_from_mysql_table($sql, $src, $tags = NULL){
    global $account_dbobj;
    if($res = $account_dbobj->query($sql)){
        while($email_data = $account_dbobj->fetch_assoc($res)) {
            $email_data['source'] = $src;
            if(!empty($tags)){
                $email_data['tags'] = $tags;
            }
            EmailsMapper::update_email($account_dbobj, $email_data);
        }
    }
}

// From Files
$field_map_1 = array(
    'email' => 0, 'first_name' => 1, 'last_name' => 2,    
);
import_email_from_csv(data_file("members_export_8dd293610c.csv"), "shopintoit_lanchrock", $field_map_1);

$field_map_2 = array(
    'email' => 1,
);
import_email_from_csv(data_file("launchrock_export_20120620_PINTICS.csv"), "pintics_launchrock", $field_map_2);

import_email_from_mysql_result(data_file("pintics_ga_account.txt"), "pintics_ga_account", array("pintics_user", "ga_account"));
import_email_from_mysql_result(data_file("pintics_registered_users.txt"), "pintics_registered_user", array("pintics_user"));

// From Database
$sql_paypal_account = "select username as email first_name, last_name from paypal_accounts";
import_email_from_mysql_table($sql_paypal_account, "shopintoit_paypal_account", array('paypal'));
$sql_paypal_account = "select paypal_email as email first_name, last_name from users";
import_email_from_mysql_table($sql_paypal_account, "shopintoit_paypal_account", array('paypal'));

$sql_waiting_merchant = "select username as email from waiting_merchants";
import_email_from_mysql_table($sql_waiting_merchant, "shopintoit_user", array('shopintoit_merchant', "shopintoit_user"));

$sql_shopper = "select username as email first_name, last_name from shoppers";
import_email_from_mysql_table($sql_paypal_account, "shopintoit_user", array('shopintoit_shopper', "shopintoit_user"));

$sql_shopper = "select username as email, first_name, last_name from users where merchant_id = 0";
import_email_from_mysql_table($sql_shopper, "shopintoit_user", array('shopintoit_shopper', "shopintoit_user"));

$sql_merchant = "select username as email, first_name, last_name from users where merchant_id != 0";
import_email_from_mysql_table($sql_merchant, "shopintoit_user", array('shopintoit_merchant', "shopintoit_user"));
