<?php
require_once(dirname(__FILE__).'/bootstrap.php');
class PapayaModuleTwitterApiTest extends PapayaTestCase {
  private $_options = array(
    'access_token' => '12345',
    'access_secret' => '67890',
    'consumer_key' => 'abcde',
    'consumer_secret' => 'fghij'
  );

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
  public function testConstructCorrect() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertAttributeEquals('12345', '_accessToken', $api);
    $this->assertAttributeEquals('67890', '_accessSecret', $api);
    $this->assertAttributeEquals('abcde', '_consumerKey', $api);
    $this->assertAttributeEquals('fghij', '_consumerSecret', $api);
  }

  /**
  * @covers PapayaModuleTwitterApi::setConfiguration
  */
  public function testConfigurationExpectingInvalidArgumentExceptionMissingData() {
    $api = new PapayaModuleTwitterApi($this->_options);
    try {
      $api->setConfiguration(array());
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('Expected key access_token not found.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleTwitterApi::setConfiguration
  */
  public function testSetConfigurationCorrect() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $options = array(
      'access_token' => '34567',
      'access_secret' => '89012',
      'consumer_key' => 'cdefg',
      'consumer_secret' => 'hijkl'
    );
    $api->setConfiguration($options);
    $this->assertAttributeEquals('34567', '_accessToken', $api);
    $this->assertAttributeEquals('89012', '_accessSecret', $api);
    $this->assertAttributeEquals('cdefg', '_consumerKey', $api);
    $this->assertAttributeEquals('hijkl', '_consumerSecret', $api);
  }

  /**
  * @covers PapayaModuleTwitterApi::url
  */
  public function testUrlWithOverride() {
    $api = new PapayaModuleTwitterApi_TestProxy($this->_options);
    $api->_overrideUrl = 'http://example.com/';
    $this->assertEquals('http://example.com/', $api->url());
  }

  /**
  * @covers PapayaModuleTwitterApi::url
  */
  public function testUrl() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertEquals('http://example.org/', $api->url('http://example.org/'));
  }

  /**
  * @covers PapayaModuleTwitterApi::mode
  */
  public function testMode() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertEquals('update', $api->mode('update'));
  }

  /**
  * @covers PapayaModuleTwitterApi::mode
  */
  public function testModeInvalid() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertEquals('user_timeline', $api->mode('INVALID MODE'));
  }

  /**
  * @covers PapayaModuleTwitterApi::time
  */
  public function testTime() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertEquals(1234567890, $api->time(1234567890));
  }

  /**
  * @covers PapayaModuleTwitterApi::method
  */
  public function testMethod() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertEquals('POST', $api->method('post'));
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersSetObject() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $parameters = $this->getMockBuilder('PapayaModuleTwitterApiParameters')->getMock();
    $this->assertEquals($parameters, $api->parameters($parameters));
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersInitialize() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $this->assertInstanceOf('PapayaModuleTwitterApiParameters', $api->parameters());
  }

  /**
  * @covers PapayaModuleTwitterApi::parameters
  */
  public function testParametersSetData() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $parameters = $api->parameters(array('foo' => 'bar', 'baz' => 'tux'));
    $this->assertEquals(array('foo' => 'bar', 'baz' => 'tux'), $parameters->data());
  }

  /**
  * @covers PapayaModuleTwitterApi::getBaseInfo
  */
  public function testGetBaseInfo() {
    $api = new PapayaModuleTwitterApi($this->_options);
    $oauth = array(
      'oauth_consumer_key' => $this->_options['consumer_key'],
      'oauth_nonce' => 1234567890,
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_token' => $this->_options['access_token'],
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
    $api = new PapayaModuleTwitterApi($this->_options);
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
    $api = new PapayaModuleTwitterApi($this->_options);
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
  * @dataProvider dataProviderMethod
  */
  public function testSend($method) {
    $api = new PapayaModuleTwitterApi_TestProxy($this->_options);
    $api->parameters(array('screen_name' => 'SomeTwitterUser'));
    $api->method($method);
    $api->time(1234567890);
    $api->_overrideUrl = dirname(__FILE__).'/json.txt';
    $object = new stdClass();
    $object->text = 'Some test text';
    $expected = json_encode(array($object))."\n";
    $this->assertEquals($expected, $api->send());
  }

  /**
  * @covers PapayaModuleTwitterApi::update
  */
  public function testUpdate() {
    $api = new PapayaModuleTwitterApi_TestProxy($this->_options);
    $api->parameters(array('screen_name' => 'SomeTwitterUser'));
    $api->method('POST');
    $api->time(1234567890);
    $api->_overrideUrl = dirname(__FILE__).'/json.txt';
    $object = new stdClass();
    $object->text = 'Some test text';
    $expected = json_encode(array($object))."\n";
    $this->assertEquals($expected, $api->update('Test tweet text.'));
  }

  public static function dataProviderMethod() {
    return array(
      array('POST'),
      array('GET')
    );
  }
}

class PapayaModuleTwitterApi_TestProxy extends PapayaModuleTwitterApi {
  public $_overrideUrl;
}
