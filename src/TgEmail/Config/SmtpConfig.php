<?php

namespace TgEmail\Config;

use TgUtils\Auth\CredentialsProvider;
use TgUtils\Auth\DefaultCredentialsProvider;
use TgEmail\EmailException;

/**
 * Configures the PHPMailer SMTP options.
 * @author ralph
 *        
 */
class SmtpConfig {

    protected $host;
    protected $port;
    protected $debugLevel;
    protected $auth;
    protected $secureOption;
    protected $credentialsProvider;
    protected $charset;
    
    /**
     * Constructor.
     */
    public function __construct($host = NULL, $port = 0, $auth = FALSE, $username = NULL, $password = NULL, $secureOption = NULL, $charset = 'UTF-8') {
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
        $this->charset = $charset;
        return $this;
    }
    
    public static function from($config) {
        if (is_array($config)) {
            $config = json_decode(json_encode($config));
        } else if (is_string($config)) {
            $config = json_decode($config);
        }
        if (is_object($config)) {
            $rc = new SmtpConfig();
            if (isset($config->host)) {
                $rc->setHost($config->host);
            }
            if (isset($config->port)) {
                $rc->setPort($config->port);
            }
            if (isset($config->debugLevel)) {
                $rc->setDebugLevel($config->debugLevel);
            }
            if (isset($config->auth)) {
                $rc->setAuth($config->auth);
            }
            if (isset($config->credentialsProvider)) {
                if (is_a($config->credentialsProvider, 'TgUtils\Auth\CredentialsProvider')) {
                    $rc->setCredentialsProvider($config->credentialsProvider);
                } else if (is_object($config->credentialsProvider)) {
                    $username = NULL;
                    $password = NULL;
                    if (isset($config->credentialsProvider->username)) {
                        $username = $config->credentialsProvider->username;
                    }
                    if (isset($config->credentialsProvider->user)) {
                        $username = $config->credentialsProvider->user;
                    }
                    if (isset($config->credentialsProvider->password)) {
                        $password = $config->credentialsProvider->password;
                    }
                    if (isset($config->credentialsProvider->passwd)) {
                        $password = $config->credentialsProvider->passwd;
                    }
                    if (isset($config->credentialsProvider->pass)) {
                        $password = $config->credentialsProvider->pass;
                    }
                    $rc->setCredentials($username, $password);
                } else {
                    throw new EmailException('Cannot configure credentialsProvider from given config');
                }
            } else if (isset($config->credentials)) {
                $username = NULL;
                $password = NULL;
                if (isset($config->credentials->username)) {
                    $username = $config->credentials->username;
                }
                if (isset($config->credentials->user)) {
                    $username = $config->credentials->user;
                }
                if (isset($config->credentials->password)) {
                    $password = $config->credentials->password;
                }
                if (isset($config->credentials->passwd)) {
                    $password = $config->credentials->passwd;
                }
                if (isset($config->credentials->pass)) {
                    $password = $config->credentials->pass;
                }
                $rc->setCredentials($username, $password);
            }
            if (isset($config->secureOption)) {
                $rc->setSecureOption($config->secureOption);
            }
            if (isset($config->charset)) {
                $rc->setCharset($config->charset);
            }
            return $rc;
        }
        throw new EmailException('Cannot create SmtpConfig object from given config');
    }
    
}

