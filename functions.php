<?php

function h(string $text): string
{
    $charset = mb_internal_encoding() ?: 'UTF-8';

    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset, true);
}
