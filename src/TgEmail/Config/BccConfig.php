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

    private $recipients;
    
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
        } else if (is_a($address, 'TgEmail\\MailAddress')) {
            $this->recipients[] = $address;
        } else {
            throw new EmailException('Cannot add recipient(s)');
        }
        return $this;
    }
}

