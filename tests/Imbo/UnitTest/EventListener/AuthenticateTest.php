<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\UnitTest\EventListener;

use Imbo\EventListener\Authenticate;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Test suite\Unit tests
 */
class AuthenticateTest extends ListenerTests {
    /**
     * @var Authenticate
     */
    private $listener;

    private $event;
    private $request;
    private $response;
    private $query;
    private $headers;

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->query = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->headers = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');

        $this->request = $this->getMock('Imbo\Http\Request\Request');
        $this->request->query = $this->query;
        $this->request->headers = $this->headers;

        $this->response = $this->getMock('Imbo\Http\Response\Response');

        $this->event = $this->getMock('Imbo\EventManager\EventInterface');
        $this->event->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));

        $this->listener = new Authenticate();
    }

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->listener;
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->request = null;
        $this->response = null;
        $this->event = null;
        $this->query = null;
        $this->headers = null;
        $this->listener = null;
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Missing authentication timestamp
     * @expectedExceptionCode 400
     */
    public function testThrowsExceptionWhenAuthInfoIsMissing() {
        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(false));
        $this->headers->expects($this->at(1))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(null));

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Missing authentication signature
     * @expectedExceptionCode 400
     */
    public function testThrowsExceptionWhenSignatureIsMissing() {
        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(true));
        $this->headers->expects($this->at(1))->method('has')->with('x-imbo-authenticate-signature')->will($this->returnValue(true));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(gmdate('Y-m-d\TH:i:s\Z')));

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @covers Imbo\EventListener\Authenticate::timestampIsValid
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Invalid timestamp: some string
     * @expectedExceptionCode 400
     */
    public function testThrowsExceptionWhenTimestampIsInvalid() {
        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(true));
        $this->headers->expects($this->at(1))->method('has')->with('x-imbo-authenticate-signature')->will($this->returnValue(true));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue('some string'));

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @covers Imbo\EventListener\Authenticate::timestampHasExpired
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Timestamp has expired: 2010-10-10T20:10:10Z
     * @expectedExceptionCode 400
     */
    public function testThrowsExceptionWhenTimestampHasExpired() {
        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(true));
        $this->headers->expects($this->at(1))->method('has')->with('x-imbo-authenticate-signature')->will($this->returnValue(true));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue('2010-10-10T20:10:10Z'));

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Signature mismatch
     * @expectedExceptionCode 400
     */
    public function testThrowsExceptionWhenSignatureDoesNotMatch() {
        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(true));
        $this->headers->expects($this->at(1))->method('has')->with('x-imbo-authenticate-signature')->will($this->returnValue(true));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(gmdate('Y-m-d\TH:i:s\Z')));
        $this->headers->expects($this->at(3))->method('get')->with('x-imbo-authenticate-signature')->will($this->returnValue('foobar'));

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @covers Imbo\EventListener\Authenticate::signatureIsValid
     * @covers Imbo\EventListener\Authenticate::timestampIsValid
     * @covers Imbo\EventListener\Authenticate::timestampHasExpired
     */
    public function testApprovesValidSignature() {
        $httpMethod = 'GET';
        $url = 'http://imbo/users/christer/images/image';
        $publicKey = 'christer';
        $privateKey = 'key';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $data = $httpMethod . '|' . $url . '|' . $publicKey . '|' . $timestamp;
        $signature = hash_hmac('sha256', $data, $privateKey);

        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(true));
        $this->headers->expects($this->at(1))->method('has')->with('x-imbo-authenticate-signature')->will($this->returnValue(true));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-timestamp')->will($this->returnValue($timestamp));
        $this->headers->expects($this->at(3))->method('get')->with('x-imbo-authenticate-signature')->will($this->returnValue($signature));

        $this->request->expects($this->once())->method('getRawUri')->will($this->returnValue($url));
        $this->request->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));
        $this->request->expects($this->once())->method('getPrivateKey')->will($this->returnValue($privateKey));
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue($httpMethod));

        $responseHeaders = $this->getMock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $responseHeaders->expects($this->once())->method('set')->with('X-Imbo-AuthUrl', $url);

        $this->response->headers = $responseHeaders;

        $this->listener->invoke($this->event);
    }

    /**
     * @covers Imbo\EventListener\Authenticate::invoke
     * @covers Imbo\EventListener\Authenticate::signatureIsValid
     * @covers Imbo\EventListener\Authenticate::timestampIsValid
     * @covers Imbo\EventListener\Authenticate::timestampHasExpired
     */
    public function testApprovesValidSignatureWithAuthInfoFromQueryParameters() {
        $httpMethod = 'GET';
        $url = 'http://imbo/users/christer/images/image';
        $publicKey = 'christer';
        $privateKey = 'key';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $data = $httpMethod . '|' . $url . '|' . $publicKey . '|' . $timestamp;
        $signature = hash_hmac('sha256', $data, $privateKey);
        $rawUrl = $url . '?signature=' . $signature . '&timestamp=' . $timestamp;

        $this->headers->expects($this->at(0))->method('has')->with('x-imbo-authenticate-timestamp')->will($this->returnValue(false));
        $this->headers->expects($this->at(1))->method('get')->with('x-imbo-authenticate-timestamp', $timestamp)->will($this->returnValue($timestamp));
        $this->headers->expects($this->at(2))->method('get')->with('x-imbo-authenticate-signature', $signature)->will($this->returnValue($signature));
        $this->query->expects($this->at(0))->method('get')->with('timestamp')->will($this->returnValue($timestamp));
        $this->query->expects($this->at(1))->method('get')->with('signature')->will($this->returnValue($signature));

        $this->request->expects($this->once())->method('getRawUri')->will($this->returnValue($rawUrl));
        $this->request->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));
        $this->request->expects($this->once())->method('getPrivateKey')->will($this->returnValue($privateKey));
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue($httpMethod));

        $responseHeaders = $this->getMock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $responseHeaders->expects($this->once())->method('set')->with('X-Imbo-AuthUrl', $url);

        $this->response->headers = $responseHeaders;

        $this->listener->invoke($this->event);
    }
}
