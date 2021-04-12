<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render;

use Avolle\UpcomingMatches\SportConfig;
use Cake\Collection\CollectionInterface;

interface RenderInterface
{
    /**
     * RenderInterface constructor.
     *
     * @param \Cake\Collection\CollectionInterface $matches Matches to render
     * @param \Avolle\UpcomingMatches\SportConfig $sportConfig The sport's config to retrive relevant info about
     */
    public function __construct(CollectionInterface $matches, SportConfig $sportConfig);

    /**
     * Will perform the rendering and readies it for output
     *
     * @return void
     */
    public function render(): void;

    /**
     * Output method. Will output the render
     */
    public function output(): void;
}
