<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\AbstractValidator;
use Zend\I18n\Validator\Alpha;
use Zend\Validator\Between;
use Zend\Validator\StaticValidator;
use Zend\Validator\ValidatorPluginManager;

/**
 * @group      Zend_Validator
 */
class StaticValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Alpha */
    public $validator;

    /**
     * Creates a new validation object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        AbstractValidator::setDefaultTranslator(null);
        StaticValidator::setPluginManager(null);
        $this->validator = new Alpha();
    }

    public function tearDown()
    {
        AbstractValidator::setDefaultTranslator(null);
        AbstractValidator::setMessageLength(-1);
    }

    public function testCanSetGlobalDefaultTranslator()
    {
        $translator = new TestAsset\Translator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    public function testGlobalDefaultTranslatorUsedWhenNoLocalTranslatorSet()
    {
        $this->testCanSetGlobalDefaultTranslator();
        $this->assertSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testLocalTranslatorPreferredOverGlobalTranslator()
    {
        $this->testCanSetGlobalDefaultTranslator();
        $translator = new TestAsset\Translator();
        $this->validator->setTranslator($translator);
        $this->assertNotSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testMaximumErrorMessageLength()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->assertEquals(-1, AbstractValidator::getMessageLength());
        AbstractValidator::setMessageLength(10);
        $this->assertEquals(10, AbstractValidator::getMessageLength());

        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = [
            'Invalid type given. String expected' => 'This is the translated message for %value%',
        ];
        $translator = new TestAsset\Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        $this->validator->setTranslator($translator);
        $this->assertFalse($this->validator->isValid(123));
        $messages = $this->validator->getMessages();

        $this->assertArrayHasKey(Alpha::INVALID, $messages);
        $this->assertEquals('This is...', $messages[Alpha::INVALID]);
    }

    public function testSetGetMessageLengthLimitation()
    {
        AbstractValidator::setMessageLength(5);
        $this->assertEquals(5, AbstractValidator::getMessageLength());

        $valid = new Between(1, 10);
        $this->assertFalse($valid->isValid(24));
        $message = current($valid->getMessages());
        $this->assertLessThanOrEqual(5, strlen($message));
    }

    public function testSetGetDefaultTranslator()
    {
        $translator = new TestAsset\Translator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    /* plugin loading */

    public function testLazyLoadsValidatorPluginManagerByDefault()
    {
        $plugins = StaticValidator::getPluginManager();
        $this->assertInstanceOf('Zend\Validator\ValidatorPluginManager', $plugins);
    }

    public function testCanSetCustomPluginManager()
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder('Zend\ServiceManager\ServiceManager')->getMock());
        StaticValidator::setPluginManager($plugins);
        $this->assertSame($plugins, StaticValidator::getPluginManager());
    }

    public function testPassingNullWhenSettingPluginManagerResetsPluginManager()
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder('Zend\ServiceManager\ServiceManager')->getMock());
        StaticValidator::setPluginManager($plugins);
        $this->assertSame($plugins, StaticValidator::getPluginManager());
        StaticValidator::setPluginManager(null);
        $this->assertNotSame($plugins, StaticValidator::getPluginManager());
    }

    public function parameterizedData()
    {
        return [
            'valid-positive-range'   => [5, 'between', ['min' => 1, 'max' => 10], true],
            'valid-negative-range'   => [-5, 'between', ['min' => -10, 'max' => -1], true],
            'invalid-positive-range' => [-5, 'between', ['min' => 1, 'max' => 10], false],
            'invalid-negative-range' => [5, 'between', ['min' => -10, 'max' => -1], false],
        ];
    }

    /**
     * @dataProvider parameterizedData
     */
    public function testExecuteValidWithParameters($value, $validator, $options, $expected)
    {
        $this->assertSame($expected, StaticValidator::execute($value, $validator, $options));
    }

    public function invalidParameterizedData()
    {
        return [
            'positive-range' => [5, 'between', [1, 10]],
            'negative-range' => [-5, 'between', [-10, -1]],
        ];
    }

    /**
     * @dataProvider invalidParameterizedData
     */
    public function testExecuteRaisesExceptionForIndexedOptionsArray($value, $validator, $options)
    {
        $this->setExpectedException('InvalidArgumentException', 'options');
        StaticValidator::execute($value, $validator, $options);
    }
}
