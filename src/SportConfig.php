<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches;

use Avolle\UpcomingMatches\Exception\InvalidFilterableException;
use Avolle\UpcomingMatches\Filterable\FilterableInterface;
use Avolle\UpcomingMatches\Services\ServicesConfig;

class SportConfig
{
    public string $sport;
    public string $teamName;
    public string $renderSubTitle;
    public ServicesConfig $serviceConfig;
    public ?string $filterable;

    public function __construct(
        string $sport,
        string $teamName,
        string $renderSubTitle,
        ServicesConfig $serviceConfig,
        ?string $filterable = null
    ) {
        $this->sport = $sport;
        $this->teamName = $teamName;
        $this->renderSubTitle = $renderSubTitle;
        $this->serviceConfig = $serviceConfig;
        $this->filterable = $filterable;
    }

    /**
     * Get a filterable class to use for filtering matches. Returns null if no filter is set
     *
     * @return \Avolle\UpcomingMatches\Filterable\FilterableInterface|null
     * @throws \Avolle\UpcomingMatches\Exception\InvalidFilterableException
     */
    public function getFilterableClass(): ?FilterableInterface
    {
        if (!isset($this->filterable)) {
            return null;
        }

        $filterClassName = $this->filterable;
        if (!class_exists($filterClassName)) {
            throw new InvalidFilterableException($filterClassName . ' is not a valid filter class.');
        }

        $filterClass = new $filterClassName();
        if (!$filterClass instanceof FilterableInterface) {
            throw new InvalidFilterableException($filterClassName . ' must implement ' . FilterableInterface::class);
        }

        return new $filterClassName();
    }
}
