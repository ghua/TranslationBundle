<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

class DummyWithArgsTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;

    public function __construct($foo)
    {

    }
}
