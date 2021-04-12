<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Themes;

/**
 * Class Theme
 *
 * @package Avolle\UpcomingMatches\Themes
 * @property string $backgroundColor
 * @property string $fontColor
 * @property string $logo
 * @property string $sponsors
 * @property bool $singleColumn
 */
class Theme
{
    /**
     * @var string
     */
    public string $backgroundColor = '#C8271A';

    /**
     * @var string
     */
    public string $fontColor = '#FFFFFF';

    /**
     * @var string
     */
    public string $logo = 'team-logo-512.png';

    /**
     * @var null|string
     */
    public ?string $sponsors = 'sponsors.png';

    /**
     * @var bool
     */
    public bool $singleColumn = false;

    /**
     * @var string
     */
    public string $font = FONTS . 'bahnschrift.ttf';
}
