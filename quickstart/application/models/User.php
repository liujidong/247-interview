<?php

class User extends BaseModel {

    public function setUsername($username) {
        if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->_username = $username;
            return true;
        } else {
            return false;
        }
    }

    public function setPassword($password) {
        if(empty($password) || $password === DUMMY_PASSWORD) return true;
        if(validate($password, 'password')) {
            $this->_password = md5($password);
            return true;
        } else {
            return false;
        }
    }

    public function setBirthDate($birth_day, $birth_month, $birth_year) {
        if(empty($birth_day) || empty($birth_month) || strlen($birth_day) !== 2 || strlen($birth_month) !== 2) {
            return false;
        }
        if(empty($birth_year)) {
            $birth_year = DEFAULT_BIRTH_YEAR;
        }
        $date = $birth_year.'-'.$birth_month.'-'.$birth_day;
        if(!strtotime($date)) {
            return false;
        }
        $this->setBirthDay($birth_day);
        $this->setBirthMonth($birth_month);
        $this->setBirthYear($birth_year);
        return true;
    }

    public function setGender($gender) {
        $gender = trim($gender);
        if($gender !== 'male' && $gender !== 'female') {
            return false;
        }
        $this->_gender = $gender;
        return true;
    }

    public function setFirstName($first_name) {
        $first_name = trim($first_name);
        if(empty($first_name)) {
            return false;
        }
        $this->_first_name = $first_name;
        return true;
    }

    public function setLastName($last_name) {
        $last_name = trim($last_name);
        if(empty($last_name)) {
            return false;
        }
        $this->_last_name = $last_name;
        return true;
    }


    public static function format($data, $ck){
        global $dbconfig;

        $data['has_store']= !empty($data['store_id']);
        if(empty($data['credit_card_ids'])){
            $data['credit_card_ids'] = array();
        } else {
            $data['credit_card_ids'] = explode(",", $data['credit_card_ids']);
        }
        if(empty($data['aid'])){
            $data['aid'] = 'assoc' . $data['id'];
        }
        $data['credit_card_on_file'] = empty($data['credit_card_ids']) ? 'N' : 'Y';
        if($data['has_store']){
            $data['store'] = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=" . $data['store_id']);
        }

        switch ($data['status']) {
        case 0:
            $status = "Created";
            break;
        case 1:
            $status = "Pending";
            break;
        case 2:
            $status = "Active";
            break;
        case 3:
            $status = "Blocked";
            break;
        default:
            $status = $data['status'];
        }
        $data['literal_status'] = $status;

        return $data;
    }
}
