<?php

namespace HTMLParser;

class TextElement extends DOMElement
{
    protected $tagName = "text";
    protected $text;

    /**
     * TextElement constructor.
     * @param string|NULL $text
     */
    public function __construct(string $text = NULL)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text) : void
    {
        $this->text = $text;
    }
}