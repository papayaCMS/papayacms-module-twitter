<?php
require_once(dirname(__FILE__).'/../bootstrap.php');

class PapayaModuleTwitterBoxBaseTest extends PapayaTestCase {

  private function getPapayaCacheServiceObjectFixture($additionalMethods = NULL) {
    $methods = array(
      'setConfiguration', 'verify', 'write', 'read', 'exists', 'delete', 'created'
    );
    if (isset($additionalMethods)) {
      $methods = array_merge($methods, $additionalMethods);
    }
    return $this->getMock('PapayaCacheService', $methods);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setOwner
  */
  public function testSetOwner() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $owner = $this
      ->getMockBuilder('base_plugin')
      ->disableOriginalConstructor()
      ->getMock();
    $baseObject->setOwner($owner);
    $this->assertAttributeSame($owner, '_owner', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setBoxData
  */
  public function testSetBoxData() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $data = array('title' => 'Tweets');
    $baseObject->setBoxData($data);
    $this->assertAttributeEquals($data, '_data', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getBoxXml
  */
  public function testGetBoxXml() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $data = array(
      'title' => 'Tweets',
      'screen_name' => 'TwitterUser',
      'follow_caption' => 'Follow me',
      'count' => 5,
      'include_rts' => 0,
      'cache_time' => 500,
      'link_replies' => 1,
      'link_tags' => 1,
      'link_urls' => 1,
      'remove_link_protocols' => 1,
      'link_mailaddresses' => 1,
      'access_token' => '12345',
      'access_secret' => '67890',
      'consumer_key' => 'abcde',
      'consumer_secret' => 'fghij'
    );
    $baseObject->setBoxData($data);
    $object = new stdClass();
    $object->id = '12345';
    $object->created_at = 'Thu May 23 10:12:27 +0000 2013';
    $object->text = 'Some test text';
    $object->source = 'Sample Twitter Client 1.0';
    $object->in_reply_to_user_id = '56789';
    $object->in_reply_to_status_id = '67890';
    $object->in_reply_to_screen_name = 'SomeOtherTwitterUser';
    $json = json_encode(array($object));
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue($json));
    $baseObject->setCacheService($cacheService);
    $owner = $this
      ->getMockBuilder('base_plugin')
      ->disableOriginalConstructor()
      ->getMock();
    $owner
      ->expects($this->any())
      ->method('getXHTMLString')
      ->will($this->returnArgument(0));
    $baseObject->setOwner($owner);
    $this->assertXmlStringEqualsXmlString(
      '<twitter screen-name="TwitterUser">
        <title>Tweets</title>
        <follow-link href="http://twitter.com/TwitterUser">Follow me</follow-link>
        <status id ="12345" created="'.date('Y-m-d H:i:s', strtotime($object->created_at)).'">
          <text>Some test text</text>
          <source>Sample Twitter Client 1.0</source>
          <reply-to user-id="56789" status-id="67890" screen-name="SomeOtherTwitterUser" />
        </status>
      </twitter>',
      $baseObject->getBoxXml()
    );
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiJson
  */
  public function testGetApiJsonFromCache() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $object = new stdClass();
    $object->text = 'Some test text';
    $json = json_encode(array($baseObject));
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue($json));
    $baseObject->setCacheService($cacheService);
    $baseObject->setBoxData(
      array(
        'access_token' => '12345',
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij',
        'screen_name' => 'SomeTwitterUser',
        'count' => 1,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals($json, $baseObject->getApiJson());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiJson
  */
  public function testGetApiJsonFromActualApi() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $object = new stdClass();
    $object->text = 'Some test text';
    $json = json_encode(array($baseObject));
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $api
      ->expects($this->once())
      ->method('send')
      ->will($this->returnValue($json));
    $baseObject->twitterApi($api);
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue(FALSE));
    $baseObject->setCacheService($cacheService);
    $baseObject->setBoxData(
      array(
        'access_token' => '12345',
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij',
        'screen_name' => 'SomeTwitterUser',
        'count' => 1,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals($json, $baseObject->getApiJson());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiJson
  */
  public function testGetApiJsonFromCacheAsFallback() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $object = new stdClass();
    $object->text = 'Some test text';
    $json = json_encode(array($baseObject));
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $api
      ->expects($this->once())
      ->method('send')
      ->will($this->returnValue(FALSE));
    $baseObject->twitterApi($api);
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->exactly(2))
      ->method('read')
      ->will(
          $this->onConsecutiveCalls(
            $this->returnValue(FALSE),
            $this->returnValue($json)
          )
        );
    $baseObject->setCacheService($cacheService);
    $baseObject->setBoxData(
      array(
        'access_token' => '12345',
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij',
        'screen_name' => 'SomeTwitterUser',
        'count' => 1,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals($json, $baseObject->getApiJson());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getCacheService
  */
  public function testGetCacheService() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $baseObject->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(array('PAPAYA_CACHE_DATA' => TRUE))
        )
      )
    );
    $cacheService = $baseObject->getCacheService();
    $this->assertInstanceOf('PapayaCacheService', $cacheService);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setCacheService
  */
  public function testSetCacheService() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $baseObject->setCacheService($cacheService);
    $this->assertAttributeSame($cacheService, '_cacheService', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::_addTwitterLinks
  */
  public function testAddTwitterLinks() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $baseObject->setBoxData(
      array(
        'remove_link_protocols' => 0,
        'link_replies' => 1,
        'link_tags' => 1,
        'link_urls' => 1,
        'link_mailaddresses' => 1
      )
    );
    $this->assertEquals(
      '<a class="twitterReply" target="_blank" href="http://twitter.com/User">@User</a>'.
      ': More info about '.
      '<a class="twitterHashtag" target="_blank"'.
      ' href="http://twitter.com/search?q=subject">#subject</a> at '.
      '<a class="twitterLink" href="http://bit.ly/subject" target="_blank">http://bit.ly/subject</a>'.
      ' or mail '.
      '<a class="twitterMail" href="mailto:info@subject.info">info@subject.info</a>',
      $baseObject->_addTwitterLinks(
        '@User: More info about #subject at http://bit.ly/subject or mail info@subject.info'
      )
    );
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::twitterApi
  */
  public function testTwitterApiSet() {
    $baseObject = new PapayaModuleTwitterBoxBase();
    $api = $this
      ->getMockBuilder('PapayaModuleTwitterApi')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertEquals($api, $baseObject->twitterApi($api));
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::twitterApi
  */
  public function testTwitterApiInitialize() {
    $baseObject = new PapayaModuleTwitterBoxBase();
    $baseObject->setBoxData(
      array(
        'access_token' => '12345',
        'access_secret' => '67890',
        'consumer_key' => 'abcde',
        'consumer_secret' => 'fghij'
      )
    );
    $this->assertInstanceOf('PapayaModuleTwitterApi', $baseObject->twitterApi());
  }
}

/**
* Used to set the protected methods of the actual TwitterBoxBase class public
*/
class PapayaModuleTwitterBoxBase_TestProxy extends PapayaModuleTwitterBoxBase {
  public function getApiJson() {
    return parent::getApiJson();
  }

  public function getCacheService() {
    return parent::getCacheService();
  }

  public function  _addTwitterLinks($text) {
    return parent::_addTwitterLinks($text);
  }
}
