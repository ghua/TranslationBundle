<?php

namespace Acme\Entity;

use VKR\TranslationBundle\Entity\LanguageEntityInterface;

class Language implements LanguageEntityInterface
{

    protected $id;

    protected $code;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     *
     * @return $this;
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

}