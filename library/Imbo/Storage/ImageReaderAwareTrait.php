<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\Storage;

/**
 * Image reader aware trait
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @package Storage
 */
trait ImageReaderAwareTrait {
    /**
     * Image reader instance
     *
     * @var ImageReader
     */
    private $imageReader;

    /**
     * Set an instance of an image reader
     *
     * @param ImageReader $reader An image reader instance
     */
    public function setImageReader(ImageReader $reader) {
        $this->imageReader = $reader;
    }

    /**
     * Get an instance of an image reader
     *
     * @return ImageReader An image reader instance
     */
    public function getImageReader() {
        return $this->imageReader;
    }
}
