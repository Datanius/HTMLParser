<?php

namespace HTMLParser;

class General
{
    /**
     * @param string $content
     * @param string $begin
     * @return bool
     */
    public static function beginsWith(string $content, string $begin): bool
    {
        return substr($content, 0, strlen($begin)) === $begin;
    }

    /**
     * @param string $content
     * @param string $end
     * @return bool
     */
    public static function endsWith(string $content, string $end): bool
    {
        return substr($content, strlen($content) - strlen($end)) === $end;
    }

    /**
     * @param string $content
     * @param string $toRemove
     * @return string
     */
    public static function lremove(string $content, string $toRemove): string
    {
        if(self::beginsWith($content, $toRemove)) {
            $content = substr($content, strlen($toRemove));
        }
        return $content;
    }

    /**
     * @param string $content
     * @param string $text
     * @return bool
     */
    public static function contains(string $content, string $text): bool
    {
        return (bool) strstr($content, $text);
    }

    /**
     * @param string $content
     * @param string $text
     * @param int $x
     * @return int
     */
    public static function findPosOfXOccurrence(string $content, string $text, int $x): int
    {
        $pos = 0;
        for($i = 0; $i < $x; $i++)
        {
            $newContentPos = $pos;
            $pos += strpos(substr($content, $newContentPos), $text) + strlen($text);
        }
        return $pos;
    }

    /**
     * @param array $replacements
     * @param string $content
     * @return string
     */
    public static function replace(array $replacements, string $content): string
    {
        foreach($replacements as $key => $replacement) {
            $content = str_replace("{{".$key."}}", $replacement, $content);
        }
        return $content;
    }

}