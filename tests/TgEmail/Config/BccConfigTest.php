<?php declare(strict_types=1);

namespace TgEmail\Config;

use PHPUnit\Framework\TestCase;

use TgEmail\EmailAddress;

/**
 * Tests the BccConfig.
 * 
 * @author ralph
 *        
 */
class BccConfigTest extends TestCase {

    public function testAddRecipientsWithString(): void {
        $config = new BccConfig();
        $config->addRecipients('John Doe <john.doe@example.com');
        
        $recipients = $config->getRecipients();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddRecipientsWithObject(): void {
        $config = new BccConfig();
        $config->addRecipients(EmailAddress::from('John Doe  <john.doe@example.com'));
        
        $recipients = $config->getRecipients();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddRecipientsWithArray(): void {
        $config = new BccConfig();
        $config->addRecipients(array(
            EmailAddress::from('John Doe  <john.doe@example.com'),
            EmailAddress::from('Jane Doe  <jane.doe@example.com'),
        ));
        
        $recipients = $config->getRecipients();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testFromWithArray(): void {
        $origConfig = array(
            'recipients' => array(
                'John Doe  <john.doe@example.com',
                array(
                    'name'  => 'Jane Doe',
                    'email' => 'jane.doe@example.com',
                ),
            ),
        );
        
        $config = BccConfig::from($origConfig);
        $recipients = $config->getRecipients();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testFromWithString(): void {
        $origConfig = self::getJsonTestString();
        
        $config = BccConfig::from($origConfig);
        $recipients = $config->getRecipients();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testFromWithObject(): void {
        $origConfig = json_decode(self::getJsonTestString());
        
        $config = BccConfig::from($origConfig);
        $recipients = $config->getRecipients();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public static function getJsonTestString() {
        return '{"recipients":["John Doe  <john.doe@example.com",{"name":"Jane Doe","email":"jane.doe@example.com"}]}';
    }
}

