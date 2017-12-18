<?php
/**
 * Created by anonymous
 * Date: 16/12/17
 * Time: 10:16
 */

namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Services\Options;

/**
 * Class OptionsTest
 */
class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->options = new Options();
    }

    /**
     * @return void
     */
    public function testSetForcedSaveReturnSelf()
    {
        $this->assertEquals($this->options, $this->options->setForcedSave(false));
        $this->assertEquals($this->options, $this->options->setForcedSave(true));
    }

    /**
     * @return void
     */
    public function testIsForcedSaveReturnFalse()
    {
        $this->assertEquals(false, $this->options->isForcedSave());
        $this->assertFalse($this->options->isForcedSave());
    }

    /**
     * @return void
     */
    public function testIsForcedSaveReturnTrue()
    {
        $reflectionClass = new \ReflectionClass(get_class($this->options));
        $property = $reflectionClass->getProperty('forcedSave');
        $property->setAccessible(true);
        $property->setValue($this->options, true);

        $this->assertEquals(true, $this->options->isForcedSave());
        $this->assertTrue($this->options->isForcedSave());
    }

    /**
     * @return void
     */
    public function testSetForcedSaveByGoogleReturnSelf()
    {
        $this->assertEquals($this->options, $this->options->setForcedSaveByGoogle(false));
        $this->assertEquals($this->options, $this->options->setForcedSaveByGoogle(true));
    }

    /**
     * @return void
     */
    public function testIsForcedSaveByGoogleReturnFalse()
    {
        $this->assertEquals(false, $this->options->isForcedSaveByGoogle());
        $this->assertFalse($this->options->isForcedSaveByGoogle());
    }

    /**
     * @return void
     */
    public function testIsForcedSaveByGoogleReturnTrue()
    {
        $reflectionClass = new \ReflectionClass(get_class($this->options));
        $property = $reflectionClass->getProperty('forcedSaveByGoogle');
        $property->setAccessible(true);
        $property->setValue($this->options, true);

        $this->assertEquals(true, $this->options->isForcedSaveByGoogle());
        $this->assertTrue($this->options->isForcedSaveByGoogle());
    }

    /**
     * @return array
     */
    public function fieldsToTranslateProvider()
    {
        return [
            [[]],
            [['foo']],
            [['foo', 'bar', 'baz']],
            [['foo', 'bar', 'baz', 'qux']],
        ];
    }

    /**
     * @dataProvider fieldsToTranslateProvider
     *
     * @param array $fields
     *
     * @return void
     */
    public function testSetFieldsToTranslateReturnArray(array $fields)
    {
        $this->assertEquals($this->options, $this->options->setFieldsToTranslate($fields));
    }

    /**
     * @dataProvider fieldsToTranslateProvider
     *
     * @param array $fields
     *
     * @return void
     */
    public function testGetFieldsToTranslateReturnArray(array $fields)
    {
        $reflectionClass = new \ReflectionClass(get_class($this->options));
        $property = $reflectionClass->getProperty('fieldsToTranslate');
        $property->setAccessible(true);
        $property->setValue($this->options, $fields);

        $this->assertEquals($fields, $this->options->getFieldsToTranslate());
    }
}
