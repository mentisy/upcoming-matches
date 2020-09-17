<?php

namespace Avolle\WeeklyMatches\Render;

use Cake\Collection\CollectionInterface;

class HtmlRender implements RenderInterface
{
    /**
     * @var \Cake\Collection\CollectionInterface
     */
    private CollectionInterface $matchesCollection;

    public function __construct(CollectionInterface $matchesCollection)
    {
        $this->matchesCollection = $matchesCollection;
    }

    public function output(): void
    {
        $matchesCollection = $this->matchesCollection;
        require TEMPLATES . 'html.php';
    }
}