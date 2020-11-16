<?php

namespace TgEmail\Config;

use TgUtils\Auth\CredentialsProvider;
use TgUtils\Auth\DefaultCredentialsProvider;

/**
 * Configures the PHPMailer SMTP options.
 * @author ralph
 *        
 */
class SmtpConfig {

    private $host;
    private $port;
    private $debugLevel;
    private $auth;
    private $secureOption;
    private $credentialsProvider;
    private $charset;
    
    /**
     * Constructor.
     */
    public function __construct($host, $port, $auth, $username, $password, $secureOption, $charset) {
        $this->host         = $host;
        $this->port         = $port;
        $this->auth         = $auth;
        $this->setCredentials($username, $password);
        $this->secureOption = $secureOption;
        $this->charset      = $charset;
    }
    
    public function getHost() {
        return $this->host;
    }
    
    public function setHost($host) {
        $this->host = $host;
        return $this;
    }
    
    public function getPort() {
        return $this->port;
    }
    
    public function setPort($port) {
        $this->port = $port;
        return $this;
    }
    
    public function getDebugLevel() {
        return $this->debugLevel;
    }
    
    public function setDebugLevel($level) {
        $this->debugLevel = $level;
        return $this;
    }
    
    public function isAuth() {
        return $this->auth;
    }
    
    public function setAuth($auth) {
        $this->auth = $auth;
        return $this;
    }
    
    public function getSecureOption() {
        return $this->secureOption;
    }
    
    public function setSecureOption($option) {
        $this->secureOption = $option;
        return $this;
    }
    
    public function getUsername() {
        return $this->getCredentialsProvider()->getUsername();
    }
    
    public function getPassword() {
        return $this->getCredentialsProvider()->getPassword();
    }
    
    public function setCredentials($username, $password) {
        $this->setCredentialsProvider(new DefaultCredentialsProvider($username, $password));
        return $this;
    }
    
    public function getCredentialsProvider() {
        return $this->credentialsProvider;
    }
    
    public function setCredentialsProvider(CredentialsProvider $provider) {
        $this->credentialsProvider = $provider;
        return $this;
    }
    
    public function getCharset() {
        return $this->charset;
    }
    
    public function setCharset($charset) {
        $this->charset;
        return $this;
    }
}

