<?php


require_once (__DIR__."/../models/DBObj.php");

$dbobj = new DBObj('10.252.53.30', 'information_schema', 'root', '');

$sql = "select SCHEMA_NAME from SCHEMATA where SCHEMA_NAME like 'store_%'";
//$alter_sql = "alter table pinterest_pages modify column html MEDIUMTEXT NOT NULL default ''";
//
//if($res = $dbobj->query($sql)) {
//    while($record=$dbobj->fetch_assoc($res)) {
//        $dbname = $record['SCHEMA_NAME'];
//        echo "$dbname\n";
//        $pinterest_dbobj = new DBObj('10.252.53.30', "$dbname", 'root', '');
//        if($pinterest_dbobj->query($alter_sql)) {
//            echo "SUCCESS\n";
//        } else {
//            echo "ERROR\n";
//        }
//    }
//}