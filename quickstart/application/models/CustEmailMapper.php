<?php 
class CustEmailMapper {
    
    public static function DeleteContactEmail($email_id, $dbobj){
        $sql = 'update cust_emails set status = '.DELETED." where id = '".$email_id."'";
        
        $dbobj->query($sql);
    }
    
    public static function getCustomerIdByEmail($email, $dbobj) {
        $customer_id = 0;
        $sql = sprintf(
                "select c.id
                    from
                    customers c join customers_cust_emails cce on (c.id=cce.customer_id)
                    join cust_emails ce on (ce.id=cce.cust_email_id)
                    where 
                    ce.email='%s' group by ce.email", $dbobj->escape($email)
                );
        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $customer_id = $record['id'];
            }
        }
        return $customer_id;
    }
    
    public static function getAllCustomerEmails($dbobj) {
        $emails = array();
        $sql = "select * from cust_emails order by id";
        $count = 0;
        if($res = $dbobj->query($sql)) {
            
            while($record = $dbobj->fetch_assoc($res)) {
                $emails[] = $record['email'];
            }
        }
        return $emails;
    }
    
}