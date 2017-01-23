<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

class DummyWithFallbackTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;

    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFoo()
    {
        return '';
    }
}
