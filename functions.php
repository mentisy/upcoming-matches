<?php

function h(string $text): string
{
    return htmlspecialchars($text);
}