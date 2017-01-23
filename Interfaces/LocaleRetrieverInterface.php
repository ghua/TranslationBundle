<?php
namespace VKR\TranslationBundle\Interfaces;

interface LocaleRetrieverInterface
{
    /**
     * @return string|null
     */
    public function getCurrentLocale();

    /**
     * @return string|null
     */
    public function getDefaultLocale();
}
