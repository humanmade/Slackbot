<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;

/**
 * @group      Zend_Validator
 */
class EmailAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailAddress
     */
    protected $validator;

    /** @var bool */
    public $multipleOptionsDetected;

    public function setUp()
    {
        $this->validator = new EmailAddress();
    }

    /**
     * Ensures that a basic valid e-mail address passes validation
     *
     * @return void
     */
    public function testBasic()
    {
        $this->assertTrue($this->validator->isValid('username@example.com'));
    }

    /**
     * Ensures that localhost address is valid
     *
     * @return void
     */
    public function testLocalhostAllowed()
    {
        $validator = new EmailAddress(Hostname::ALLOW_ALL);
        $this->assertTrue($validator->isValid('username@localhost'));
    }

    /**
     * Ensures that local domain names are valid
     *
     * @return void
     */
    public function testLocaldomainAllowed()
    {
        $validator = new EmailAddress(Hostname::ALLOW_ALL);
        $this->assertTrue($validator->isValid('username@localhost.localdomain'));
    }

    /**
     * Ensures that IP hostnames are valid
     *
     * @return void
     */
    public function testIPAllowed()
    {
        $validator = new EmailAddress(Hostname::ALLOW_DNS | Hostname::ALLOW_IP);
        $valuesExpected = [
            [Hostname::ALLOW_DNS, true, ['bob@212.212.20.4']],
            [Hostname::ALLOW_DNS, false, ['bob@localhost']]
            ];
        foreach ($valuesExpected as $element) {
            foreach ($element[2] as $input) {
                $this->assertEquals($element[1], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that validation fails when the local part is missing
     *
     * @return void
     */
    public function testLocalPartMissing()
    {
        $this->assertFalse($this->validator->isValid('@example.com'));
        $messages = $this->validator->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertContains('local-part@hostname', current($messages));
    }

    /**
     * Ensures that validation fails and produces the expected messages when the local part is invalid
     *
     * @return void
     */
    public function testLocalPartInvalid()
    {
        $this->assertFalse($this->validator->isValid('Some User@example.com'));

        $messages = $this->validator->getMessages();

        $this->assertEquals(3, count($messages));

        $this->assertContains('Some User', current($messages));
        $this->assertContains('dot-atom', current($messages));

        $this->assertContains('Some User', next($messages));
        $this->assertContains('quoted-string', current($messages));

        $this->assertContains('Some User', next($messages));
        $this->assertContains('not a valid local part', current($messages));
    }

    /**
     * Ensures that no validation failure message is produced when the local part follows the quoted-string format
     *
     * @return void
     */
    public function testLocalPartQuotedString()
    {
        $this->assertTrue($this->validator->isValid('"Some User"@example.com'));

        $messages = $this->validator->getMessages();

        $this->assertInternalType('array', $messages);
        $this->assertEquals(0, count($messages));
    }

    /**
     * Ensures that validation fails when the hostname is invalid
     *
     * @return void
     */
    public function testHostnameInvalid()
    {
        $this->assertFalse($this->validator->isValid('username@ example . com'));
        $messages = $this->validator->getMessages();
        $this->assertThat(count($messages), $this->greaterThanOrEqual(1));
        $this->assertContains('not a valid hostname', current($messages));
    }

    /**
     * Ensures that quoted-string local part is considered valid
     *
     * @return void
     */
    public function testQuotedString()
    {
        $emailAddresses = [
            '""@domain.com', // Optional
            '" "@domain.com', // x20
            '"!"@domain.com', // x21
            '"\""@domain.com', // \" (escaped x22)
            '"#"@domain.com', // x23
            '"$"@domain.com', // x24
            '"Z"@domain.com', // x5A
            '"["@domain.com', // x5B
            '"\\\"@domain.com', // \\ (escaped x5C)
            '"]"@domain.com', // x5D
            '"^"@domain.com', // x5E
            '"}"@domain.com', // x7D
            '"~"@domain.com', // x7E
            '"username"@example.com',
            '"bob%jones"@domain.com',
            '"bob jones"@domain.com',
            '"bob@jones"@domain.com',
            '"[[ bob ]]"@domain.com',
            '"jones"@domain.com'
            ];
        foreach ($emailAddresses as $input) {
            $this->assertTrue($this->validator->isValid($input), "$input failed to pass validation:\n"
                            . implode("\n", $this->validator->getMessages()));
        }
    }

    /**
     * Ensures that quoted-string local part is considered invalid
     *
     * @return void
     */
    public function testInvalidQuotedString()
    {
        $emailAddresses = [
            "\"\x00\"@example.com",
            "\"\x01\"@example.com",
            "\"\x1E\"@example.com",
            "\"\x1F\"@example.com",
            '"""@example.com', // x22 (not escaped)
            '"\"@example.com', // x5C (not escaped)
            "\"\x7F\"@example.com",
            ];
        foreach ($emailAddresses as $input) {
            $this->assertFalse($this->validator->isValid($input), "$input failed to pass validation:\n"
                            . implode("\n", $this->validator->getMessages()));
        }
    }

    /**
     * Ensures that validation fails when the e-mail is given as for display,
     * with angle brackets around the actual address
     *
     * @return void
     */
    public function testEmailDisplay()
    {
        $this->assertFalse($this->validator->isValid('User Name <username@example.com>'));
        $messages = $this->validator->getMessages();
        $this->assertThat(count($messages), $this->greaterThanOrEqual(3));
        $this->assertContains('not a valid hostname', current($messages));
        $this->assertContains('cannot match TLD', next($messages));
        $this->assertContains('does not appear to be a valid local network name', next($messages));
    }

    public function validEmailAddresses()
    {
        // @codingStandardsIgnoreStart
        return [
            'bob@domain.com'                                                          => ['bob@domain.com'],
            'bob.jones@domain.co.uk'                                                  => ['bob.jones@domain.co.uk'],
            'bob.jones.smythe@domain.co.uk'                                           => ['bob.jones.smythe@domain.co.uk'],
            'BoB@domain.museum'                                                       => ['BoB@domain.museum'],
            'bobjones@domain.info'                                                    => ['bobjones@domain.info'],
            'bob+jones@domain.us'                                                     => ['bob+jones@domain.us'],
            'bob+jones@domain.co.uk'                                                  => ['bob+jones@domain.co.uk'],
            'bob@some.domain.uk.com'                                                  => ['bob@some.domain.uk.com'],
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugar.com' => ['bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugar.com'],
            "B.O'Callaghan@domain.com"                                                => ["B.O'Callaghan@domain.com"],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses
     *
     * @dataProvider validEmailAddresses
     */
    public function testBasicValid($value)
    {
        $this->assertTrue(
            $this->validator->isValid($value),
            sprintf(
                '%s failed validation: %s',
                $value,
                implode("\n", $this->validator->getMessages())
            )
        );
    }

    public function invalidEmailAddresses()
    {
        // @codingStandardsIgnoreStart
        return [
            '[empty]'                                                                  => [''],
            'bob jones@domain.com'                                                     => ['bob jones@domain.com'],
            '.bobJones@studio24.com'                                                   => ['.bobJones@studio24.com'],
            'bobJones.@studio24.com'                                                   => ['bobJones.@studio24.com'],
            'bob.Jones.@studio24.com'                                                  => ['bob.Jones.@studio24.com'],
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com' => ['bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com'],
            'bob+domain.com'                                                           => ['bob+domain.com'],
            'bob.domain.com'                                                           => ['bob.domain.com'],
            'bob @domain.com'                                                          => ['bob @domain.com'],
            'bob@ domain.com'                                                          => ['bob@ domain.com'],
            'bob @ domain.com'                                                         => ['bob @ domain.com'],
            'Abc..123@example.com'                                                     => ['Abc..123@example.com'],
            '"bob%jones@domain.com'                                                    => ['"bob%jones@domain.com'],
            'multiline'                                                                => ['bob

            @domain.com'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for invalid email addresses
     *
     * @dataProvider invalidEmailAddresses
     */
    public function testBasicInvalid($value)
    {
        $this->assertFalse($this->validator->isValid($value));
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with complex local parts
     *
     * @return void
     */
    public function testComplexLocalValid()
    {
        $emailAddresses = [
            'Bob.Jones@domain.com',
            'Bob.Jones!@domain.com',
            'Bob&Jones@domain.com',
            '/Bob.Jones@domain.com',
            '#Bob.Jones@domain.com',
            'Bob.Jones?@domain.com',
            'Bob~Jones@domain.com'
            ];
        foreach ($emailAddresses as $input) {
            $this->assertTrue($this->validator->isValid($input));
        }
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with the non-strict option
     *
     * @return void
     */
    public function testNonStrict()
    {
        $validator = new EmailAddress(['strict' => false]);
        $emailAddresses = [
            // RFC 5321 does mention a limit of 64 for the username,
            // but it also states "To the maximum extent possible,
            // implementation techniques that impose no limits on the
            // length of these objects should be used.".
            // http://tools.ietf.org/html/rfc5321#section-4.5.3.1
            'line length 320' => str_repeat('x', 309).'@domain.com',
            'line length 321' => str_repeat('x', 310).'@domain.com',
            'line length 911' => str_repeat('x', 900).'@domain.com',
        ];
        foreach ($emailAddresses as $input) {
            $this->assertTrue($validator->isValid($input));
        }
    }

   /**
     * Ensures that the validator follows expected behavior for checking MX records
     *
     * @return void
     */
    public function testMXRecords()
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress(Hostname::ALLOW_DNS, true);

        // Are MX checks supported by this system?
        if (!$validator->isMxSupported()) {
            $this->markTestSkipped('Testing MX records is not supported with this configuration');
        }

        $valuesExpected = [
            [true,  ['Bob.Jones@zend.com',        'Bob.Jones@php.net']],
            [false, ['Bob.Jones@bad.example.com', 'Bob.Jones@anotherbad.example.com']]
        ];
        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                $this->assertEquals($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }

        // Try a check via setting the option via a method
        unset($validator);
        $validator = new EmailAddress();
        $validator->useMxCheck(true);
        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                $this->assertEquals($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that the validator follows expected behavior for checking MX records with A record fallback.
     * This behavior is documented in RFC 2821, section 5: "If no MX records are found, but an A RR is
     * found, the A RR is treated as if it was associated with an implicit MX RR, with a preference of 0,
     * pointing to that host.
     *
     * @return void
     */
    public function testNoMxRecordARecordFallback()
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress(Hostname::ALLOW_DNS, true);

        // Are MX checks supported by this system?
        if (!$validator->isMxSupported()) {
            $this->markTestSkipped('Testing MX records is not supported with this configuration');
        }

        $email = 'good@example.com';
        $host = preg_replace('/.*@/', null, $email);

        //Assert that email host contains no MX records.
        $this->assertFalse(checkdnsrr($host, 'MX'), 'Email host contains MX records');

        //Asert that email host contains at least one A record.
        $this->assertTrue(checkdnsrr($host, 'A'), 'Email host contains no A records');

        //Assert that validtor falls back to A record.
        $this->assertTrue($validator->isValid($email), implode("\n", $validator->getMessages()));
    }

   /**
     * Test changing hostname settings via EmailAddress object
     *
     * @return void
     */
    public function testHostnameSettings()
    {
        $validator = new EmailAddress();

        // Check no IDN matching
        $validator->getHostnameValidator()->useIdnCheck(false);
        $valuesExpected = [
            [false, ['name@b�rger.de', 'name@h�llo.de', 'name@h�llo.se']]
            ];
        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                $this->assertEquals($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }

        // Check no TLD matching
        $validator->getHostnameValidator()->useTldCheck(false);
        $valuesExpected = [
            [true, ['name@domain.xx', 'name@domain.zz', 'name@domain.madeup']]
            ];
        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                $this->assertEquals($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that getMessages() returns expected default value (an empty array)
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * @group ZF-2861
     */
    public function testHostnameValidatorMessagesShouldBeTranslated()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $hostnameValidator = new Hostname();
        $translations = [
            'hostnameIpAddressNotAllowed'   => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld'            => 'The input appears to be a DNS hostname '
            . 'but cannot match TLD against known list',
            'hostnameDashCharacter'         => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld'     => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname'       => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName'      => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed'   => 'hostnameLocalNameNotAllowed translation',
        ];
        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = $translations;
        $translator = new TestAsset\Translator();
        $translator->getPluginManager()->setService('test', $loader);
        $translator->addTranslationFile('test', null);

        $this->validator->setTranslator($translator)->setHostnameValidator($hostnameValidator);

        $this->validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $hostnameValidator->getMessages();
        $found = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                $this->assertEquals($translations[$code], $message);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * @group ZF-4888
     */
    public function testEmailsExceedingLength()
    {
        // @codingStandardsIgnoreStart
        $emailAddresses = [
            'thislocalpathoftheemailadressislongerthantheallowedsizeof64characters@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarexpialidociousspoonfulofsugar.com',
        ];
        // @codingStandardsIgnoreEnd
        foreach ($emailAddresses as $input) {
            $this->assertFalse($this->validator->isValid($input));
        }
    }

    /**
     * @group ZF-4352
     */
    public function testNonStringValidation()
    {
        $this->assertFalse($this->validator->isValid([1 => 1]));
    }

    /**
     * @group ZF-7490
     */
    public function testSettingHostnameMessagesThroughEmailValidator()
    {
        $translations = [
            'hostnameIpAddressNotAllowed' => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld' => 'hostnameUnknownTld translation',
            'hostnameDashCharacter' => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld' => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname' => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName' => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed' => 'hostnameLocalNameNotAllowed translation',
        ];

        $this->validator->setMessages($translations);
        $this->validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $this->validator->getMessages();
        $found = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                $this->assertEquals($translations[$code], $message);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Testing initializing with several options
     */
    public function testInstanceWithOldOptions()
    {
        $handler = set_error_handler([$this, 'errorHandler'], E_USER_NOTICE);
        $validator = new EmailAddress();
        $options   = $validator->getOptions();

        $this->assertEquals(Hostname::ALLOW_DNS, $options['allow']);
        $this->assertFalse($options['useMxCheck']);

        try {
            $validator = new EmailAddress(Hostname::ALLOW_ALL, true, new Hostname(Hostname::ALLOW_ALL));
            $options   = $validator->getOptions();

            $this->assertEquals(Hostname::ALLOW_ALL, $options['allow']);
            $this->assertTrue($options['useMxCheck']);
            set_error_handler($handler);
        } catch (\Zend\Validator\Exception\InvalidArgumentException $e) {
            $this->markTestSkipped('MX not available on this system');
        }
    }

    /**
     * Testing setOptions
     */
    public function testSetOptions()
    {
        $this->validator->setOptions(['messages' => [EmailAddress::INVALID => 'TestMessage']]);
        $messages = $this->validator->getMessageTemplates();
        $this->assertEquals('TestMessage', $messages[EmailAddress::INVALID]);

        $oldHostname = $this->validator->getHostnameValidator();
        $this->validator->setOptions(['hostnameValidator' => new Hostname(Hostname::ALLOW_ALL)]);
        $hostname = $this->validator->getHostnameValidator();
        $this->assertNotEquals($oldHostname, $hostname);
    }

    /**
     * Testing setMessage
     */
    public function testSetSingleMessage()
    {
        $messages = $this->validator->getMessageTemplates();
        $this->assertNotEquals('TestMessage', $messages[EmailAddress::INVALID]);
        $this->validator->setMessage('TestMessage', EmailAddress::INVALID);
        $messages = $this->validator->getMessageTemplates();
        $this->assertEquals('TestMessage', $messages[EmailAddress::INVALID]);
    }

    public function testSetSingleMessageViaOptions()
    {
        $validator = new EmailAddress(['message' => 'TestMessage']);
        foreach ($validator->getMessageTemplates() as $message) {
            $this->assertEquals('TestMessage', $message);
        }
        foreach ($validator->getHostnameValidator()->getMessageTemplates() as $message) {
            $this->assertEquals('TestMessage', $message);
        }
    }

    public function testSetMultipleMessageViaOptions()
    {
        $validator = new EmailAddress(['messages' => [EmailAddress::INVALID => 'TestMessage']]);
        $messages = $validator->getMessageTemplates();
        $this->assertEquals('TestMessage', $messages[EmailAddress::INVALID]);
    }

    /**
     * Testing getValidateMx
     */
    public function testGetValidateMx()
    {
        $this->assertFalse($this->validator->getMxCheck());
    }

    /**
     * Testing getDeepMxCheck
     */
    public function testGetDeepMxCheck()
    {
        $this->assertFalse($this->validator->getDeepMxCheck());
    }

    /**
     * Testing setMessage for all messages
     *
     * @group ZF-10690
     */
    public function testSetMultipleMessages()
    {
        $messages = $this->validator->getMessageTemplates();
        $this->assertNotEquals('TestMessage', $messages[EmailAddress::INVALID]);
        $this->validator->setMessage('TestMessage');
        foreach ($this->validator->getMessageTemplates() as $message) {
            $this->assertEquals('TestMessage', $message);
        }
        foreach ($this->validator->getHostnameValidator()->getMessageTemplates() as $message) {
            $this->assertEquals('TestMessage', $message);
        }
    }

    /**
     * Testing getDomainCheck
     */
    public function testGetDomainCheck()
    {
        $this->assertTrue($this->validator->getDomainCheck());
    }

    public function errorHandler($errno, $errstr)
    {
        if (strstr($errstr, 'deprecated')) {
            $this->multipleOptionsDetected = true;
        }
    }

    /**
     * @group ZF-11222
     * @group ZF-11451
     */
    public function testEmailAddressesWithTrailingDotInHostPartAreRejected()
    {
        $this->assertFalse($this->validator->isValid('example@gmail.com.'));
        $this->assertFalse($this->validator->isValid('test@test.co.'));
        $this->assertFalse($this->validator->isValid('test@test.co.za.'));
    }

    /**
     * @group ZF-11239
     */
    public function testNotSetHostnameValidator()
    {
        $hostname = $this->validator->getHostnameValidator();
        $this->assertInstanceOf('Zend\Validator\Hostname', $hostname);
    }

    /**
     * Test getMXRecord
     */
    public function testGetMXRecord()
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress(['useMxCheck' => true, 'allow' => Hostname::ALLOW_ALL]);
        $validator = new EmailAddress(['useMxCheck' => true, 'allow' => Hostname::ALLOW_ALL]);

        if (!$validator->isMxSupported()) {
            $this->markTestSkipped('Testing MX records is not supported with this configuration');
        }

        $this->assertTrue($validator->isValid('john.doe@gmail.com'));
        $result = $validator->getMXRecord();
        $this->assertNotEmpty($result);
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertAttributeEquals(
            $validator->getOption('messageTemplates'),
            'messageTemplates',
            $validator
        );
    }

    public function testEqualsMessageVariables()
    {
        $validator = $this->validator;
        $this->assertAttributeEquals(
            $validator->getOption('messageVariables'),
            'messageVariables',
            $validator
        );
    }

    /**
     * @group ZF2-130
     */
    public function testUseMxCheckBasicValid()
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress([
            'useMxCheck'        => true,
            'useDeepMxCheck'    => true
        ]);

        $emailAddresses = [
            'bob@gmail.com',
            'bob.jones@bbc.co.uk',
            'bob.jones.smythe@bbc.co.uk',
            'BoB@aol.com',
            'bobjones@nist.gov',
            "B.O'Callaghan@usmc.mil",
            'bob+jones@nic.us',
            'bob+jones@dailymail.co.uk',
            'bob@teaparty.uk.com',
            'bob@thelongestdomainnameintheworldandthensomeandthensomemoreandmore.com'
        ];

        if (extension_loaded('intl')) {
            $emailAddresses[] = 'иван@письмо.рф';
            $emailAddresses[] = 'xn--@-7sbfxdyelgv5j.xn--p1ai';
        }

        foreach ($emailAddresses as $input) {
            $this->assertTrue($validator->isValid($input), "$input failed to pass validation:\n"
                            . implode("\n", $validator->getMessages()));
        }
    }

    /**
     * @group ZF2-130
     */
    public function testUseMxRecordsBasicInvalid()
    {
        $validator = new EmailAddress([
            'useMxCheck'        => true,
            'useDeepMxCheck'    => true
        ]);

        $emailAddresses = [
            '',
            'bob

            @domain.com',
            'bob jones@domain.com',
            '.bobJones@studio24.com',
            'bobJones.@studio24.com',
            'bob.Jones.@studio24.com',
            '"bob%jones@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com',
            'bob+domain.com',
            'bob.domain.com',
            'bob @domain.com',
            'bob@ domain.com',
            'bob @ domain.com',
            'Abc..123@example.com'
            ];

        if (!extension_loaded('intl')) {
            $emailAddresses[] = 'иван@письмо.рф';
            $emailAddresses[] = 'xn--@-7sbfxdyelgv5j.xn--p1ai';
        }

        foreach ($emailAddresses as $input) {
            $this->assertFalse($validator->isValid($input), implode("\n", $this->validator->getMessages()) . $input);
        }
    }

    /**
     * @group ZF-12349
     */
    public function testReservedIpRangeValidation()
    {
        $validator = new TestAsset\EmailValidatorWithExposedIsReserved();
        // 0.0.0.0/8
        $this->assertTrue($validator->isReserved('0.0.0.0'));
        $this->assertTrue($validator->isReserved('0.255.255.255'));
        // 10.0.0.0/8
        $this->assertTrue($validator->isReserved('10.0.0.0'));
        $this->assertTrue($validator->isReserved('10.255.255.255'));
        // 127.0.0.0/8
        $this->assertTrue($validator->isReserved('127.0.0.0'));
        $this->assertTrue($validator->isReserved('127.255.255.255'));
        // 100.64.0.0/10
        $this->assertTrue($validator->isReserved('100.64.0.0'));
        $this->assertTrue($validator->isReserved('100.127.255.255'));
        // 172.16.0.0/12
        $this->assertTrue($validator->isReserved('172.16.0.0'));
        $this->assertTrue($validator->isReserved('172.31.255.255'));
        // 198.18.0.0./15
        $this->assertTrue($validator->isReserved('198.18.0.0'));
        $this->assertTrue($validator->isReserved('198.19.255.255'));
        // 169.254.0.0/16
        $this->assertTrue($validator->isReserved('169.254.0.0'));
        $this->assertTrue($validator->isReserved('169.254.255.255'));
        // 192.168.0.0/16
        $this->assertTrue($validator->isReserved('192.168.0.0'));
        $this->assertTrue($validator->isReserved('192.168.255.25'));
        // 192.0.2.0/24
        $this->assertTrue($validator->isReserved('192.0.2.0'));
        $this->assertTrue($validator->isReserved('192.0.2.255'));
        // 192.88.99.0/24
        $this->assertTrue($validator->isReserved('192.88.99.0'));
        $this->assertTrue($validator->isReserved('192.88.99.255'));
        // 198.51.100.0/24
        $this->assertTrue($validator->isReserved('198.51.100.0'));
        $this->assertTrue($validator->isReserved('198.51.100.255'));
        // 203.0.113.0/24
        $this->assertTrue($validator->isReserved('203.0.113.0'));
        $this->assertTrue($validator->isReserved('203.0.113.255'));
        // 224.0.0.0/4
        $this->assertTrue($validator->isReserved('224.0.0.0'));
        $this->assertTrue($validator->isReserved('239.255.255.255'));
        // 240.0.0.0/4
        $this->assertTrue($validator->isReserved('240.0.0.0'));
        $this->assertTrue($validator->isReserved('255.255.255.254'));
        // 255.255.255.255/32
        $this->assertTrue($validator->isReserved('255.255.55.255'));
    }

    /**
     * @group ZF-12349
     */
    public function testIpRangeValidationOnRangesNoLongerMarkedAsReserved()
    {
        $validator = new TestAsset\EmailValidatorWithExposedIsReserved();
        // 128.0.0.0/16
        $this->assertFalse($validator->isReserved('128.0.0.0'));
        $this->assertFalse($validator->isReserved('128.0.255.255'));
        // 191.255.0.0/16
        $this->assertFalse($validator->isReserved('191.255.0.0'));
        $this->assertFalse($validator->isReserved('191.255.255.255'));
        // 223.255.255.0/24
        $this->assertFalse($validator->isReserved('223.255.255.0'));
        $this->assertFalse($validator->isReserved('223.255.255.255'));
    }

    /**
     * @throws \PHPUnit_Framework_SkippedTestError
     *
     * @return void
     */
    private function skipIfOnlineTestsDisabled()
    {
        if (!getenv('TESTS_ZEND_VALIDATOR_ONLINE_ENABLED')) {
            $this->markTestSkipped('Testing MX records has been disabled');
        }
    }

    public function testCanSetDomainCheckFlag()
    {
        $validator = new EmailAddress();
        $validator->useDomainCheck(false);
        $this->assertFalse($validator->getDomainCheck());
    }
}
