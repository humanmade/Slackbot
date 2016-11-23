<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\Between;

/**
 * @group      Zend_Validator
 */
class BetweenTest extends \PHPUnit_Framework_TestCase
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
         *      - minimum
         *      - maximum
         *      - inclusive
         *      - expected validation result
         *      - array of test input values
         */
        $valuesExpected = [
            [1, 100, true, true, [1, 10, 100]],
            [1, 100, true, false, [0, 0.99, 100.01, 101]],
            [1, 100, false, false, [0, 1, 100, 101]],
            ['a', 'z', true, true, ['a', 'b', 'y', 'z']],
            ['a', 'z', false, false, ['!', 'a', 'z']]
            ];
        foreach ($valuesExpected as $element) {
            $validator = new Between(['min' => $element[0], 'max' => $element[1], 'inclusive' => $element[2]]);
            foreach ($element[4] as $input) {
                $this->assertEquals(
                    $element[3],
                    $validator->isValid($input),
                    'Failed values: ' . $input . ":" . implode("\n", $validator->getMessages())
                );
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
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals([], $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(1, $validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(10, $validator->getMax());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     *
     * @return void
     */
    public function testGetInclusive()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(true, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertAttributeEquals($validator->getOption('messageTemplates'), 'messageTemplates', $validator);
    }

    public function testEqualsMessageVariables()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertAttributeEquals($validator->getOption('messageVariables'), 'messageVariables', $validator);
    }

    /**
     * @covers Zend\Validator\Between::__construct()
     * @dataProvider constructBetweenValidatorInvalidDataProvider
     *
     * @param array $args
     */
    public function testMissingMinOrMax(array $args)
    {
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            "Missing option. 'min' and 'max' have to be given"
        );

        new Between($args);
    }

    public function constructBetweenValidatorInvalidDataProvider()
    {
        return [
            [
                ['min' => 1],
            ],
            [
                ['max' => 5],
            ],
        ];
    }

    public function testConstructorCanAcceptInclusiveParameter()
    {
        $validator = new Between(1, 10, false);
        $this->assertFalse($validator->getInclusive());
    }
}
