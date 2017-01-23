<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityTrait;

class DummyWithFallback implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;

    private $slug;

    private $name;

    public $translatableFields = [];

    public function getId()
    {
        return 0;
    }

    public function getTranslatableFields()
    {
        return $this->translatableFields;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTranslationFallback($field = '')
    {
        if ($field == 'name') {
            return $this->slug;
        }
        return '';
    }

}
