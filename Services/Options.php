<?php
/**
 * Created by anonymous
 * Date: 14/12/17
 * Time: 12:53
 */

namespace VKR\TranslationBundle\Services;

/**
 * Class Options
 */
class Options
{
    /**
     * @var bool
     */
    protected $forcedSave = false;

    /**
     * @var array
     */
    protected $fieldsToTranslate = [];

    /**
     * @return bool
     */
    public function isForcedSave()
    {
        return $this->forcedSave;
    }

    /**
     * @param bool $forcedSave
     */
    public function setForcedSave($forcedSave)
    {
        $this->forcedSave = $forcedSave;
    }

    /**
     * @return array
     */
    public function getFieldsToTranslate()
    {
        return $this->fieldsToTranslate;
    }

    /**
     * @param array $fieldsToTranslate
     */
    public function setFieldsToTranslate($fieldsToTranslate)
    {
        $this->fieldsToTranslate = $fieldsToTranslate;
    }
}
