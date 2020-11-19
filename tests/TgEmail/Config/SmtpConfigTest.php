<?php declare(strict_types=1);

namespace TgEmail\Config;

use PHPUnit\Framework\TestCase;

use TgEmail\EmailAddress;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Tests the SmtpConfig.
 * 
 * @author ralph
 *        
 */
class SmtpConfigTest extends TestCase {

    public function testSetHost(): void {
        $config = new SmtpConfig();
        $config->setHost('www.example.com');
        $this->assertEquals('www.example.com', $config->getHost());
    }
    
    public function testSetPort(): void {
        $config = new SmtpConfig();
        $config->setPort(587);
        $this->assertEquals(587, $config->getPort());
    }
    
    public function testSetDebugLevel(): void {
        $config = new SmtpConfig();
        $config->setDebuglevel(SMTP::DEBUG_SERVER);
        $this->assertEquals(SMTP::DEBUG_SERVER, $config->getDebuglevel());
    }
    
    
    public function testSetAuth(): void {
        $config = new SmtpConfig();
        $config->setAuth(TRUE);
        $this->assertTrue($config->isAuth());
    }
    
    
    public function testSetCredentials(): void {
        $config = new SmtpConfig();
        $config->setCredentials('username', 'password');
        $this->assertEquals('username', $config->getUsername());
        $this->assertEquals('password', $config->getPassword());
    }
    
    
    public function testSetSecureOption(): void {
        $config = new SmtpConfig();
        $config->setSecureOption(PHPMailer::ENCRYPTION_STARTTLS);
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $config->getSecureOption());
    }
    
    
    public function testSetCharset(): void {
        $config = new SmtpConfig();
        $config->setCharset('utf8');
        $this->assertEquals('utf8', $config->getCharset());
    }
    
    public function testFromWithArray(): void {
        $origConfig = array(
            'host'         => 'www.example.com',
            'port'         => 587,
            'debugLevel'   => SMTP::DEBUG_SERVER,
            'auth'         => TRUE,
            'credentials'  => array(
                'user' => 'username',
                'pass' => 'password',
            ),
            'secureOption' => PHPMailer::ENCRYPTION_STARTTLS,
            'charset'      => 'utf8',
        );
        
        $config = SmtpConfig::from($origConfig);
        $this->assertEquals('www.example.com',              $config->getHost());
        $this->assertEquals(587,                            $config->getPort());
        $this->assertEquals(SMTP::DEBUG_SERVER,             $config->getDebuglevel());
        $this->assertTrue($config->isAuth());
        $this->assertEquals('username',                     $config->getUsername());
        $this->assertEquals('password',                     $config->getPassword());
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $config->getSecureOption());
        $this->assertEquals('utf8',                         $config->getCharset());
    }
    
    public function testFromWithString(): void {
        $origConfig = self::getJsonTestString();
        
        $config = SmtpConfig::from($origConfig);
        $this->assertEquals('www.example.com',              $config->getHost());
        $this->assertEquals(587,                            $config->getPort());
        $this->assertEquals(SMTP::DEBUG_SERVER,             $config->getDebuglevel());
        $this->assertTrue($config->isAuth());
        $this->assertEquals('username',                     $config->getUsername());
        $this->assertEquals('password',                     $config->getPassword());
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $config->getSecureOption());
        $this->assertEquals('utf8',                         $config->getCharset());
    }
    
    public function testFromWithObject(): void {
        $origConfig = json_decode(self::getJsonTestString());
        
        $config = SmtpConfig::from($origConfig);
        $this->assertEquals('www.example.com',              $config->getHost());
        $this->assertEquals(587,                            $config->getPort());
        $this->assertEquals(SMTP::DEBUG_SERVER,             $config->getDebuglevel());
        $this->assertTrue($config->isAuth());
        $this->assertEquals('username',                     $config->getUsername());
        $this->assertEquals('password',                     $config->getPassword());
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $config->getSecureOption());
        $this->assertEquals('utf8',                         $config->getCharset());
    }
    
    public static function getJsonTestString() {
        return '{"host":"www.example.com","port":587,"debugLevel":2,"auth":true,"credentials":{"user":"username","pass":"password"},"secureOption":"tls","charset":"utf8"}';
    }
}

