<?php
namespace VKR\TranslationBundle\TestHelpers\Entity;

use VKR\TranslationBundle\Entity\LazyTranslatableTrait;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityTrait;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class EmailTemplateEntity implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;
    use LazyTranslatableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $html;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return array
     */
    public function getTranslatableFields()
    {
        return ['subject', 'html'];
    }
}