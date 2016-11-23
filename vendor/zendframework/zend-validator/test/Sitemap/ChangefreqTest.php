<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator\Sitemap;

use Zend\Validator\Sitemap\Changefreq;

/**
 * @group      Zend_Validator
 */
class ChangefreqTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Changefreq
     */
    protected $validator;

    protected function setUp()
    {
        $this->validator = new Changefreq();
    }

    /**
     * Tests valid change frequencies
     *
     */
    public function testValidChangefreqs()
    {
        $values = [
            'always',  'hourly', 'daily', 'weekly',
            'monthly', 'yearly', 'never'
        ];

        foreach ($values as $value) {
            $this->assertSame(true, $this->validator->isValid($value));
        }
    }

    /**
     * Tests strings that should be invalid
     *
     */
    public function testInvalidStrings()
    {
        $values = [
            'alwayz',  '_hourly', 'Daily', 'wEekly',
            'mönthly ', ' yearly ', 'never ', 'rofl',
            'yesterday',
        ];

        foreach ($values as $value) {
            $this->assertSame(false, $this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertContains('is not a valid', current($messages));
        }
    }

    /**
     * Tests values that are not strings
     *
     */
    public function testNotString()
    {
        $values = [
            1, 1.4, null, new \stdClass(), true, false
        ];

        foreach ($values as $value) {
            $this->assertSame(false, $this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertContains('String expected', current($messages));
        }
    }
}
