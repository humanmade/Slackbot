<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\LessThan;

/**
 * @group      Zend_Validator
 */
class LessThanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        /**
         * The elements of each array are, in order:
         *      - maximum
         *      - expected validation result
         *      - array of test input values
         */
        $valuesExpected = [
            [100, true, [-1, 0, 0.01, 1, 99.999]],
            [100, false, [100, 100.0, 100.01]],
            ['a', false, ['a', 'b', 'c', 'd']],
            ['z', true, ['x', 'y']],
            [['max' => 100, 'inclusive' => true], true, [-1, 0, 0.01, 1, 99.999, 100, 100.0]],
            [['max' => 100, 'inclusive' => true], false, [100.01]],
            [['max' => 100, 'inclusive' => false], true, [-1, 0, 0.01, 1, 99.999]],
            [['max' => 100, 'inclusive' => false], false, [100, 100.0, 100.01]]
        ];

        foreach ($valuesExpected as $element) {
            $validator = new LessThan($element[0]);
            foreach ($element[2] as $input) {
                $this->assertEquals($element[1], $validator->isValid($input));
            }
        }
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new LessThan(10);
        $this->assertEquals([], $validator->getMessages());
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new LessThan(10);
        $this->assertEquals(10, $validator->getMax());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     *
     * @return void
     */
    public function testGetInclusive()
    {
        $validator = new LessThan(10);
        $this->assertEquals(false, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new LessThan(10);
        $this->assertAttributeEquals(
            $validator->getOption('messageTemplates'),
            'messageTemplates',
            $validator
        );
    }

    public function testEqualsMessageVariables()
    {
        $validator = new LessThan(10);
        $this->assertAttributeEquals(
            $validator->getOption('messageVariables'),
            'messageVariables',
            $validator
        );
    }

    public function testConstructorAllowsSettingAllOptionsAsDiscreteArguments()
    {
        $validator = new LessThan(10, true);
        $this->assertSame(10, $validator->getMax());
        $this->assertTrue($validator->getInclusive());
    }
}
