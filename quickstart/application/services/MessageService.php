<?php

class MessageService extends BaseService {
    
    // input: from, to, subject, content, account_dbobj
    public function send() {
        
        $from = $this->params['from'];
        $to = $this->params['to'];
        $subject = $this->params['subject'];
        $content = $this->params['content'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $message = new Message($account_dbobj);
        $message->setFrom($from);
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setContent($content);
        $message->save();
        
        $this->status = 0;
    }
}


