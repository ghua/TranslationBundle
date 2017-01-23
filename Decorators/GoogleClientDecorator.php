<?php
namespace VKR\TranslationBundle\Decorators;

use Google\Cloud\Translate\TranslateClient;

class GoogleClientDecorator
{
    /**
     * @param string $apiKey
     * @return TranslateClient
     */
    public function createClient($apiKey)
    {
        return new TranslateClient([
            'key' => $apiKey,
        ]);
    }
}
