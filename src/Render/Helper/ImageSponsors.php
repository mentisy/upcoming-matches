<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Render\Helper;

use Avolle\UpcomingMatches\Themes\Theme;
use Imagick;

/**
 * Class ImageSponsors
 *
 * Prepares an Imagick instance of Sponsors, to later be added to the complete image
 */
class ImageSponsors
{
    /**
     * Imagick instance
     *
     * @var \Imagick
     */
    protected Imagick $imagick;

    /**
     * ImageSponsors constructor
     *
     * @param \Avolle\UpcomingMatches\Themes\Theme $theme Theme to use
     * @param int $imageWidth Width of complete image
     * @throws \ImagickException
     */
    public function __construct(Theme $theme, int $imageWidth)
    {
        $sponsors = new Imagick();
        $sponsors->readImage(RENDERABLES . $theme->sponsors);
        $factor = $imageWidth / $sponsors->getImageWidth();
        $height = (int)($sponsors->getImageHeight() * $factor);
        $sponsors->resizeImage($imageWidth, $height, 0, 0);

        $this->imagick = $sponsors;
    }

    /**
     * Returns the Imagick instance
     *
     * @return \Imagick
     */
    public function getImagick(): Imagick
    {
        return $this->imagick;
    }
}
