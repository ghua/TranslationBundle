<?php
namespace VKR\TranslationBundle\Tests\Services;

use Google\Cloud\Translate\TranslateClient;
use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Decorators\GoogleClientDecorator;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithoutFields;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithoutFieldsTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithWrongFields;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithWrongFieldsTranslations;

class GoogleTranslationDriverTest extends TestCase
{
    const GOOGLE_API_KEY = '';

    private $shouldErrorOut = false;

    private $translations = [
        'field1' => [
            'en' => 'Dog',
            'de' => 'Hund',
        ],
        'field2' => [
            'en' => 'Cat',
            'de' => 'Katz',
        ]
    ];

    /**
     * @var GoogleTranslationDriver
     */
    private $googleTranslationDriver;

    public function setUp()
    {
        $translationClassChecker = $this->mockTranslationClassChecker();
        $googleClientDecorator = $this->mockGoogleClientDecorator();
        $this->googleTranslationDriver = new GoogleTranslationDriver(
            $translationClassChecker, $googleClientDecorator, self::GOOGLE_API_KEY
        );
    }

    public function testWithTwoFields()
    {
        $english = new DummyLanguageEntity();
        $english->setCode('en_US');
        $target = 'de_DE';
        $record = new Dummy();
        $value = new DummyTranslations();
        $value
            ->setLanguage($english)
            ->setField1('Dog')
            ->setField2('Cat')
        ;
        /** @var DummyTranslations|null $translation */
        $translation = $this->googleTranslationDriver->getTranslation($record, $target, $value);
        $this->assertInstanceOf(DummyTranslations::class, $translation);
        $this->assertEquals('Hund', $translation->getField1());
        $this->assertEquals('Katz', $translation->getField2());
    }

    public function testWithoutFields()
    {
        $english = new DummyLanguageEntity();
        $english->setCode('en_US');
        $target = 'de_DE';
        $record = new DummyWithoutFields();
        $value = new DummyWithoutFieldsTranslations();
        $value->setLanguage($english);
        $translation = $this->googleTranslationDriver->getTranslation($record, $target, $value);
        $this->assertInstanceOf(DummyWithoutFieldsTranslations::class, $translation);
    }

    public function testWithoutGetter()
    {
        $english = new DummyLanguageEntity();
        $english->setCode('en_US');
        $target = 'de_DE';
        $record = new DummyWithWrongFields();
        $value = new DummyWithWrongFieldsTranslations();
        $value->setLanguage($english);
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Method getField1 not found in class ' . DummyWithWrongFieldsTranslations::class);
        $this->googleTranslationDriver->getTranslation($record, $target, $value);
    }

    public function testWithAPIError()
    {
        $this->shouldErrorOut = true;
        $english = new DummyLanguageEntity();
        $english->setCode('en_US');
        $target = 'de_DE';
        $record = new Dummy();
        $value = new DummyTranslations();
        $value
            ->setLanguage($english)
            ->setField1('Dog')
            ->setField2('Cat')
        ;
        $this->expectException(GoogleTranslationException::class);
        $this->expectExceptionMessage('Error from Google Translate API');
        $this->googleTranslationDriver->getTranslation($record, $target, $value);
    }

    private function mockTranslationClassChecker()
    {
        $translationClassChecker = $this->createMock(TranslationClassChecker::class);
        $translationClassChecker->method('checkTranslationClass')
            ->willReturnCallback([$this, 'checkTranslationClassCallback']);
        return $translationClassChecker;
    }

    private function mockGoogleClientDecorator()
    {
        $googleClientDecorator = $this->createMock(GoogleClientDecorator::class);
        $googleClientDecorator->method('createClient')->willReturn($this->mockGoogleClient());
        return $googleClientDecorator;
    }

    private function mockGoogleClient()
    {
        $googleClient = $this->createMock(TranslateClient::class);
        $googleClient->method('translate')->willReturnCallback([$this, 'translateCallback']);
        return $googleClient;
    }

    public function checkTranslationClassCallback($entity)
    {
        $className = get_class($entity);
        return $className . 'Translations';
    }

    public function translateCallback($value, array $options)
    {
        if ($this->shouldErrorOut) {
            throw new \Exception();
        }
        $source = $options['source'];
        $target = $options['target'];
        foreach ($this->translations as $translation) {
            if ($translation[$source] == $value) {
                return ['text' => $translation[$target]];
            }
        }
        return '';
    }
}
