<?php
use OAuth\Common\Http\ArtaxClient;

use OAuth\Common\Http\Uri;

class HttpClientsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var object|\OAuth\Common\Http\ClientInterface[]
     */
    protected $clients;

    public function setUp()
    {
        $this->clients[] = new ArtaxClient();
    }

    public function tearDown()
    {
        foreach($this->clients as $client)
        {
            unset($client);
        }
    }

    public function testException()
    {
        // sending a post here should get us a 405 which should trigger an exception
        $testUri = new Uri('http://httpbin.org/delete');
        foreach($this->clients as $client)
        {
            $this->setExpectedException('OAuth\Common\Http\Exception\TokenResponseException');
            $client->retrieveResponse($testUri, ['blah' => 'blih'] );
        }

    }

    public function testDelete()
    {
        $testUri = new Uri('http://httpbin.org/delete');

        $deleteTestCb = function($response)
        {
            $data = json_decode($response, true);
            $this->assertEquals( '', $data['data'] );
        };

        $this->__doTestRetrieveResponse($testUri, [], [], 'DELETE', $deleteTestCb );
    }

    public function testPut()
    {
        $testUri = new Uri('http://httpbin.org/put');

        $putTestCb = function($response)
        {
            // verify the put response
            $data = json_decode($response, true);
            $this->assertEquals( 'testKey=testValue', $data['data'] );
        };

        $this->__doTestRetrieveResponse($testUri, ['testKey' => 'testValue'], [], 'PUT', $putTestCb );
    }

    public function testPost()
    {
        // http test server
        $testUri = new Uri('http://httpbin.org/post');

        $postTestCb = function($response)
        {
            // verify the post response
            $data = json_decode($response, true);
            // note that we check this because the retrieveResponse wrapper function automatically adds a content-type
            // if there isn't one and it
            $this->assertEquals( 'testValue', $data['form']['testKey'] );
        };

        $this->__doTestRetrieveResponse($testUri, ['testKey' => 'testValue'], [], 'POST', $postTestCb );
    }

    public function testGet()
    {
        // test uri
        $testUri = new Uri('http://httpbin.org/get?testKey=testValue');

        $getTestCb = function($response)
        {
            $data = json_decode($response, true);
            $this->assertEquals( 'testValue', $data['args']['testKey'] );
        };

        $this->__doTestRetrieveResponse($testUri, [], [], 'GET', $getTestCb);

    }

    /**
     * Test on all HTTP clients.
     *
     * @param OAuth\Common\Http\UriInterface $uri
     * @param array $param
     * @param array $header
     * @param $method
     * @param $responseCallback
     */
    protected function __doTestRetrieveResponse(\OAuth\Common\Http\UriInterface $uri, array $param, array $header, $method, callable $responseCallback)
    {
        foreach($this->clients as $client)
        {
            $response = $client->retrieveResponse($uri, $param, $header, $method);
            $responseCallback($response, $client);
        }
    }
}