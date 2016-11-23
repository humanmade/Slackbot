<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator\File;

use Zend\Validator\File;
use Zend\Validator;

/**
 * @group      Zend_Validator
 */
class WordCountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function basicBehaviorDataProvider()
    {
        $testFile = __DIR__ . '/_files/wordcount.txt';
        $testData = [
            //    Options, isValid Param, Expected value
            [15,      $testFile,     true],
            [4,       $testFile,     false],
            [['min' => 0,  'max' => 10], $testFile,   true],
            [['min' => 10, 'max' => 15], $testFile,   false],
        ];

        // Dupe data in File Upload format
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1], 'name' => basename($data[1]),
                'size' => 200, 'error' => 0, 'type' => 'text'
            ];
            $testData[] = [$data[0], $fileUpload, $data[2]];
        }
        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testBasic($options, $isValidParam, $expected)
    {
        $validator = new File\WordCount($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Zend\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testLegacy($options, $isValidParam, $expected)
    {
        if (is_array($isValidParam)) {
            $validator = new File\WordCount($options);
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        }
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\WordCount(['min' => 1, 'max' => 5]);
        $this->assertEquals(1, $validator->getMin());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\WordCount(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $validator->setMin(100);
        $this->assertEquals(100, $validator->getMin());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'less than or equal');
        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new File\WordCount(['min' => 1, 'max' => 100]);
        $this->assertEquals(100, $validator->getMax());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\WordCount(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1000000);
        $this->assertEquals(1000000, $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals(1000000, $validator->getMax());
    }

    /**
     * @group ZF-11258
     */
    public function testZF11258()
    {
        $validator = new File\WordCount(['min' => 1, 'max' => 10000]);
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileWordCountNotFound', $validator->getMessages());
        $this->assertContains("does not exist", current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage()
    {
        $validator = new File\WordCount();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\WordCount::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\WordCount::NOT_FOUND, $validator->getMessages());
    }

    public function testCanSetMinValueUsingOptionsArray()
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $minValue  = 33;
        $options   = ['min' => $minValue];

        $validator->setMin($options);
        $this->assertSame($minValue, $validator->getMin());
    }

    public function invalidMinMaxValues()
    {
        return [
            'null'               => [null],
            'true'               => [true],
            'false'              => [false],
            'non-numeric-string' => ['not-a-good-value'],
            'array-without-keys' => [[100]],
            'object'             => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidMinMaxValues
     */
    public function testSettingMinValueRaisesExceptionForInvalidType($value)
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            'Invalid options to validator provided'
        );
        $validator->setMin($value);
    }

    public function testCanSetMaxValueUsingOptionsArray()
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $maxValue  = 33333333;
        $options   = ['max' => $maxValue];

        $validator->setMax($options);
        $this->assertSame($maxValue, $validator->getMax());
    }

    /**
     * @dataProvider invalidMinMaxValues
     */
    public function testSettingMaxValueRaisesExceptionForInvalidType($value)
    {
        $validator = new File\WordCount(['min' => 1000, 'max' => 10000]);
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            'Invalid options to validator provided'
        );
        $validator->setMax($value);
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat()
    {
        $validator = new File\WordCount(['min' => 1, 'max' => 10000]);
        $value     = ['foo' => 'bar'];
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            'Value array must be in $_FILES format'
        );
        $validator->isValid($value);
    }

    public function testConstructCanAcceptAllOptionsAsDiscreteArguments()
    {
        $min       = 1;
        $max       = 10000;
        $validator = new File\WordCount($min, $max);

        $this->assertSame($min, $validator->getMin());
        $this->assertSame($max, $validator->getMax());
    }
}
