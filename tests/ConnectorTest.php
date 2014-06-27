<?php
require_once(dirname(__FILE__).'/bootstrap.php');
class PapayaModuleTwitterConnectorTest extends PapayaTestCase {
  private $_options = array(
    'access_token' => '12345',
    'access_secret' => '67890',
    'consumer_key' => 'abcde',
    'consumer_secret' => 'fghij'
  );

  /**
  * @covers PapayaModuleTwitterConnector::api
  */
  public function testApiSet() {
    $connector = new PapayaModuleTwitterConnector();
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($api, $connector->api($api));
  }

  /**
  * @covers PapayaModuleTwitterConnector::api
  */
  public function testApiGetExpectingRuntimeException() {
    $connector = new PapayaModuleTwitterConnector();
    try {
      $connector->api();
      $this->fail("Expected RuntimeException not thrown.");
    } catch (RuntimeException $e) {
      $this->assertEquals(
        'Twitter API cannot be used without configuration.',
        $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaModuleTwitterConnector::api
  */
  public function testApiGetSuccess() {
    $connector = new PapayaModuleTwitterConnector();
    $connector->setConfiguration($this->_options);
    $this->assertInstanceOf('PapayaModuleTwitterApi', $connector->api());
  }

  /**
  * @covers PapayaModuleTwitterConnector::setConfiguration
  */
  public function testSetConfiguration() {
    $connector = new PapayaModuleTwitterConnector();
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $connector->api($api);
    $connector->setConfiguration($this->_options);
    $this->assertAttributeEquals($this->_options, '_configuration', $connector);
  }

  /**
  * @covers PapayaModuleTwitterConnector::getOptions
  */
  public function testGetOptions() {
    $connector = new PapayaModuleTwitterConnector();
    $options = array(
      '3239c62be16c65bc389f45f95cfef6e8' => array(
        'access_token' => '12345', 
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij'
      )
    );
    $plugins = $this->getMockBuilder('PapayaPlugins')->getMock();
    $plugins->options = $options;
    $connector->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));
    $this->assertEquals(
      array(
        'access_token' => '12345', 
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij'
      ),
      $connector->getOptions()
    );
  }

  /**
  * @covers PapayaModuleTwitterConnector::getTweets
  */
  public function testGetTweets() {
    $connector = new PapayaModuleTwitterConnector();
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $api
      ->expects($this->once())
      ->method('send')
      ->will($this->returnValue('{}'));
    $api
      ->expects($this->once())
      ->method('mode')
      ->with($this->equalTo('user_timeline'));
    $api
      ->expects($this->once())
      ->method('method')
      ->with($this->equalTo('GET'));
    $connector->api($api);
    $this->assertEquals(
      '{}',
      $connector->getTweets(
        'testuser',
        array(
          'count' => 10,
          'include_rts' => 0
        )
      )
    );
  }

  /**
  * @covers PapayaModuleTwitterConnector::update
  */
  public function testUpdate() {
    $connector = new PapayaModuleTwitterConnector();
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $api
      ->expects($this->once())
      ->method('update')
      ->will($this->returnValue('{}'));
    $connector->api($api);
    $this->assertTrue($connector->update('Test tweet.'));
  }
}

