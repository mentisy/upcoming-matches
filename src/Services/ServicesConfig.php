<?php

namespace Avolle\WeeklyMatches\Services;

class ServicesConfig
{
    /*
     * The API URI endpoint in which the matches can be found
     */
    public string $apiUri;

    /**
     * The API endpoint will require you to pass along which period you want matches for (from-to)
     * This property describes what these query fields are called. These fields are then tacked onto the request URI
     *
     * E.g. The API endpoint query fields are `periodFrom` for the from field and `periodTo` for the to field
     * You will then pass along: ['fromDate' => 'periodFrom', 'toDate' => 'periodTo']
     */
    public array $dateFields;

    /*
     * Extra parameters that should be tacked onto the request URI. E.g. API token, required parameters like clubId etc.
     */
    public array $params;

    /*
     * ServicesConfig constructor.
     */
    public function __construct(string $url, array $dateFields, array $params = [])
    {
        $this->apiUri = $url;
        $this->params = $params;
        $this->dateFields = $dateFields;
    }
}
