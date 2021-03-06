<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\IntegrationTest\EventListener;

use Imbo\EventListener\ExifMetadata,
    Imbo\Model\Image;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @package Test suite\Integration tests
 */
class ExifMetadataTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Imbo\EventListener\ExifMetadata::__construct
     * @covers Imbo\EventListener\ExifMetadata::populate
     */
    public function testCanGetPropertiesFromImageUnfiltered() {
        $listener = new ExifMetadata();

        $image = new Image();
        $image->setBlob(file_get_contents(FIXTURES_DIR . '/exif-logo.jpg'));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->once())->method('getImage')->will($this->returnValue($image));
        $event = $this->getMock('Imbo\EventManager\EventInterface');
        $event->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        $properties = $listener->populate($event);

        $this->assertSame('SAMSUNG', $properties['exif:Make']);
        $this->assertSame('GT-I9100', $properties['exif:Model']);
        $this->assertSame('254/5', $properties['exif:GPSAltitude']);
        $this->assertSame('63/1, 40/1, 173857/3507', $properties['exif:GPSLatitude']);
        $this->assertSame('9/1, 5/1, 38109/12500', $properties['exif:GPSLongitude']);
    }

    /**
     * @covers Imbo\EventListener\ExifMetadata::__construct
     * @covers Imbo\EventListener\ExifMetadata::populate
     * @covers Imbo\EventListener\ExifMetadata::filterProperties
     */
    public function testCanGetPropertiesFromImageFiltered() {
        $listener = new ExifMetadata(array(
            'exif:Flash',
            'exif:Compression'
        ));

        $image = new Image();
        $image->setBlob(file_get_contents(FIXTURES_DIR . '/exif-logo.jpg'));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->once())->method('getImage')->will($this->returnValue($image));
        $event = $this->getMock('Imbo\EventManager\EventInterface');
        $event->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        $properties = $listener->populate($event);

        $this->assertSame('16', $properties['exif:Flash']);
        $this->assertSame('6', $properties['exif:Compression']);
        $this->assertArrayNotHasKey('exif:GPSAltitude', $properties);
    }

    /**
     * @covers Imbo\EventListener\ExifMetadata::__construct
     * @covers Imbo\EventListener\ExifMetadata::populate
     * @covers Imbo\EventListener\ExifMetadata::parseProperties
     * @covers Imbo\EventListener\ExifMetadata::parseGpsCoordinate
     */
    public function testCanParseGpsValues() {
        $listener = new ExifMetadata();

        $image = new Image();
        $image->setBlob(file_get_contents(FIXTURES_DIR . '/exif-logo.jpg'));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->once())->method('getImage')->will($this->returnValue($image));
        $event = $this->getMock('Imbo\EventManager\EventInterface');
        $event->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        $properties = $listener->populate($event);

        $this->assertEquals(9.0841802, $properties['gps:location'][0], '', 0.05);
        $this->assertEquals(63.680437300003, $properties['gps:location'][1], '', 0.05);
        $this->assertEquals(50.8, $properties['gps:altitude'], '', 0.05);
    }

    /**
     * @covers Imbo\EventListener\ExifMetadata::__construct
     * @covers Imbo\EventListener\ExifMetadata::populate
     * @covers Imbo\EventListener\ExifMetadata::save
     */
    public function testCanGetAndSaveProperties() {
        $listener = new ExifMetadata();
        $publicKey = 'foobar';

        $image = new Image();
        $image->setBlob(file_get_contents(FIXTURES_DIR . '/exif-logo.jpg'));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->exactly(2))->method('getImage')->will($this->returnValue($image));
        $request->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));

        $database = $this->getMock('Imbo\Database\DatabaseInterface');
        $database->expects($this->once())->method('updateMetadata')->with(
            $this->equalTo($publicKey),
            $this->equalTo('753e11e00522ff1e95600d8f91c74e8e'),
            $this->arrayHasKey('gps:location')
        );

        $event = $this->getMock('Imbo\EventManager\EventInterface');
        $event->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->once())->method('getDatabase')->will($this->returnValue($database));

        $properties = $listener->populate($event);

        $this->assertSame('SAMSUNG', $properties['exif:Make']);
        $this->assertSame('GT-I9100', $properties['exif:Model']);

        $listener->save($event);

    }
}
