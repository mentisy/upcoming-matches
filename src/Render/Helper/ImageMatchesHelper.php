<?php

namespace Avolle\WeeklyMatches\Render\Helper;

use Avolle\WeeklyMatches\Match;
use Cake\Collection\CollectionInterface;
use Imagick;
use ImagickDraw;
use ImagickPixel;

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

    public function __construct(CollectionInterface $matchesCollection, int $imageWidth, int $imageHeight, int $allotedSpaceY)
    {
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

    private function renderDay($day, $matches)
    {
        if (!$this->willWholeDayFit(count($matches))) {
            $this->x += self::DAY_SPACING_X + $this->maxStringWidthCurrent;
            $this->y = self::IMAGE_START_Y;
            $this->incrementRequiredWidth(self::DAY_SPACING_X + $this->maxStringWidthCurrent);
            $this->incrementRequiredHeight($this->y);
            $this->maxStringWidthCurrent = 0;
        }

        $this->image->annotateImage($this->dayText, $this->x, $this->y, 0, ucfirst($day));
        $this->y += 25; // Spacing between day header and the first match of the day
        $this->incrementRequiredHeight(25);
        foreach ($matches as $match) {
            $this->renderMatch($match);
        }
    }

    private function renderMatch(Match $match)
    {
        $text = sprintf("%s: %s - %s (%s)", $match->time, $match->homeTeam, $match->awayTeam, $match->tournament);
        $this->maxStringWidth = max([$this->maxStringWidth, (strlen($text) * self::MATCH_FONT_SIZE / 2)]);
        $this->maxStringWidthCurrent = max([$this->maxStringWidthCurrent, (strlen($text) * self::MATCH_FONT_SIZE / 2)]);

        $this->image->annotateImage($this->matchText, $this->x, $this->y, 0, $text);
        $this->y += self::MATCH_SPACING_Y;
        $this->incrementRequiredHeight(self::MATCH_SPACING_Y);
    }

    private function willWholeDayFit(int $matchCount): bool
    {
        // Calculate necessary pixels to fit day header and all matches inside the allocated grid.
        $requiredPixels = $matchCount * self::MATCH_SPACING_Y;

        // If required space is above the available space, then render the day on another column.if
        // Gjør matte for å sjekke om NÅVÆRENDE Y + alle kampene < MATCH_GRID_Y_END.
        return $this->y + $requiredPixels <= $this->allottedSpaceY;
    }

    private function incrementRequiredWidth(int $increment)
    {
        $this->requiredWidth += $increment;

        if ($this->requiredWidth > $this->imageWidth) {
            $this->requiredWidth = $this->imageWidth;
        }
    }

    private function incrementRequiredHeight(int $increment)
    {
        $this->requiredHeight += $increment;

        if ($this->requiredHeight > $this->allottedSpaceY) {
            $this->requiredHeight = $this->allottedSpaceY;
        }
    }

    /**
     * @return float|int
     */
    public function getRequiredWidth()
    {
        return $this->requiredWidth + $this->maxStringWidth;
    }

    /**
     * @return float|int
     */
    public function getRequiredHeight()
    {
        return $this->requiredHeight;
    }
}