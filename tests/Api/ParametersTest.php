<?php
require_once(dirname(__FILE__).'/../bootstrap.php');

class PapayaModuleTwitterApiParametersTest extends PapayaTestCase {
  /**
  * @covers PapayaModuleTwitterApiParameters::__construct
  */
  public function testConstruct() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertInstanceOf('PapayaModuleTwitterApiParameters', $parameters);
    $this->assertAttributeEquals(array('foo' => 'bar'), '_parameters', $parameters);
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::data
  */
  public function testData() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar', 'baz' => 'tux'),
      $parameters->data(array('baz' => 'tux'))
    );
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::count
  */
  public function testCount() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertEquals(1, $parameters->count());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::current
  */
  public function testCurrent() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertEquals('bar', $parameters->current());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::key
  */
  public function testKey() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertEquals('foo', $parameters->key());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::next
  */
  public function testNext() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar', 'baz' => 'tux'));
    $parameters->next();
    $this->assertEquals('baz', $parameters->key());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::offsetExists
  */
  public function testOffsetExists() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertTrue($parameters->offsetExists('foo'));
    $this->assertFalse($parameters->offsetExists('baz'));
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::offsetGet
  */
  public function testOffsetGet() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertEquals('bar', $parameters->offsetGet('foo'));
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::offsetSet
  */
  public function testOffsetSet() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $parameters->offsetSet('foo', 'tux');
    $this->assertEquals('tux', $parameters['foo']);
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::offsetUnset
  */
  public function testOffsetUnset() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $parameters->offsetUnset('foo');
    $this->assertFalse($parameters->offsetExists('foo'));
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::rewind
  */
  public function testRewind() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar', 'baz' => 'tux'));
    $parameters->next();
    $parameters->rewind();
    $this->assertEquals('foo', $parameters->key());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::valid
  */
  public function testValid() {
    $parameters = new PapayaModuleTwitterApiParameters(array('foo' => 'bar'));
    $this->assertTrue($parameters->valid());
    $parameters->next();
    $this->assertFalse($parameters->valid());
  }

  /**
  * @covers PapayaModuleTwitterApiParameters::__toString
  */
  public function testToString() {
    $parameters = new PapayaModuleTwitterApiParameters(
      array('foo' => 'The Bar', 'baz' => 'Tux!')
    );
    $this->assertEquals('foo=The%20Bar&baz=Tux%21', $parameters->__toString());
  }
}