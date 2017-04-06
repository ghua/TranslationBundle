<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\LazyTranslatableTrait;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityTrait;

class DummyLazy implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;
    use LazyTranslatableTrait;

    private $id = 1;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTranslatableFields()
    {
        return ['field1', 'field2'];
    }

}
