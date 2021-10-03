<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render\Helper;

use Avolle\UpcomingMatches\Match;
use Avolle\UpcomingMatches\Themes\Theme;
use Cake\Collection\CollectionInterface;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * Class ImageMatchesHelper
 *
 * @package Avolle\UpcomingMatches\Render\Helper
 */
class ImageMatchesHelper
{
    /**
     * Font size of Day text
     */
    public const DAY_FONT_SIZE = 18;

    /**
     * Font size of Match text
     */
    public const MATCH_FONT_SIZE = 14;

    /**
     * First position of added details in X axis
     */
    public const IMAGE_START_X = 5;

    /**
     * First position of added details in Y axix
     */
    public const IMAGE_START_Y = 20;

    /**
     * Spacing between days in the X axis
     */
    public const DAY_SPACING_X = 30;

    /**
     * Spacing between days in the Y axis
     */
    public const DAY_SPACING_Y = 30;

    /**
     * Spacing between matches in the Y axis
     */
    public const MATCH_SPACING_Y = 25;

    /**
     * Required width of complete image
     *
     * @var int
     */
    public int $requiredWidth = self::IMAGE_START_X + self::DAY_SPACING_X;

    /**
     * Width of the widest string
     *
     * @var float
     */
    public float $maxStringWidth = 0;

    /**
     * Width of the widest string on the current column of matches
     *
     * @var float
     */
    public float $maxStringWidthCurrent = 0;

    /**
     * Collection of Matches
     *
     * @var \Cake\Collection\CollectionInterface
     */
    private CollectionInterface $matchesCollection;

    /**
     * Theme class
     *
     * @var \Avolle\UpcomingMatches\Themes\Theme
     */
    private Theme $theme;

    /**
     * Imagick instance of complete image
     *
     * @var \Imagick
     */
    private Imagick $image;

    /**
     * Day Text ImagickDraw instance
     *
     * @var \ImagickDraw
     */
    private ImagickDraw $dayText;

    /**
     * Match Text ImagickDraw instance
     *
     * @var \ImagickDraw
     */
    private ImagickDraw $matchText;

    /**
     * Day Shadow Text ImagickDraw instance
     *
     * @var \ImagickDraw
     */
    private ImagickDraw $dayShadowText;

    /**
     * Match Shadow Text ImagickDraw instance
     *
     * @var \ImagickDraw
     */
    private ImagickDraw $matchShadowText;

    /**
     * Current position of X axis to render next detail
     *
     * @var int
     */
    private int $x;

    /**
     * Current position of Y axis to render next detail
     *
     * @var int
     */
    private int $y;

    /**
     * Count of columns on the current render
     *
     * @var int
     */
    private int $columns = 1;

    /**
     * Height of the highest column
     *
     * @var int
     */
    private int $maxColumnHeight;

    /**
     * Width of the widest string on the current column of matches
     * Height of the highest column
     *
     * @var int
     */
    private int $maxRequiredColumnHeight = 0;

    /**
     * Height of the current column rendering
     *
     * @var int
     */
    private int $maxRequiredColumnHeightCurrent = 0;

    /**
     * ImageMatchesHelper constructor
     *
     * @param \Cake\Collection\CollectionInterface $matchesCollection Collection of matches
     * @param \Avolle\UpcomingMatches\Themes\Theme $theme Theme
     * @param int $imageWidth Width of image
     * @param int $imageHeight Height of image
     * @param int $maxColumnHeight Max height of column before starting a new column
     * @throws \ImagickDrawException|\ImagickException|\ImagickPixelException
     */
    public function __construct(
        CollectionInterface $matchesCollection,
        Theme $theme,
        int $imageWidth,
        int $imageHeight,
        int $maxColumnHeight
    ) {
        $this->matchesCollection = $matchesCollection;
        $this->maxColumnHeight = $maxColumnHeight;
        $this->theme = $theme;

        $this->dayText = new ImagickDraw();
        $this->dayText->setFont($this->theme->font);
        $this->dayText->setFillColor(new ImagickPixel($this->theme->fontColor));
        $this->dayText->setFontWeight(600);
        $this->dayText->setFontSize(self::DAY_FONT_SIZE);

        $this->dayShadowText = new ImagickDraw();
        $this->dayShadowText->setFont($this->theme->font);
        $this->dayShadowText->setFontSize(self::DAY_FONT_SIZE);
        $this->dayShadowText->setFontWeight(600);
        $this->dayShadowText->setFillColor(new ImagickPixel('rgb(50,50,50'));

        $this->matchText = new ImagickDraw();
        $this->matchText->setFont($this->theme->font);
        $this->matchText->setFontSize(self::MATCH_FONT_SIZE);
        $this->matchText->setFontWeight(600);
        $this->matchText->setFillColor(new ImagickPixel($this->theme->fontColor));

        $this->matchShadowText = new ImagickDraw();
        $this->matchShadowText->setFont($this->theme->font);
        $this->matchShadowText->setFontSize(self::MATCH_FONT_SIZE);
        $this->matchShadowText->setFontWeight(600);
        $this->matchShadowText->setFillColor(new ImagickPixel("rgb(50,50,50)"));

        $this->image = new Imagick();
        $this->image->newImage($imageWidth, $imageHeight, $this->theme->backgroundColor, 'png');
    }

    /**
     * Will render all days and their matches
     * Initiates the x and y coordinates
     *
     * Returns the rendered imge
     *
     * @throws \ImagickException
     */
    public function renderMatches(): Imagick
    {
        $this->x = self::IMAGE_START_X;
        $this->y = self::IMAGE_START_Y;
        foreach ($this->matchesCollection as $day => $matches) {
            $this->renderDay($day, $matches);
            $this->y += self::DAY_SPACING_Y;
            $this->maxRequiredColumnHeightCurrent += self::DAY_SPACING_Y;
        }
        $this->maxRequiredColumnHeight = max($this->maxRequiredColumnHeight, $this->maxRequiredColumnHeightCurrent);

        return $this->image;
    }

    /**
     * Will render a day, including matches for that day, onto the canvas
     * Checks whether the whole day (with matches) will fit into the current column, otherwise start a new column
     *
     * @param string $day Day name
     * @param array $matches Matches collection of current day
     * @throws \ImagickException
     */
    private function renderDay(string $day, array $matches): void
    {
        if (!$this->willWholeDayFit(count($matches)) && !$this->theme->singleColumn) {
            $this->startNewColumn();
        }

        $this->image->annotateImage($this->dayShadowText, $this->x + 1, $this->y + 1, 0, ucfirst($day));
        $this->image->annotateImage($this->dayText, $this->x, $this->y, 0, ucfirst($day));
        $this->y += 25; // Spacing between day header and the first match of the day
        $this->maxRequiredColumnHeightCurrent += 25;
        foreach ($matches as $match) {
            $this->renderMatch($match);
        }
    }

    /**
     * Will render a match onto the canvas, using the current x and y coordinates.
     * Will also check for the longest string width, so that can be added to required width when cropping the image
     * Adds onto the required height property
     *
     * @param \Avolle\UpcomingMatches\Match $match Match to render
     * @throws \ImagickException
     */
    private function renderMatch(Match $match): void
    {
        $text = sprintf(
            "%s: %s - %s (%s - %s)",
            $match->time,
            $match->homeTeam,
            $match->awayTeam,
            $match->tournament,
            $match->pitch,
        );
        $this->maxStringWidth = max([$this->maxStringWidth, $this->calculateStringWidth($text)]);
        $this->maxStringWidthCurrent = max([$this->maxStringWidthCurrent, $this->calculateStringWidth($text)]);
        $this->maxRequiredColumnHeightCurrent += self::MATCH_SPACING_Y;

        $this->image->annotateImage($this->matchShadowText, $this->x + 1, $this->y + 1, 0, $text);
        $this->image->annotateImage($this->matchText, $this->x, $this->y, 0, $text);
        $this->y += self::MATCH_SPACING_Y;
    }

    /**
     * Calculate whether the whole day will fit into the current column.
     * If not start a new column of days and increment the required width property
     *
     * @param int $matchCount
     * @return bool
     */
    private function willWholeDayFit(int $matchCount): bool
    {
        $requiredPixels = $matchCount * (self::MATCH_SPACING_Y + 13);

        return $this->y + $requiredPixels <= $this->maxColumnHeight;
    }

    /**
     * Will start a new column in which the next days will be rendered onto
     * Adds onto the required width of the entire canvas
     *
     * @return void
     */
    private function startNewColumn(): void
    {
        $this->x += (int)(self::DAY_SPACING_X + $this->maxStringWidthCurrent);
        $this->y = self::IMAGE_START_Y;
        $this->incrementRequiredWidth((int)(self::DAY_SPACING_X + $this->maxStringWidthCurrent));
        $this->maxRequiredColumnHeight = max($this->maxRequiredColumnHeight, $this->maxRequiredColumnHeightCurrent);
        $this->maxRequiredColumnHeightCurrent = 0;
        $this->maxStringWidthCurrent = 0;
        $this->columns++;
    }

    /**
     * Add onto the necessary width of the entire canvas
     * If the required width is larger than the image width,
     * override that with the image width property
     *
     * @param int $increment How many pixels to increment
     * @return void
     */
    private function incrementRequiredWidth(int $increment): void
    {
        $this->requiredWidth += $increment;
    }

    /**
     * Get the required image width, which is the required width + the width of the longest string of last column
     *
     * @return float|int
     */
    public function getRequiredWidth()
    {
        $paddingRight = self::IMAGE_START_X + self::DAY_SPACING_X;

        return $this->requiredWidth + $this->maxStringWidthCurrent + $paddingRight;
    }

    /**
     * Get the required image height
     *
     * @return float|int
     */
    public function getRequiredHeight()
    {
        return $this->maxRequiredColumnHeight - self::DAY_SPACING_X;
    }

    /**
     * Calculate how much horizontal space the match text takes up.
     * This helps decide where to position a new column of matches
     *
     * @param string $text Text to calculate width of
     * @return int
     * @throws \ImagickException
     */
    private function calculateStringWidth(string $text): int
    {
        return (int)$this->image->queryFontMetrics($this->matchText, $text)['textWidth'];
    }
}
