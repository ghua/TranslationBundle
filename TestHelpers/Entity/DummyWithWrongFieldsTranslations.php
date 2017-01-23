<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

class DummyWithWrongFieldsTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;

    private $field2;

    public function setField2($value)
    {
        $this->field2 = $value;
    }

    public function getField2()
    {
        return $this->field2;
    }
}