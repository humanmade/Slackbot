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

/**
 * Md5 testbed
 *
 * @group      Zend_Validator
 */
class Md5Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function basicBehaviorDataProvider()
    {
        $testFile = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            [
                'ed74c22109fe9f110579f77b053b8bc3',
                $testFile, true, ''
            ],
            [
                '4d74c22109fe9f110579f77b053b8bc3',
                $testFile, false, 'fileMd5DoesNotMatch'
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', 'ed74c22109fe9f110579f77b053b8bc3'],
                $testFile, true, ''
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', '7d74c22109fe9f110579f77b053b8bc3'],
                $testFile, false, 'fileMd5DoesNotMatch'
            ],
        ];

        $testFile = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ed74c22109fe9f110579f77b053b8bc3', $testFile, false, 'fileMd5NotFound'],
        ];

        $testFile = __DIR__ . '/_files/testsize.mo';
        $sizeFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ec441f84a2944405baa22873cda22370', $testFile, true,  ''],
            ['7d74c22109fe9f110579f77b053b8bc3', $testFile, false, 'fileMd5DoesNotMatch'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $sizeFileTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1], 'name' => basename($data[1]),
                'size' => 200, 'error' => 0, 'type' => 'text'
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];
        }
        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testBasic($options, $isValidParam, $expected, $messageKey)
    {
        $validator = new File\Md5($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam));
        if (!$expected) {
            $this->assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Zend\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testLegacy($options, $isValidParam, $expected, $messageKey)
    {
        if (is_array($isValidParam)) {
            $validator = new File\Md5($options);
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
            if (!$expected) {
                $this->assertArrayHasKey($messageKey, $validator->getMessages());
            }
        }
    }

    /**
     * Ensures that getMd5() returns expected value
     *
     * @return void
     */
    public function testgetMd5()
    {
        $validator = new File\Md5('12345');
        $this->assertEquals(['12345' => 'md5'], $validator->getMd5());

        $validator = new File\Md5(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'md5', '12333' => 'md5', '12344' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that getHash() returns expected value
     *
     * @return void
     */
    public function testgetHash()
    {
        $validator = new File\Md5('12345');
        $this->assertEquals(['12345' => 'md5'], $validator->getHash());

        $validator = new File\Md5(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'md5', '12333' => 'md5', '12344' => 'md5'], $validator->getHash());
    }

    /**
     * Ensures that setMd5() returns expected value
     *
     * @return void
     */
    public function testSetMd5()
    {
        $validator = new File\Md5('12345');
        $validator->setMd5('12333');
        $this->assertEquals(['12333' => 'md5'], $validator->getMd5());

        $validator->setMd5(['12321', '12121']);
        $this->assertEquals(['12321' => 'md5', '12121' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that setHash() returns expected value
     *
     * @return void
     */
    public function testSetHash()
    {
        $validator = new File\Md5('12345');
        $validator->setHash('12333');
        $this->assertEquals(['12333' => 'md5'], $validator->getMd5());

        $validator->setHash(['12321', '12121']);
        $this->assertEquals(['12321' => 'md5', '12121' => 'md5'], $validator->getMd5());
    }

    /**
     * Ensures that addMd5() returns expected value
     *
     * @return void
     */
    public function testAddMd5()
    {
        $validator = new File\Md5('12345');
        $validator->addMd5('12344');
        $this->assertEquals(['12345' => 'md5', '12344' => 'md5'], $validator->getMd5());

        $validator->addMd5(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'md5', '12344' => 'md5', '12321' => 'md5', '12121' => 'md5'],
            $validator->getMd5()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     *
     * @return void
     */
    public function testAddHash()
    {
        $validator = new File\Md5('12345');
        $validator->addHash('12344');
        $this->assertEquals(['12345' => 'md5', '12344' => 'md5'], $validator->getMd5());

        $validator->addHash(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'md5', '12344' => 'md5', '12321' => 'md5', '12121' => 'md5'],
            $validator->getMd5()
        );
    }

    /**
     * @group ZF-11258
     */
    public function testZF11258()
    {
        $validator = new File\Md5('12345');
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileMd5NotFound', $validator->getMessages());
        $this->assertContains("does not exist", current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage()
    {
        $validator = new File\Md5();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\Md5::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\Md5::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat()
    {
        $validator = new File\Md5();
        $value     = ['foo' => 'bar'];
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');
        $validator->isValid($value);
    }
}
