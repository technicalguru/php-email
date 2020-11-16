<?php

namespace TgEmail;

use TgUtils\Date;

/**
 * An Email object to be sent or persisted.
 * @author ralph
 *        
 */
class Email {

    public const PENDING    = 'pending';
    public const PROCESSING = 'processing';
    public const SENT       = 'sent';
    public const FAILED     = 'failed';
    
    public $uid;
    public $status;
    public $failed_attempts;
    public $sent_time;
    public $queued_time;
    
    public $sender;
    public $recipients;
    public $reply_to;
    public $subject;
    public $body;
    public $attachments;
    
    /**
     * Default Constructor.
     */
    public function __construct() {
    }
    
    public function getSender() {
        if ($this->sender != NULL) {
            if (!is_object($this->sender)) {
                $this->sender = MailAddress::from($this->sender);
            }
        }
        return $this->sender;
    }
    
    public function setSender($email, $name) {
        $this->sender = MailAddress::from($email, $name);
    }
    
    public function getReplyTo() {
        if ($this->reply_to != NULL) {
            if (!is_object($this->reply_to)) {
                $this->reply_to = MailAddress::from($this->reply_to);
            }
        }
        return $this->sender;
    }
    
    public function setReplyTo($email, $name) {
        $this->reply_to = MailAddress::from($email, $name);
    }
    
    protected function getRecipients() {
        if ($this->recipients == NULL) {
            $this->recipients = new \stdClass;
            $this->recipients->to  = array();
            $this->recipients->cc  = array();
            $this->recipients->bcc = array();
        }
        if (!is_object($this->recipients)) {
            $this->recipients = json_decode($this->recipients);
            $this->recipients->to  = $this->convertToAddresses($this->recipients->to);
            $this->recipients->cc  = $this->convertToAddresses($this->recipients->cc);
            $this->recipients->bcc = $this->convertToAddresses($this->recipients->bcc);
        }
        return $this->recipients;
    }
    
    protected function convertToAddresses($arr) {
        $rc = array();
        foreach ($arr AS $address) {
            $rc[] = MailAddress::from($address);
        }
        return $rc;
    }
    
    public function getTo() {
        return getRecipients()->to;
    }
    
    public function addTo($address, $name = NULL) {
        if (is_array($address)) {
            foreach ($address AS $a) {
                $this->addTo($a);
            }
        } else if (is_string($address)) {
            getRecipients()->to[] = MailAddress::from($address, $name);
        } else if (is_a($address, 'TgEmail\\MailAddress')) {
            getRecipients()->to[] = $address;
        } else {
            throw new MailException('Cannot add TO recipient(s)');
        }
    }
            
    public function getCc() {
        return getRecipients()->cc;
    }
    
    public function addCc($address, $name = NULL) {
        if (is_array($address)) {
            foreach ($address AS $a) {
                $this->addCc($a);
            }
        } else if (is_string($address)) {
            getRecipients()->cc[] = MailAddress::from($address, $name);
        } else if (is_a($address, 'TgEmail\\MailAddress')) {
            getRecipients()->cc[] = $address;
        } else {
            throw new MailException('Cannot add CC recipient(s)');
        }
    }
            
    public function getBcc() {
        return getRecipients()->bcc;
    }
    
    public function addBcc($address, $name = NULL) {
        if (is_array($address)) {
            foreach ($address AS $a) {
                $this->addBcc($a);
            }
        } else if (is_string($address)) {
            getRecipients()->bcc[] = MailAddress::from($address, $name);
        } else if (is_a($address, 'TgEmail\\MailAddress')) {
            getRecipients()->bcc[] = $address;
        } else {
            throw new MailException('Cannot add BCC recipient(s)');
        }
    }
         
    public function getSubject() {
        return $this->subject;
    }
    
    public function setSubject($s) {
        $this->subject = $s;
    }
    
    public function getBody($type = 'text') {
        if (($this->body != NULL) && is_string($this->body)) {
            $this->body = json_decode($this->body);
        } else {
            $this->body = new \stdClass;
        }
        if (isset($this->body->$type)) {
            return $this->body->$type;
        }
        return NULL;
    }
    
    public function setBody($type = 'text', $body) {
        if (($this->body != NULL) && is_string($this->body)) {
            $this->body = json_decode($this->body);
        } else {
            $this->body = new \stdClass;
        }
        $this->body->$type = $body;
    }
    
    public function getAttachments() {
        if ($this->attachments == NULL) {
            $this->attachments = array();
        } else if (is_string($this->attachments)) {
            $arr = json_decode($this->attachments);
            $this->attachments = array();
            foreach ($arr AS $a) {
                $this->attachments[] = Attachment::from($a);
            }
        }
        return $this->attachments;
    }
    
    public function addAttachment(Attachment $a) {
        $this->getAttachments();
        $this->attachments[] = $a;
    }

    public function addAttachments(array $arr) {
        $this->getAttachments();
        foreach ($arr AS $a) {
            $this->attachments[] = $a;
        }
    }
    
    public function getSentTime() {
        if (($this->sent_time != NULL) && is_string($this->sent_time)) {
            $this->sent_time = new Date($this->sent_time, 'Europe/Berlin');
        }
        return $this->sent_time;
    }
    
    public function getQueuedTime() {
        if (($this->queued_time != NULL) && is_string($this->queued_time)) {
            $this->queued_time = new Date($this->queued_time, 'Europe/Berlin');
        }
        return $this->queued_time;
    }
    
    public function getLogString() {
        $rc  =   'TO='.$this->stringify($this->getRecipients()->to);
        $rc .=  ' CC='.$this->stringify($this->getRecipients()->cc);
        $rc .= ' BCC='.$this->stringify($this->getRecipients()->bcc);
        return $rc;
    }
    
    public function stringify($addresses) {
        $rc = array();
        foreach ($addresses AS $a) {
            $rc[] = $a->__toString();
        }
        return implode(',', $rc);
    }
}

