<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

class GoogleTranslatableDummyTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;

    private $field1;
    private $field2;

    public function setField1($field1)
    {
        $this->field1 = $field1;
        return $this;
    }


    public function getField1()
    {
        return $this->field1;
    }

    public function setField2($field2)
    {
        $this->field2 = $field2;
        return $this;
    }

    public function getField2()
    {
        return $this->field2;
    }

    public function setField3($field3)
    {
        // nothing here
    }
}
