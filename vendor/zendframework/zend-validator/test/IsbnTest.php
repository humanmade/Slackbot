<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\Isbn;

/**
 * @group      Zend_Validator
 */
class IsbnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $validator = new Isbn();

        // Brave New World by Aldous Huxley
        $this->assertTrue($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('006092987X'));

        // Time Rations by Benjamin Friedlander
        $this->assertTrue($validator->isValid('188202205X'));
        $this->assertFalse($validator->isValid('1882022059'));

        // Towards The Primeval Lighting Field by Will Alexander
        $this->assertTrue($validator->isValid('1882022300'));
        $this->assertFalse($validator->isValid('1882022301'));

        //  ISBN-13 for dummies by Zoë Wykes
        $this->assertTrue($validator->isValid('9780555023402'));
        $this->assertFalse($validator->isValid('97805550234029'));

        // Change Your Brain, Change Your Life Daniel G. Amen
        $this->assertTrue($validator->isValid('9780812929980'));
        $this->assertFalse($validator->isValid('9780812929981'));
    }

    /**
     * Ensures that setSeparator() works as expected
     *
     * @return void
     */
    public function testType()
    {
        $validator = new Isbn();

        $validator->setType(Isbn::AUTO);
        $this->assertEquals(Isbn::AUTO, $validator->getType());

        $validator->setType(Isbn::ISBN10);
        $this->assertEquals(Isbn::ISBN10, $validator->getType());

        $validator->setType(Isbn::ISBN13);
        $this->assertEquals(Isbn::ISBN13, $validator->getType());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'Invalid ISBN type');
        $validator->setType('X');
    }

    /**
     * Ensures that setSeparator() works as expected
     *
     * @return void
     */
    public function testSeparator()
    {
        $validator = new Isbn();

        $validator->setSeparator('-');
        $this->assertEquals('-', $validator->getSeparator());

        $validator->setSeparator(' ');
        $this->assertEquals(' ', $validator->getSeparator());

        $validator->setSeparator('');
        $this->assertEquals('', $validator->getSeparator());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'Invalid ISBN separator');
        $validator->setSeparator('X');
    }


    /**
     * Ensures that __construct() works as expected
     *
     * @return void
     */
    public function testInitialization()
    {
        $options = ['type'      => Isbn::AUTO,
                         'separator' => ' '];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::AUTO, $validator->getType());
        $this->assertEquals(' ', $validator->getSeparator());

        $options = ['type'      => Isbn::ISBN10,
                         'separator' => '-'];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::ISBN10, $validator->getType());
        $this->assertEquals('-', $validator->getSeparator());

        $options = ['type'      => Isbn::ISBN13,
                         'separator' => ''];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::ISBN13, $validator->getType());
        $this->assertEquals('', $validator->getSeparator());
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testTypeAuto()
    {
        $validator = new Isbn();

        $this->assertTrue($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('0 06 092987 1'));

        $this->assertTrue($validator->isValid('9780555023402'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator('-');

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertTrue($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('0 06 092987 1'));

        $this->assertFalse($validator->isValid('9780555023402'));
        $this->assertTrue($validator->isValid('978-0-555023-40-2'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator(' ');

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertTrue($validator->isValid('0 06 092987 1'));

        $this->assertFalse($validator->isValid('9780555023402'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));
        $this->assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testType10()
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN10);

        $this->assertTrue($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        $this->assertTrue($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        $this->assertTrue($validator->isValid('0 06 092987 1'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testType13()
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN13);

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertTrue($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertTrue($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        $this->assertFalse($validator->isValid('0 06 092987 1'));
        $this->assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * @group ZF-9605
     */
    public function testInvalidTypeGiven()
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN13);

        $this->assertFalse($validator->isValid((float) 1.2345));
        $this->assertFalse($validator->isValid((object) 'Test'));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Isbn();
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }
}
