<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

class DummyWithoutFieldsTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;
}
