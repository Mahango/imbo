<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\IntegrationTest\Image\Transformation;

use Imbo\Image\Transformation\Transpose;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Test suite\Integration tests
 */
class TransposeTest extends TransformationTests {
    /**
     * {@inheritdoc}
     */
    protected function getTransformation() {
        return new Transpose();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedName() {
        return 'transpose';
    }

    /**
     * {@inheritdoc}
     * @covers Imbo\Image\Transformation\Canvas::applyToImage
     */
    protected function getImageMock() {
        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->any())->method('getBlob')->will($this->returnValue(file_get_contents(FIXTURES_DIR . '/image.png')));
        $image->expects($this->once())->method('setBlob')->with($this->isType('string'))->will($this->returnValue($image));

        return $image;
    }
}
