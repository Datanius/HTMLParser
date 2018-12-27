<?php

namespace HTMLParser;

class DOMElement
{
    /**
     * @var string
     */
    protected $tagName;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var DOMElement[]
     */
    protected $children = [];

    /**
     * @return string|null
     */
    public function getTagName() : ?string
    {
        return $this->tagName;
    }

    /**
     * @param string $identifier
     * @return string|null
     */
    public function getAttribute(string $identifier) : ?string
    {
        return $this->attributes[$identifier] ?? NULL;
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @param string $tagName
     */
    public function setTagName(string $tagName): void
    {
        $this->tagName = $tagName;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param DOMElement[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @param string|NULL $selector
     * @return DOMElement[]
     */
    public function find(string $selector = NULL) : array
    {
        return $this->getChildren($selector);
    }

    /**
     * @param DOMElement[] $DOMElements
     * @param string|NULL $selector
     * @return DOMElement[]
     */
    public function findInElements(array $DOMElements, string $selector = NULL): array
    {
        $elements = [];
        foreach($DOMElements as $DOMElement) {
            $elements = array_merge($elements, $DOMElement->find($selector));
        }
        return $elements;
    }

    /**
     * @param string $selector
     * @return DOMElement[]
     */
    public function getChildren(string $selector = NULL) : array
    {
        if(!isset($selector)) {
            return $this->children;
        }
        $selector = trim($selector);
        $selectorArr = explode(" ", $selector, 2);
        if(count($selectorArr) > 1) {
            $selector = $selectorArr[0];
        }
        if(General::contains($selector, ".")) {
            $data = explode(".", $selector, 2);
            $children =  $this->getChildrenByClass($data[1], empty($data[0]) ? NULL : $data[0]);
        } elseif(General::contains($selector, "#")) {
            $data = explode("#", $selector, 2);
            $children = $this->getChildrenByAttribute("id", $data[1], empty($data[0]) ? NULL : $data[0]);
        } else {
            $children = $this->getChildrenByTag($selector);
        }
        if(empty($children) && !empty($this->getChildren())) {
            $elements = $this->findInElements($this->getChildren(), $selector);
        } else {
            $elements = $children;
        }
        if(count($selectorArr) > 1) {
            $elements = $this->findInElements($elements, $selectorArr[1]);
        }
        return $elements;
    }

    /**
     * @param int $index
     * @return DOMElement
     */
    public function getNthChild(int $index) : DOMElement
    {
        return $this->children[$index - 1];
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        $text = "";
        foreach($this->getChildren() as $child) {
            $text .= $child->getText();
        }
        return $text;
    }

    /**
     * @param DOMElement $child
     */
    protected function addChild(DOMElement $child) : void
    {
        $this->children[] = $child;
    }

    /**
     * @param string $class
     * @param string $tagName
     * @return DOMElement[]
     */
    private function getChildrenByClass(string $class, string $tagName = NULL) : array
    {
        $children = [];
        foreach($this->getChildren() as $DOMElement)
        {
            $classAttr = $DOMElement->getAttribute("class");
            if(!isset($classAttr) || (isset($tagName) && $DOMElement->getTagName() !== $tagName)) {
                continue;
            }
            if(in_array($class, explode(" ", $classAttr))) {
                $children[] = $DOMElement;
            }
        }
        return $children;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @param string $tagName
     * @return DOMElement[]
     */
    private function getChildrenByAttribute(string $attribute, string $value, string $tagName = NULL) : array
    {
        $children = [];
        foreach($this->getChildren() as $DOMElement)
        {
            if(isset($tagName) && $DOMElement->getTagName() !== $tagName) {
                continue;
            }
            if($DOMElement->getAttribute($attribute) === $value) {
                $children[] = $DOMElement;
            }
        }
        return $children;
    }

    /**
     * @param string $tag
     * @return DOMElement[]
     */
    private function getChildrenByTag(string $tag) : array
    {
        $children = [];
        foreach($this->getChildren() as $DOMElement)
        {
            if($DOMElement->getTagName() === $tag) {
                $children[] = $DOMElement;
            }
        }
        return $children;
    }
}