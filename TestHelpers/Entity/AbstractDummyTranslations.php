<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityTrait;

abstract class AbstractDummyTranslations implements TranslationEntityInterface
{
    use TranslationEntityTrait;
}
