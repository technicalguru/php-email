<?php declare(strict_types=1);

namespace TgEmail;

use PHPUnit\Framework\TestCase;

/**
 * Tests the EmailAddress.
 * 
 * @author ralph
 *        
 */
class EmailAddressTest extends TestCase {

    public function testToStringWithEmail(): void {
        $addr = new EmailAddress('john.doe@example.com');
        $this->assertEquals('<john.doe@example.com>', $addr->__toString());
    }
    
    public function testToStringWithEmailName(): void {
        $addr = new EmailAddress('john.doe@example.com', 'John Doe');
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithEmail1(): void {
        $addr = EmailAddress::from('john.doe@example.com');
        $this->assertEquals('<john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithEmail2(): void {
        $addr = EmailAddress::from('<john.doe@example.com>');
        $this->assertEquals('<john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithString(): void {
        $addr = EmailAddress::from('John Doe <john.doe@example.com>');
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithJsonString(): void {
        $addr = EmailAddress::from('{"name":"John Doe","email":"john.doe@example.com"}');
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithEmailName(): void {
        $addr = EmailAddress::from('john.doe@example.com', 'John Doe');
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }
    
    public function testFromWithObject(): void {
        $obj = new \stdClass;
        $obj->name  = 'John Doe';
        $obj->email = 'john.doe@example.com';
        $addr = EmailAddress::from($obj);
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }

    public function testFromWithAddress(): void {
        $obj = new EmailAddress('john.doe@example.com', 'John Doe');
        $addr = EmailAddress::from($obj);
        $this->assertEquals('John Doe <john.doe@example.com>', $addr->__toString());
    }
}

