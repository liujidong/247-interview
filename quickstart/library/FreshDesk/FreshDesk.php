<?php
class FreshDesk {
    private $_username = 'xxx@gmail.com';
    private $_passwd = 'xxx';
    public static $submit_url = "http://shopinterest.freshdesk.com/helpdesk/tickets.xml";
    public $errors = array();
    
    public function __construct($username = null, $passwd = null) {
        if (! empty ( $username ))
            $this->_username = $username;
        if (! empty ( $passwd ))
            $this->_passwd = $passwd;
    }
    
    public function createTicket($data) {
        
        if(empty($data['email'])){
            $this->errors[] = $GLOBALS['errors'][INVALID_EMAIL]['msg'];
        }
        
        if(empty($data['subject'])){
            $this->errors[] = $GLOBALS['errors'][INVALID_TICKET_SUBJECT]['msg'];
        }
        
        if(empty($data['description'])){
            $this->errors[] = $GLOBALS['errors'][INVALID_TICKET_DESCRIPTION]['msg'];
        }
        
        
        if(empty($this->errors)){
           
            $curl = curl_init ();
            
            $xmldata = $this->_createXML ( $data );
            
            curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt ( $curl, CURLOPT_USERPWD, $this->_username . ":" . $this->_passwd );
            curl_setopt ( $curl, CURLOPT_HTTPHEADER, array ('Content-Type: application/xml' ) );
            
            //curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt ( $curl, CURLOPT_POST, true );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $xmldata );
            curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)" );
            curl_setopt ( $curl, CURLOPT_URL, self::$submit_url );
            
            try {
                $res = curl_exec ( $curl );
            } catch ( Exception $e ) {
                $res = false;
            }
        }
        else
            $res = false;
        return $res;
    }
    
    private function _createXML($data) {
        //create the xml document
        $xmlDoc = new DOMDocument ();
        
        //create the root element
        $root = $xmlDoc->appendChild ( $xmlDoc->createElement ( "helpdesk_ticket" ) );
        
        foreach ( $data as $key => $val ) {
            $root->appendChild ( $xmlDoc->createElement ( $key, $val ) );
        }
        //make the output pretty
        $xmlDoc->formatOutput = true;
        
        return $xmlDoc->saveXML ();
    }
}