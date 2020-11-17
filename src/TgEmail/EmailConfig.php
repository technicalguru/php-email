<?php

namespace TgEmail;

use TgEmail\Config\SmtpConfig;
use TgEmail\Config\RerouteConfig;
use TgEmail\Config\BccConfig;

/**
 * Configuration for the Mail Queue.
 * @author ralph
 *        
 */
class EmailConfig {

    private $timezone;
    private $mailMode;
    private $smtpConfig;
    private $rerouteConfig;
    private $bccConfig;
    private $debugAddress;
    private $defaultSender;
    private $subjectPrefix;
    
    /**
     * Constructor.
     */
    public function __construct(SmtpConfig $smtpConfig = NULL, RerouteConfig $rerouteConfig = NULL, BccConfig $bccConfig = NULL) {
        $this->timezone      = 'UTC';
        $this->mailMode      = EmailQueue::DEFAULT;
        $this->setSmtpConfig($smtpConfig);
        $this->setRerouteConfig($rerouteConfig);
        $this->setBccConfig($bccConfig);
        $this->debugAddress  = array();
        $this->defaultSender = NULL;
        $this->subjectPrefix = NULL;
    }
    
    public function getTimezone() {
        return $this->timezone;
    }
    
    public function setTimezone($tz) {
        $this->timezone = $tz;
        return $this;
    }
    
    public function getMailMode() {
        return $this->mailMode;
    }
    
    public function setMailMode($mailMode, $config = NULL) {
        if ($mailMode == EmailQueue::REROUTE) {
            if ($config != NULL) {
                $this->setRerouteConfig($config);
            }
            if ($this->getRerouteConfig() == NULL) {
                throw new EmailException('No RerouteConfig available. Set the config along with the mailMode.');
            }
        } else if ($mailMode == EmailQueue::BCC) {
            if ($config != NULL) {
                $this->setBccConfig($config);
            }
            if ($this->getBccConfig() == NULL) {
                throw new EmailException('No BccConfig available. Set the config along with the mailMode.');
            }
        }
        $this->mailMode = $mailMode;
        return $this;
    }
    
    public function getSmtpConfig() {
        return $this->smtpConfig;
    }
    
    public function setSmtpConfig($config) {
        if (($config == NULL) || is_a($config, 'TgEmail\\Config\\SmtpConfig')) {
            $this->smtpConfig = $config;
            return $this;
        }
        throw new EmailException('Not a SmtpConfig object.');
    }
    
    public function getRerouteConfig() {
        return $this->rerouteConfig;
    }
    
    public function setRerouteConfig($config) {
        if (($config == NULL) || is_a($config, 'TgEmail\\Config\\RerouteConfig')) {
            $this->rerouteConfig = $config;
            return $this;
        }
        throw new EmailException('Not a RerouteConfig object.');
    }
    
    public function getBccConfig() {
        return $this->bccConfig;
    }
    
    public function setBccConfig($config) {
        if (($config == NULL) || is_a($config, 'TgEmail\\Config\\BccConfig')) {
            $this->bccConfig = $config;
            return $this;
        }
        throw new EmailException('Not a BccConfig object.');
    }
    
    public function getDefaultSender() {
        return $this->defaultSender;
    }
    
    public function setDefaultSender($email, $name = NULL) {
        $this->defaultSender = EmailAddress::from($email, $name);
        return $this;
    }
    
    public function getDebugAddress() {
        return $this->debugAddress;
    }
    
    public function addDebugAddress($address, $name = NULL) {
        if (is_array($address)) {
            foreach ($address AS $a) {
                $this->addDebugAddress($a);
            }
        } else if (is_string($address)) {
            $this->debugAddress[] = EmailAddress::from($address, $name);
        } else if (is_object($address)) {
            $this->debugAddress[] = EmailAddress::from($address);
        } else {
            throw new EmailException('Cannot add debugging recipient(s)');
        }
        return $this;        
    }
    
    public function getSubjectPrefix() {
        if ($this->subjectPrefix == NULL) return '';
        return $this->subjectPrefix;
    }
    
    public function setSubjectPrefix($s) {
        $this->subjectPrefix = $s;
    }
    
    public static function from($config) {
        if (is_array($config)) {
            $config = json_decode(json_encode($config));
        } else if (is_string($config)) {
            $config = json_decode($config);
        }
        if (is_object($config)) {
            $rc = new EmailConfig();
            if (isset($config->timezone)) {
                $rc->setTimezone($config->timezone);
            }
            if (isset($config->smtpConfig)) {
                $rc->setSmtpConfig(SmtpConfig::from($config->smtpConfig));
            }
            if (isset($config->rerouteConfig)) {
                $rc->setRerouteConfig(RerouteConfig::from($config->rerouteConfig));
            }
            if (isset($config->bccConfig)) {
                $rc->setBccConfig(BccConfig::from($config->bccConfig));
            }
            if (isset($config->mailMode)) {
                $rc->setMailMode($config->mailMode);
            }
            if (isset($config->debugAddress)) {
                $rc->addDebugAddress($config->debugAddress);
            }
            if (isset($config->defaultSender)) {
                $rc->setDefaultSender($config->defaultSender);
            }
            if (isset($config->subjectPrefix)) {
                $rc->setSubjectPrefix($config->subjectPrefix);
            }
            return $rc;
        }
        throw new EmailException('Cannot create EmailConfig object from given config');
    }
    
}

