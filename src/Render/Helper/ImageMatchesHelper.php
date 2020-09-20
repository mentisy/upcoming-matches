<?php

namespace Avolle\WeeklyMatches\Render\Helper;

use Avolle\WeeklyMatches\Match;
use Cake\Collection\CollectionInterface;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * Class ImageMatchesHelper
 *
 * @package Avolle\WeeklyMatches\Render\Helper
 */
class ImageMatchesHelper
{
    const DAY_FONT_SIZE = 18;
    const MATCH_FONT_SIZE = 14;

    const IMAGE_START_X = 5;
    const IMAGE_START_Y = 20;

    const DAY_SPACING_X = 10;
    const DAY_SPACING_Y = 30;

    const MATCH_SPACING_Y = 25;

    public float $requiredWidth = self::IMAGE_START_X + self::DAY_SPACING_X;
    public float $requiredHeight = 0;

    public float $maxStringWidth = 0;
    public float $maxStringWidthCurrent = 0;

    private CollectionInterface $matchesCollection;

    private Imagick $image;
    private ImagickDraw $dayText;
    private ImagickDraw $matchText;
    private ImagickDraw $shadowText;

    private int $x;
    private int $y;
    private int $imageWidth;
    private int $imageHeight;
    private int $allottedSpaceY;

    public function __construct(
        CollectionInterface $matchesCollection,
        int $imageWidth,
        int $imageHeight,
        int $allotedSpaceY
    ) {
        $this->matchesCollection = $matchesCollection;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->allottedSpaceY = $allotedSpaceY;

        $this->dayText = new ImagickDraw();
        $this->dayText->setFillColor(new ImagickPixel('#FFFFFF'));
        $this->dayText->setFontSize(self::DAY_FONT_SIZE);
        $this->dayText->setFont(FONTS . 'Roboto-Regular.ttf');

        $this->matchText = new ImagickDraw();
        $this->matchText->setFontSize(self::MATCH_FONT_SIZE);
        $this->matchText->setFillColor(new ImagickPixel("#FFFFFF"));

        $this->shadowText = new ImagickDraw();
        $this->shadowText->setFontSize(self::MATCH_FONT_SIZE);
        $this->shadowText->setFillColor(new ImagickPixel("rgb(132,132,132)"));

        $this->image = new Imagick();
        $this->image->newImage($imageWidth, $imageHeight, "#E81B22", 'png');
    }

    /*
     * Will render all days and their matches
     * Initiates the x and y coordinates
     *
     * Returns the rendered imge
     */
    public function renderMatches(): Imagick
    {
        $this->x = self::IMAGE_START_X;
        $this->y = self::IMAGE_START_Y;
        foreach ($this->matchesCollection as $day => $matches) {
            $this->renderDay($day, $matches);
            $this->y+= self::DAY_SPACING_Y;
            $this->incrementRequiredHeight(self::DAY_SPACING_Y);
        }

        return $this->image;
    }

    /*
     * Will render a day, including matches for that day, onto the canvas
     * Checks whether the whole day (with matches) will fit into the current column, otherwise start a new column
     */
    private function renderDay($day, $matches): void
    {
        if (!$this->willWholeDayFit(count($matches))) {
            $this->startNewColumn();
        }

        $this->image->annotateImage($this->dayText, $this->x, $this->y, 0, ucfirst($day));
        $this->y += 25; // Spacing between day header and the first match of the day
        $this->incrementRequiredHeight(25);
        foreach ($matches as $match) {
            $this->renderMatch($match);
        }
    }

    /*
     * Will render a match onto the canvas, using the current x and y coordinates.
     * Will also check for the longest string width, so that can be added to required width when cropping the image
     * Adds onto the required height property
     */
    private function renderMatch(Match $match): void
    {
        $text = sprintf("%s: %s - %s (%s)", $match->time, $match->homeTeam, $match->awayTeam, $match->tournament);
        $this->maxStringWidth = max([$this->maxStringWidth, (strlen($text) * self::MATCH_FONT_SIZE / 2)]);
        $this->maxStringWidthCurrent = max([$this->maxStringWidthCurrent, (strlen($text) * self::MATCH_FONT_SIZE / 2)]);

        $this->image->annotateImage($this->matchText, $this->x, $this->y, 0, $text);
        $this->y += self::MATCH_SPACING_Y;
        $this->incrementRequiredHeight(self::MATCH_SPACING_Y);
    }

    /*
     * Calculate whether the whole day will fit into the current column.
     * If not start a new column of days and increment the required width property
     */
    private function willWholeDayFit(int $matchCount): bool
    {
        $requiredPixels = $matchCount * self::MATCH_SPACING_Y;

        return $this->y + $requiredPixels <= $this->allottedSpaceY;
    }

    /*
     * Will start a new column in which the next days will be rendered onto
     * Adds onto the required width of the entire canvas
     */
    private function startNewColumn(): void
    {
        $this->x += self::DAY_SPACING_X + $this->maxStringWidthCurrent;
        $this->y = self::IMAGE_START_Y;
        $this->incrementRequiredWidth(self::DAY_SPACING_X + $this->maxStringWidthCurrent);
        $this->incrementRequiredHeight($this->y);
        $this->maxStringWidthCurrent = 0;
    }

    /*
     * Add onto the necessary width of the entire canvas
     * If the required width is larger than the image width,
     * override that that with the image width property
     */
    private function incrementRequiredWidth(int $increment): void
    {
        $this->requiredWidth += $increment;

        if ($this->requiredWidth > $this->imageWidth) {
            $this->requiredWidth = $this->imageWidth;
        }
    }

    /*
     * Add onto the necessary height of the entire canvas
     * If the required height is larger than the allotted space,
     * override that that with the allotted space property
     */
    private function incrementRequiredHeight(int $increment): void
    {
        $this->requiredHeight += $increment;

        if ($this->requiredHeight > $this->allottedSpaceY) {
            $this->requiredHeight = $this->allottedSpaceY;
        }
    }

    /**
     * Get the required image width, which is the required width + the width of the longest string
     *
     * @return float|int
     */
    public function getRequiredWidth()
    {
        return $this->requiredWidth + $this->maxStringWidth;
    }

    /**
     * Get the required image height
     *
     * @return float|int
     */
    public function getRequiredHeight()
    {
        return $this->requiredHeight;
    }
}
