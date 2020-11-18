<?php declare(strict_types=1);

namespace TgEmail;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Email object.
 * @author ralph
 *        
 */
class EmailTest extends TestCase {

    public function testSetSender(): void {
        $email = new Email();
        $email->setSender(EmailAddress::from('John Doe <john.doe@example.com>'));
        $this->assertEquals('John Doe <john.doe@example.com>', $email->getSender()->__toString());
    }
    
    public function testSetReplyTo(): void {
        $email = new Email();
        $email->setReplyTo(EmailAddress::from('John Doe <john.doe@example.com>'));
        $this->assertEquals('John Doe <john.doe@example.com>', $email->getReplyTo()->__toString());
    }
    
    public function testAddToWithString(): void {
        $email = new Email();
        $email->addTo('John Doe <john.doe@example.com');
        
        $recipients = $email->getTo();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddToWithObject(): void {
        $email = new Email();
        $email->addTo(EmailAddress::from('John Doe  <john.doe@example.com'));
        
        $recipients = $email->getTo();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddToWithArray(): void {
        $email = new Email();
        $email->addTo(array(
            EmailAddress::from('John Doe  <john.doe@example.com'),
            EmailAddress::from('Jane Doe  <jane.doe@example.com'),
        ));
        
        $recipients = $email->getTo();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testAddCcWithString(): void {
        $email = new Email();
        $email->addCc('John Doe <john.doe@example.com');
        
        $recipients = $email->getCc();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddCcWithObject(): void {
        $email = new Email();
        $email->addCc(EmailAddress::from('John Doe  <john.doe@example.com'));
        
        $recipients = $email->getCc();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddCcWithArray(): void {
        $email = new Email();
        $email->addCc(array(
            EmailAddress::from('John Doe  <john.doe@example.com'),
            EmailAddress::from('Jane Doe  <jane.doe@example.com'),
        ));
        
        $recipients = $email->getCc();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testAddBccWithString(): void {
        $email = new Email();
        $email->addBcc('John Doe <john.doe@example.com');
        
        $recipients = $email->getBcc();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddBccWithObject(): void {
        $email = new Email();
        $email->addBcc(EmailAddress::from('John Doe  <john.doe@example.com'));
        
        $recipients = $email->getBcc();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
    }
    
    public function testAddBccWithArray(): void {
        $email = new Email();
        $email->addBcc(array(
            EmailAddress::from('John Doe  <john.doe@example.com'),
            EmailAddress::from('Jane Doe  <jane.doe@example.com'),
        ));
        
        $recipients = $email->getBcc();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals('John Doe <john.doe@example.com>', $recipients[0]->__toString());
        $this->assertEquals('Jane Doe <jane.doe@example.com>', $recipients[1]->__toString());
    }
    
    public function testSetSubject(): void {
        $email = new Email();
        $email->setSubject('A subject');
        $this->assertEquals('A subject', $email->getSubject());
    }
    
    public function testSetHtmlBody(): void {
        $email = new Email();
        $email->setBody(Email::HTML, 'A HTML body text');
        $this->assertEquals('A HTML body text', $email->getBody(Email::HTML));
    }
    
    public function testSetTextBody(): void {
        $email = new Email();
        $email->setBody(Email::TEXT, 'A plain body text');
        $this->assertEquals('A plain body text', $email->getBody(Email::TEXT));
    }
    
    
}

