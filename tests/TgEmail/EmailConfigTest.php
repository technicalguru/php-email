<?php declare(strict_types=1);

namespace TgEmail;

use PHPUnit\Framework\TestCase;

use TgEmail\Config\BccConfig;
use TgEmail\Config\BccConfigTest;
use TgEmail\Config\RerouteConfig;
use TgEmail\Config\RerouteConfigTest;
use TgEmail\Config\SmtpConfig;
use TgEmail\Config\SmtpConfigTest;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Tests the EmailConfig.
 * 
 * @author ralph
 *        
 */
class EmailConfigTest extends TestCase {

    
    public function testSetTimezone(): void {
        $config = new EmailConfig();
        $config->setTimezone('Europe/Berlin');
        $this->assertEquals('Europe/Berlin', $config->getTimezone());
    }
    
    public function testSetMailMode(): void {
        $config = new EmailConfig();
        $config->setMailMode(EmailQueue::BLOCK);
        $this->assertEquals(EmailQueue::BLOCK, $config->getMailMode());
    }
    
    public function testSetSmtpConfig(): void {
        $config = new EmailConfig();
        $smtpConfig = SmtpConfig::from(SmtpConfigTest::getJsonTestString());
        $config->setSmtpConfig($smtpConfig);
        $this->assertEquals($smtpConfig, $config->getSmtpConfig());
    }
    
    
    public function testSetRerouteConfig(): void {
        $config = new EmailConfig();
        $rerouteConfig = RerouteConfig::from(RerouteConfigTest::getJsonTestString());
        $config->setRerouteConfig($rerouteConfig);
        $this->assertEquals($rerouteConfig, $config->getRerouteConfig());
    }
    
    public function testSetBccConfig(): void {
        $config = new EmailConfig();
        $bccConfig = BccConfig::from(BccConfigTest::getJsonTestString());
        $config->setBccConfig($bccConfig);
        $this->assertEquals($bccConfig, $config->getBccConfig());
    }
       
    public function testAddAddDebugAddressWithString(): void {
        $config = new EmailConfig();
        $config->addDebugAddress('John Doe <john.doe@example.com');
        
        $recipients = $config->getDebugAddress();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddAddDebugAddressWithObject(): void {
        $config = new EmailConfig();
        $config->addDebugAddress(EmailAddress::from('John Doe  <john.doe@example.com'));
        
        $recipients = $config->getDebugAddress();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddAddDebugAddressWithArray(): void {
        $config = new EmailConfig();
        $config->addDebugAddress(array(
            EmailAddress::from('John Doe  <john.doe@example.com'),
            EmailAddress::from('Jane Doe  <jane.doe@example.com'),
        ));
        
        $recipients = $config->getDebugAddress();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testSetDefaultSender(): void {
        $config = new EmailConfig();
        $config->setDefaultSender(EmailAddress::from('John Doe <john.doe@example.com>'));
        $this->assertEquals('John Doe <john.doe@example.com>', $config->getDefaultSender()->__toString());
    }
    
    public function testSetSubjectPrefix(): void {
        $config = new EmailConfig();
        $config->setSubjectPrefix('A subject prefix');
        $this->assertEquals('A subject prefix', $config->getSubjectPrefix());
    }
    

    public function testFromWithArray(): void {
        $origConfig = array(
            'timezone'      => 'Europe/Berlin',
            'mailMode'      => EmailQueue::BLOCK,
            'smtpConfig'    => json_decode(SmtpConfigTest::getJsonTestString(), TRUE),
            'rerouteConfig' => json_decode(RerouteConfigTest::getJsonTestString(), TRUE),
            'bccConfig'     => json_decode(BccConfigTest::getJsonTestString(), TRUE),
            'debugAddress'  => 'Jane Doe  <jane.doe@example.com',
            'defaultSender' => 'John Doe  <john.doe@example.com',
            'subjectPrefix' => 'A subject prefix',
        );
        
        $config = EmailConfig::from($origConfig);
        $this->assertEquals('Europe/Berlin',                   $config->getTimezone());
        $this->assertEquals(EmailQueue::BLOCK,                 $config->getMailMode());
        $this->assertInstanceOf(SmtpConfig::class,             $config->getSmtpConfig());
        $this->assertInstanceOf(RerouteConfig::class,          $config->getRerouteConfig());
        $this->assertInstanceOf(BccConfig::class,              $config->getBccConfig());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $config->getDebugAddress()[0]->__toString());
        $this->assertEquals('John Doe <john.doe@example.com>', $config->getDefaultSender()->__toString());
        $this->assertEquals('A subject prefix',                $config->getSubjectPrefix());
    }
    
    public function testFromWithString(): void {
        $origConfig = self::getJsonTestString();
        
        $config = EmailConfig::from($origConfig);
        $this->assertEquals('Europe/Berlin',                   $config->getTimezone());
        $this->assertEquals(EmailQueue::BLOCK,                 $config->getMailMode());
        $this->assertInstanceOf(SmtpConfig::class,             $config->getSmtpConfig());
        $this->assertInstanceOf(RerouteConfig::class,          $config->getRerouteConfig());
        $this->assertInstanceOf(BccConfig::class,              $config->getBccConfig());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $config->getDebugAddress()[0]->__toString());
        $this->assertEquals('John Doe <john.doe@example.com>', $config->getDefaultSender()->__toString());
        $this->assertEquals('A subject prefix',                $config->getSubjectPrefix());
    }
    
    public function testFromWithObject(): void {
        $origConfig = json_decode(self::getJsonTestString());
        
        $config = EmailConfig::from($origConfig);
        $this->assertEquals('Europe/Berlin',                   $config->getTimezone());
        $this->assertEquals(EmailQueue::BLOCK,                 $config->getMailMode());
        $this->assertInstanceOf(SmtpConfig::class,             $config->getSmtpConfig());
        $this->assertInstanceOf(RerouteConfig::class,          $config->getRerouteConfig());
        $this->assertInstanceOf(BccConfig::class,              $config->getBccConfig());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $config->getDebugAddress()[0]->__toString());
        $this->assertEquals('John Doe <john.doe@example.com>', $config->getDefaultSender()->__toString());
        $this->assertEquals('A subject prefix',                $config->getSubjectPrefix());
    }
    
    public static function getJsonTestString() {
        return '{"timezone":"Europe\/Berlin","mailMode":"block","smtpConfig":{"host":"www.example.com","port":587,"debugLevel":2,"auth":true,"credentials":{"user":"username","pass":"password"},"secureOption":"tls","charset":"utf8"},"rerouteConfig":{"recipients":["John Doe  <john.doe@example.com",{"name":"Jane Doe","email":"jane.doe@example.com"}],"subjectPrefix":"A specific prefix"},"bccConfig":{"recipients":["John Doe  <john.doe@example.com",{"name":"Jane Doe","email":"jane.doe@example.com"}]},"debugAddress":"Jane Doe  <jane.doe@example.com","defaultSender":"John Doe  <john.doe@example.com","subjectPrefix":"A subject prefix"}';
    }
}

