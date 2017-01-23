<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityTrait;

class DummyWithWrongFields implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;

    private $field1;

    public function getId()
    {
        return 0;
    }

    public function getTranslatableFields()
    {
        return ['field1'];
    }

    public function setField1($value)
    {
        $this->field1 = $value;
    }

    public function getField1()
    {
        return $this->field1;
    }
}