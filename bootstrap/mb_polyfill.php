<?php

if (! function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        $delimiter = '/'.str_replace('/', '\/', $pattern).'/u';

        return preg_split($delimiter, $string, $limit);
    }
}
