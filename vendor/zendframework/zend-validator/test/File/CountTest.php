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
use ReflectionClass;

/**
 * @group      Zend_Validator
 */
class CountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = [
            [5, true, true, true, true],
            [['min' => 0, 'max' => 3], true, true, true, false],
            [['min' => 2, 'max' => 3], false, true, true, false],
            [['min' => 2], false, true, true, true],
            [['max' => 5], true, true, true, true],
            ];

        foreach ($valuesExpected as $element) {
            $validator = new File\Count($element[0]);
            $this->assertEquals(
                $element[1],
                $validator->isValid(__DIR__ . '/_files/testsize.mo'),
                "Tested with " . var_export($element, 1)
            );
            $this->assertEquals(
                $element[2],
                $validator->isValid(__DIR__ . '/_files/testsize2.mo'),
                "Tested with " . var_export($element, 1)
            );
            $this->assertEquals(
                $element[3],
                $validator->isValid(__DIR__ . '/_files/testsize3.mo'),
                "Tested with " . var_export($element, 1)
            );
            $this->assertEquals(
                $element[4],
                $validator->isValid(__DIR__ . '/_files/testsize4.mo'),
                "Tested with " . var_export($element, 1)
            );
        }
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\Count(['min' => 1, 'max' => 5]);
        $this->assertEquals(1, $validator->getMin());
    }

    public function testGetMinGreaterThanOrEqualThrowsException()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
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
        $validator = new File\Count(['min' => 1, 'max' => 100]);
        $this->assertEquals(100, $validator->getMax());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1000000);
        $this->assertEquals(1000000, $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals(1000000, $validator->getMax());
    }

    public function testCanSetMaxValueUsingAnArrayWithMaxKey()
    {
        $validator   = new File\Count(['min' => 1000, 'max' => 10000]);
        $maxValue    = 33333333;
        $setMaxArray = ['max' => $maxValue];

        $validator->setMax($setMaxArray);
        $this->assertSame($maxValue, $validator->getMax());
    }

    public function invalidMinMaxValues()
    {
        return [
            'null'           => [null],
            'true'           => [true],
            'false'          => [false],
            'invalid-string' => ['will-not-work'],
            'invalid-array'  => [[100]],
            'object'         => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidMinMaxValues
     */
    public function testSettingMaxWithInvalidArgumentRaisesException($max)
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            'Invalid options to validator provided'
        );

        $validator->setMax($max);
    }

    public function testCanSetMinUsingAnArrayWithAMinKey()
    {
        $validator   = new File\Count(['min' => 1000, 'max' => 10000]);
        $minValue    = 33;
        $setMinArray = ['min' => $minValue];

        $validator->setMin($setMinArray);
        $this->assertEquals($minValue, $validator->getMin());
    }

    /**
     * @dataProvider invalidMinMaxValues
     */
    public function testSettingMinWithInvalidArgumentRaisesException($min)
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $this->setExpectedException(
            'Zend\Validator\Exception\InvalidArgumentException',
            'Invalid options to validator provided'
        );
        $validator->setMin($min);
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithArrayRepresentingTooFewFiles()
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $filename  = 'test.txt';
        $fileArray = ['name' => $filename];

        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $fileArray, File\Count::TOO_FEW);

        $this->assertFalse($result);
        $this->assertEquals($filename, $property->getValue($validator));
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithASingleFilename()
    {
        $validator  = new File\Count(['min' => 1000, 'max' => 10000]);
        $filename   = 'test.txt';
        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $filename, File\Count::TOO_FEW);

        $this->assertFalse($result);
        $this->assertEquals($filename, $property->getValue($validator));
    }

    public function testCanProvideMinAndMaxAsDiscreteConstructorArguments()
    {
        $min       = 1000;
        $max       = 10000;
        $validator = new File\Count($min, $max);

        $this->assertSame($min, $validator->getMin());
        $this->assertSame($max, $validator->getMax());
    }
}
