<?php
namespace Actinity\Actinite\Services;

class Parser
{
    public static function output(string $content)
    {
        preg_match_all('!cms://node/([0-9]+)!',$content,$matches);
        foreach($matches[1] as $idx => $node_id) {
            $node = app('Actinite')->get($node_id);
            if($node) {
                $content = str_replace(
                    $matches[0][$idx],
                    $node->url,
                    $content
                );
            }
        }

        return $content;
    }
}
