<?php

namespace HTMLParser;

class DOM
{
    /**
     * @var DOMElement[]
     */
    protected $elements;

    /**
     * DOM constructor.
     * @param string|NULL $content
     */
    public function __construct(string $content = NULL)
    {
        if(isset($content)) {
            $this->parse($content);
        }
    }

    /**
     * @param string $content
     */
    public function parse(string $content): void
    {
        if (is_file($content)) {
            $content = file_get_contents($content);
        }
        $content = $this->removeDOCTYPE($content);
        $content = $this->removeComments($content);
        $this->loadElements($content);
    }

    /**
     * @param string $selector
     * @return DOMElement[]
     */
    public function find(string $selector = NULL): array
    {
        $DOMElement = new DOMElement();
        $DOMElement->setChildren($this->elements);
        return $DOMElement->getChildren($selector);
    }

    /**
     * @param string $HTMLContent
     * @return string
     */
    private function removeDOCTYPE(string $HTMLContent): string
    {
        return preg_replace("/<!DOCTYPE.*>/U", "", $HTMLContent);
    }

    /**
     * @param string $HTMLContent
     * @return string
     */
    private function removeComments(string $HTMLContent): string
    {
        return preg_replace("/<!--.*-->/Us", "", $HTMLContent);
    }

    /**
     * @param string $HTMLContent
     */
    private function loadElements(string $HTMLContent): void
    {
        $this->elements = $this->getFirstLevelElements($HTMLContent);
    }

    /**
     * @param string $HTMLContent
     * @return DOMElement[]
     */
    public function getFirstLevelElements(string $HTMLContent): array
    {
        $HTMLContent = $this->removeComments($HTMLContent);
        $elements = [];
        $pattern = "/<([^\/]\w*?)(.*)>/Uis";
        preg_match_all($pattern, $HTMLContent, $matches);
        if (empty($matches[1]) && strlen(trim($HTMLContent)) > 0) {
            $elements[] = new TextElement($HTMLContent);
        }
        while (!empty($matches[1])) {
            $tagName = $matches[1][0];
            $attributeString = $matches[2][0];
            $isSelfEnclosing = General::endsWith(trim($attributeString), "/");
            $startPos = strpos($HTMLContent, "<{$tagName}");
            if ($startPos > 0 && strlen(trim(substr($HTMLContent, 0, $startPos))) > 0) {
                $elements[] = new TextElement(substr($HTMLContent, 0, $startPos));
            }
            $DOMElement = new DOMElement();
            $DOMElement->setAttributes($this->extractAttributes($attributeString));
            $DOMElement->setTagName($tagName);
            if ($isSelfEnclosing) {
                $elements[] = $DOMElement;
                $pos = $startPos + strlen($attributeString) + 1;
            } else {
                $pos = $this->findEndTagPosition($HTMLContent, $tagName);
                $startPos = $startPos + strlen("<{$tagName} ") + strlen($attributeString);
                $elementStr = substr($HTMLContent, $startPos, ($pos - $startPos) - strlen("</{$tagName}>"));
                $firstLevelElements = $this->getFirstLevelElements($elementStr);
                $DOMElement->setChildren($firstLevelElements);
                $elements[] = $DOMElement;
            }

            $HTMLContent = substr($HTMLContent, $pos);
            preg_match_all($pattern, $HTMLContent, $matches);
            if (empty($matches[1]) && strlen(trim($HTMLContent)) > 0) {
                $elements[] = new TextElement($HTMLContent);
            }
        }
        return $elements;
    }

    /**
     * @param string $HTMLContent
     * @param string $tag
     * @return int
     */
    private function findEndTagPosition(string $HTMLContent, string $tag): int
    {
        $isEnd = false;
        $openTagCount = substr_count($HTMLContent, "<{$tag}");
        $closeTagCount = substr_count($HTMLContent, "</{$tag}");
        for ($i = 1; !$isEnd; $i++) {
            $pos = General::findPosOfXOccurrence($HTMLContent, "</{$tag}>", $i);
            $anzahl = substr_count(substr($HTMLContent, 0, $pos), "<{$tag}");
            $closedOpenDiff = ($anzahl - substr_count(substr($HTMLContent, 0, $pos), "</{$tag}>"));
            $isEnd = ($closedOpenDiff === 0);
            if ($openTagCount > $closeTagCount && !$isEnd) {
                break;
            }
        }
        if ($openTagCount > $closeTagCount) {
            return General::findPosOfXOccurrence($HTMLContent, "<{$tag}", 2) - strlen("<{$tag}");
        }
        return General::findPosOfXOccurrence($HTMLContent, "</{$tag}>", $i - 1);
    }

    /**
     * @param string $attributeString
     * @return array
     */
    private function extractAttributes(string $attributeString = NULL): array
    {
        if (!isset($attributeString)) {
            return [];
        }
        $pattern = '/([-\w]+\s?=\s?".*[^\\\]"\s?)/Ums';
        preg_match_all($pattern, trim($attributeString), $matches);
        $attributes = [];
        foreach ($matches[1] as $match) {
            $attrArr = explode("=", $match, 2);
            $attributes[trim($attrArr[0])] = trim(trim(trim($attrArr[1]), "\""));
        }
        return $attributes;
    }
}