<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render;

use Avolle\UpcomingMatches\Match;
use Avolle\UpcomingMatches\Render\Helper\ImageMatchesHelper;
use Avolle\UpcomingMatches\Render\Helper\ImageSponsors;
use Avolle\UpcomingMatches\Render\Helper\ImageTeamDetails;
use Avolle\UpcomingMatches\SportConfig;
use Avolle\UpcomingMatches\Themes\Theme;
use Cake\Collection\CollectionInterface;
use Imagick;

/**
 * ImageRender class
 *
 * Renders an image of upcoming matches
 */
class ImageRender implements RenderInterface
{
    /**
     * Collection of matches to render
     *
     * @var \Cake\Collection\CollectionInterface
     */
    private CollectionInterface $matchesCollection;

    /**
     * Sports Config
     *
     * @var \Avolle\UpcomingMatches\SportConfig
     */
    private SportConfig $sportConfig;

    /**
     * Theme to render with
     *
     * @var \Avolle\UpcomingMatches\Themes\Theme
     */
    private Theme $theme;

    /**
     * Imagick instance of complete image
     *
     * @var \Imagick
     */
    private Imagick $imagick;

    /**
     * Padding around details
     */
    public const PADDING = 40;

    /**
     * Initial width of complete image. Needs to be large as to contain all matches. Will be cropped later
     */
    public const IMAGE_INITIAL_WIDTH = 3000;

    /**
     * Max height of a column before a new one should be started
     */
    public const MAX_COLUMN_HEIGHT = 600;

    /**
     * Position to start rendering matches in the X axis
     */
    public const MATCH_GRID_X_START = 50;

    /**
     * Position to start rendering matches in the Y axis
     */
    public const MATCH_GRID_Y_START = 220;

    /**
     * Imagick instance of Match image
     *
     * @var \Imagick
     */
    protected Imagick $matchImage;

    /**
     * Required width of all matches rendered
     *
     * @var int
     */
    protected int $requiredMatchSizeX;

    /**
     * Required height of all matches rendered
     *
     * @var int
     */
    protected int $requiredMatchSizeY;

    /**
     * Required height of complete image
     *
     * @var int
     */
    private int $requiredHeight;

    /**
     * ImageRender constructor
     *
     * @param \Cake\Collection\CollectionInterface $matchesCollection Collection of matches
     * @param \Avolle\UpcomingMatches\SportConfig $sportConfig Sports Config
     */
    public function __construct(CollectionInterface $matchesCollection, SportConfig $sportConfig)
    {
        $this->matchesCollection = $this->groupByDate($matchesCollection);
        $this->sportConfig = $sportConfig;
    }

    /**
     * Renders the image with the current theme and configuration
     *
     * @return void
     * @throws \ImagickException|\ImagickDrawException|\ImagickPixelException
     */
    public function render(): void
    {
        if (!isset($this->theme)) {
            $this->theme = new Theme();
        }
        $this->prepareMatches();
        $teamDetails = new ImageTeamDetails($this->sportConfig, $this->theme, $this->requiredMatchSizeX);
        if ($this->theme->sponsors) {
            $sponsors = new ImageSponsors($this->theme, $this->requiredMatchSizeX);
        }
        $this->createCanvas($teamDetails, $sponsors ?? null);
    }

    /**
     * Output rendering to template
     *
     * @return void
     */
    public function output(): void
    {
        $imagick = $this->imagick;

        require TEMPLATES . 'image.php';
    }

    /**
     * Set a new Theme class to use for rendering. Must be set before any rendering starts
     *
     * @return void
     * @param \Avolle\UpcomingMatches\Themes\Theme $theme Theme to use for future rendering
     */
    public function setTheme(Theme $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Takes the collection of matches and groups them by date
     *
     * @param \Cake\Collection\CollectionInterface $matchesCollection Collection of matches
     * @return \Cake\Collection\CollectionInterface
     */
    private function groupByDate(CollectionInterface $matchesCollection): CollectionInterface
    {
        return $matchesCollection->groupBy(
            fn(Match $match) => $match->day . strftime(' %d. %B', $match->date->getTimestamp())
        );
    }

    /**
     * Prepares all matches for rendering onto the main image
     *
     * @return void
     * @throws \ImagickException|\ImagickPixelException|\ImagickDrawException
     */
    private function prepareMatches(): void
    {
        $matchesHelper = new ImageMatchesHelper(
            $this->matchesCollection,
            $this->theme,
            self::IMAGE_INITIAL_WIDTH,
            self::MAX_COLUMN_HEIGHT,
            self::MAX_COLUMN_HEIGHT,
        );

        $this->matchImage = $matchesHelper->renderMatches();

        $this->requiredMatchSizeX = (int)$matchesHelper->getRequiredWidth();
        $this->requiredMatchSizeY = (int)$matchesHelper->getRequiredHeight();
    }

    /**
     * Writes the Imagick instance into a file
     *
     * @param string $filename Filename of image, with path
     * @return void
     * @throws \ImagickException
     */
    public function toFile(string $filename): void
    {
        $this->imagick->writeImage($filename);
    }

    /**
     * Creates a canvas, onto which we will render all details (matches, team details and sponsor)
     *
     * @param \Avolle\UpcomingMatches\Render\Helper\ImageTeamDetails $imageTeamDetails Instance of TeamDetails render helper
     * @param \Avolle\UpcomingMatches\Render\Helper\ImageSponsors|null $sponsors Instance of Sponsors render helper
     * @return void
     * @throws \ImagickException
     */
    private function createCanvas(ImageTeamDetails $imageTeamDetails, ?ImageSponsors $sponsors): void
    {
        $this->requiredHeight = (int)($this->requiredMatchSizeY
            + $imageTeamDetails->getImagick()->getImageHeight()
            + ($sponsors->getImagick()->getImageHeight() ?? 0)
            + (self::PADDING * 3)
        );

        $this->imagick = new Imagick();
        $this->imagick->newImage(
            $this->requiredMatchSizeX + self::PADDING,
            $this->requiredHeight,
            $this->theme->backgroundColor,
            'png',
        );

        $this->imagick->cropImage($this->requiredMatchSizeX + self::PADDING, $this->requiredHeight, 0, 0);

        $this->renderTeamDetails($imageTeamDetails);
        $this->renderMatches();

        $this->renderSponsors($sponsors);
    }

    /**
     * Renders team details onto complete image
     *
     * @param \Avolle\UpcomingMatches\Render\Helper\ImageTeamDetails $imageTeamDetails Instance of TeamDetails render helper
     * @throws \ImagickException
     */
    private function renderTeamDetails(ImageTeamDetails $imageTeamDetails): void
    {
        $this->imagick->compositeImage(
            $imageTeamDetails->getImagick(),
            Imagick::COMPOSITE_DEFAULT,
            0,
            0,
        );
    }

    /**
     * Renders matches onto complete image
     *
     * @return void
     * @throws \ImagickException
     */
    private function renderMatches(): void
    {
        $this->imagick->compositeImage(
            $this->matchImage,
            Imagick::COMPOSITE_DEFAULT,
            self::MATCH_GRID_X_START,
            self::MATCH_GRID_Y_START,
        );
    }

    /**
     * Renders sponsors onto complete image
     *
     * @param \Avolle\UpcomingMatches\Render\Helper\ImageSponsors|null $sponsors Instance of Sponsors render helper
     * @throws \ImagickException
     */
    private function renderSponsors(?ImageSponsors $sponsors): void
    {
        $sponsorFromBottom = $this->requiredSponsorSpacingFromBottom($sponsors->getImagick()->getImageHeight());

        $this->imagick->compositeImage(
            $sponsors->getImagick(),
            Imagick::COMPOSITE_DEFAULT,
            (int)(self::PADDING / 2),
            $sponsorFromBottom,
        );
    }

    /**
     * Calculate the position from the bottom of complete image required to place the sponsor image
     *
     * @param float $sponsorHeight Sponsor image height
     * @return int
     */
    private function requiredSponsorSpacingFromBottom(float $sponsorHeight): int
    {
        return (int)($this->requiredHeight - $sponsorHeight - self::PADDING);
    }
}
