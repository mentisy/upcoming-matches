<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render\Helper;

use Avolle\UpcomingMatches\SportConfig;
use Avolle\UpcomingMatches\Themes\Theme;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * Class ImageTeamDetails
 *
 * Prepares an Imagick instance of Team Details, to later be added to the complete image
 */
class ImageTeamDetails
{
    /**
     * Required height for the team details
     */
    public const REQUIRED_HEIGHT = 200;

    /**
     * Padding from edge of team details
     */
    public const PADDING = 40;

    /**
     * Position of logo in the X axis
     */
    public const LOGO_POSITION_X = 50;

    /**
     * Position of logo in the Y axis
     */
    public const LOGO_POSITION_Y = 100;

    /**
     * Font size of team name
     */
    public const TEAM_NAME_FONT_SIZE = 46;

    /**
     * Font size of sport name
     */
    public const SPORT_FONT_SIZE = 30;

    /**
     * Imagick instance
     *
     * @var \Imagick
     */
    public Imagick $imagick;

    /**
     * ImageTeamDetails constructor
     *
     * @param \Avolle\UpcomingMatches\SportConfig $sportConfig Sports Config
     * @param \Avolle\UpcomingMatches\Themes\Theme $theme Theme to use
     * @param int $imageWidth Width of complete image
     * @throws \ImagickDrawException|\ImagickException|\ImagickPixelException
     */
    public function __construct(SportConfig $sportConfig, Theme $theme, int $imageWidth)
    {
        $this->imagick = new Imagick();
        $this->imagick->newImage($imageWidth, $this->getRequiredHeight(), 'transparent');

        $this->addTextDetails($theme, $sportConfig);
        $this->addLogo($theme->logo, $imageWidth);
    }

    /**
     * Get required height
     *
     * @return int
     */
    public function getRequiredHeight(): int
    {
        return self::REQUIRED_HEIGHT;
    }

    /**
     * Get Imagick instance
     *
     * @return \Imagick
     */
    public function getImagick(): Imagick
    {
        return $this->imagick;
    }

    /**
     * Add logo to imagick instance
     *
     * @param string $logoImage Logo image file without path
     * @param int $imageWidth Width of complete image
     * @return void
     * @throws \ImagickException
     */
    private function addLogo(string $logoImage, int $imageWidth): void
    {
        $logo = new Imagick();
        $logo->readImage(RENDERABLES . $logoImage);
        $logo->resizeImage(128, 128, 0, 0);

        $logoPositionX = $imageWidth - 200;
        $logoPositionX = max([250, $logoPositionX]); // 250px is min X position to avoid hitting team name

        if ($imageWidth <= 500) {
            $logoPositionX += 30;
        }
        $this->imagick->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, $logoPositionX, self::PADDING);
    }

    /**
     * Add texts for the team details
     *
     * @param \Avolle\UpcomingMatches\Themes\Theme $theme Theme to use
     * @param \Avolle\UpcomingMatches\SportConfig $sportConfig Sports Config
     * @return void
     * @throws \ImagickDrawException|\ImagickException|\ImagickPixelException
     */
    private function addTextDetails(Theme $theme, SportConfig $sportConfig): void
    {
        $x = self::LOGO_POSITION_X;
        $y = self::LOGO_POSITION_Y;
        $teamText = new ImagickDraw();
        $teamText->setFont($theme->font);
        $teamText->setFontSize(self::TEAM_NAME_FONT_SIZE);
        $teamText->setFillColor(new ImagickPixel($theme->fontColor));

        $sportText = new ImagickDraw();
        $sportText->setFont($theme->font);
        $sportText->setFontSize(self::SPORT_FONT_SIZE);
        $sportText->setFillColor(new ImagickPixel($theme->fontColor));

        $subTitleY = $y + self::TEAM_NAME_FONT_SIZE;

        $this->imagick->annotateImage($teamText, $x, $y, 0, strtoupper($sportConfig->teamName));
        $this->imagick->annotateImage($sportText, $x, $subTitleY, 0, $sportConfig->renderSubTitle);
    }
}
