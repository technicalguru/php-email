<?php

namespace TgEmail\Config;

use TgEmail\EmailAddress;
use TgEmail\EmailException;

/**
 * Configures the Reroute Mail Mode.
 * @author ralph
 *        
 */
class RerouteConfig {

    protected $subjectPrefix;
    protected $recipients;
    
    /**
     * Constructor.
     */
    public function __construct($subjectPrefix = '', $recipients = array()) {
        $this->subjectPrefix = $subjectPrefix;
        $this->recipients    = array();
        $this->addRecipients($recipients);
    }
    
    public function getSubjectPrefix() {
        return $this->subjectPrefix;
    }
    
    public function setSubjectPrefix($prefix) {
        $this->subjectPrefix = $prefix;
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
            $rc = new RerouteConfig();
            if (isset($config->recipients)) {
                $rc->addRecipients($config->recipients);
            }
            if (isset($config->subjectPrefix)) {
                $rc->setSubjectPrefix($config->subjectPrefix);
            }
            return $rc;
        }
        throw new EmailException('Cannot create RerouteConfig object from given config');
    }
    
}

