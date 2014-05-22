<?php
require_once(dirname(__FILE__).'/bootstrap.php');

class PapayaModuleTwitterApiTest extends PapayaTestCase {
  /**
  * @covers PapayaModuleTwitterApi::__construct
  */
  public function testConstructExpectingInvalidArgumentExceptionNoArray() {
    try {
      new PapayaModuleTwitterApi(NULL);
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('Array with OAuth configuration data expected.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleTwitterApi::__construct
  */
  public function testConstructExpectingInvalidArgumentExceptionMissingData() {
    try {
      new PapayaModuleTwitterApi(array());
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('Expected key access_token not found.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleTwitterApi::__construct
  */
  public function testConstructCorrect() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $this->assertAttributeEquals('12345', '_accessToken', $api);
    $this->assertAttributeEquals('67890', '_accessSecret', $api);
    $this->assertAttributeEquals('abcde', '_consumerKey', $api);
    $this->assertAttributeEquals('fghij', '_consumerSecret', $api);
  }

  /**
  * @covers PapayaModuleTwitterApi::time
  */
  public function testTime() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $this->assertEquals(1234567890, $api->time(1234567890));
  }

  /**
  * @covers PapayaModuleTwitterApi::method
  */
  public function testMethod() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $this->assertEquals('POST', $api->method('post'));
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersSetObject() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $parameters = $this->getMockBuilder('PapayaModuleTwitterApiParameters')->getMock();
    $this->assertEquals($parameters, $api->parameters($parameters));
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersInitialize() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $this->assertInstanceOf('PapayaModuleTwitterApiParameters', $api->parameters());
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersSetData() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $parameters = $api->parameters(array('foo' => 'bar', 'baz' => 'tux'));
    $this->assertEquals(array('foo' => 'bar', 'baz' => 'tux'), $parameters->data());
  }

  /**
  * @covers PapayaModuleTwitterApi::getBaseInfo
  */
  public function testGetBaseInfo() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $oauth = array(
      'oauth_consumer_key' => $options['consumer_key'],
      'oauth_nonce' => 1234567890,
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_token' => $options['access_token'],
      'oauth_timestamp' => 1234567890,
      'oauth_version' => '1.0',
      'screen_name' => 'SomeTwitterUser'
    );
    $this->assertEquals(
      'GET&https%3A%2F%2Fapi.twitter.com%2F1.1%2Fstatuses%2Fuser_timeline.json&'
      .'oauth_consumer_key%3Dabcde%26oauth_nonce%3D1234567890%26oauth_signature_method%3DHMAC-SHA1'
      .'%26oauth_timestamp%3D1234567890%26oauth_token%3D12345%26oauth_version%3D1.0'
      .'%26screen_name%3DSomeTwitterUser',
      $api->getBaseInfo($oauth)
    );
  }

  /**
  * @covers PapayaModuleTwitterApi::getOauth
  */
  public function testGetOauth() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $api->parameters(array('screen_name' => 'SomeTwitterUser'));
    $api->time(1234567890);
    $this->assertEquals(
      array(
        'oauth_consumer_key' => 'abcde',
        'oauth_nonce' => 1234567890,
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_token' => '12345',
        'oauth_timestamp' => 1234567890,
        'oauth_version' => '1.0',
        'screen_name' => 'SomeTwitterUser',
        'oauth_signature' => '3TMAcS0zhtIQmNU9km3BTa0z1P4='
      ),
      $api->getOauth()
    );
  }

  /**
  * @covers PapayaModuleTwitterApi::getAuthHeader
  */
  public function testGetAuthHeader() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi($options);
    $api->parameters(array('screen_name' => 'SomeTwitterUser'));
    $api->time(1234567890);
    $this->assertEquals(
      'OAuth oauth_consumer_key="abcde", oauth_nonce="1234567890", '
      .'oauth_signature_method="HMAC-SHA1", oauth_token="12345", oauth_timestamp="1234567890", '
      .'oauth_version="1.0", screen_name="SomeTwitterUser", '
      .'oauth_signature="3TMAcS0zhtIQmNU9km3BTa0z1P4%3D"',
      $api->getAuthHeader()
    );
  }

  /**
  * @covers PapayaModuleTwitterApi::send
  */
  public function testSend() {
    $options = array(
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $api = new PapayaModuleTwitterApi_TestProxy($options);
    $api->parameters(array('screen_name' => 'SomeTwitterUser'));
    $api->method('POST');
    $api->time(1234567890);
    $api->_url = dirname(__FILE__).'/json.txt';
    $object = new stdClass();
    $object->text = 'Some test text';
    $expected = json_encode(array($object))."\n";
    $this->assertEquals($expected, $api->send());
  }
}

class PapayaModuleTwitterApi_TestProxy extends PapayaModuleTwitterApi {
  public $_url;
}