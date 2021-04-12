<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render;

use Avolle\UpcomingMatches\SportConfig;
use Cake\Collection\CollectionInterface;

class HtmlRender implements RenderInterface
{
    /**
     * @var \Cake\Collection\CollectionInterface
     */
    private CollectionInterface $matchesCollection;

    public function __construct(CollectionInterface $matchesCollection, SportConfig $sportConfig)
    {
        $this->matchesCollection = $matchesCollection;
    }

    public function render(): void
    {
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function output(): void
    {
        $matchesCollection = $this->matchesCollection;
        require TEMPLATES . 'html.php';
    }
}
