<?php

namespace TgEmail\Config;

use TgEmail\EmailAddress;
use TgEmail\EmailException;

/**
 * Configures the BCC mail mode.
 * @author ralph
 *        
 */
class BccConfig {

    protected $recipients;
    
    /**
     * Constructor.
     */
    public function __construct($recipients = array()) {
        $this->recipients    = array();
        $this->addRecipients($recipients);
    }
    
    public function getRecipients() {
        return $this->recipients;
    }
    
    public function addRecipients($address, $name = NULL) {
        if (is_array($address)) {
            foreach ($address AS $a) {
                $this->addRecipients($a);
            }
        } else if (is_string($address)) {
            $this->recipients[] = EmailAddress::from($address, $name);
        } else if (is_object($address)) {
            $this->recipients[] = EmailAddress::from($address);
        } else {
            throw new EmailException('Cannot add recipient(s)');
        }
        return $this;
    }
    
    public static function from($config) {
        if (is_array($config)) {
            $config = json_decode(json_encode($config));
        } else if (is_string($config)) {
            $config = json_decode($config);
        }
        if (is_object($config)) {
            $rc = new BccConfig();
            if (isset($config->recipients)) {
                $rc->addRecipients($config->recipients);
            }
            return $rc;
        }
        throw new EmailException('Cannot create BccConfig object from given config');
    }
    
}

