<?php
/**
* Twitter API parameters
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
* @version $Id: Parameters.php 38529 2013-06-26 13:59:13Z kersken $
*/

/**
* Twitter API parameters class
*
* This box module displays twitter statuses of a specified twitter user. The number of
* displayed statuses can also be speciefied in the content section of each box.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterApiParameters implements ArrayAccess, Iterator, Countable {
  /**
  * Internal array that contains the parameters
  * @var array
  */
  private $_parameters = array();

  /**
  * Cursor that points to the current parameter
  * @var integer
  */
  private $_current = 0;

  /**
  * Constructor
  *
  * You can optionally add an array that will be the initial list of fields
  *
  * @param array $data optional, default array()
  */
  public function __construct($data = array()) {
    $this->_parameters = $data;
  }

  /**
  * Add/replace/get data
  *
  * @param array $data optional, default NULL
  * @param boolean $replace optional, default TRUE
  * @return array
  */
  public function data($data = NULL, $replace = TRUE) {
    if ($data !== NULL) {
      foreach ($data as $key => $value) {
        if ($replace || !isset($this->_parameters[$key])) {
          $this->_parameters[$key] = $value;
        }
      }
    }
    return $this->_parameters;
  }

  /**
  * Get the current number of parameters
  *
  * @return integer
  */
  public function count() {
    return count($this->_parameters);
  }

  /**
  * Get the current element
  *
  * @return mixed
  */
  public function current() {
    return $this->_parameters[$this->key()];
  }

  /**
  * Get the current key
  *
  * @return mixed
   */
  public function key() {
    $keys = array_keys($this->_parameters);
    $key = NULL;
    if ($this->valid()) {
      $key = $keys[$this->_current];
    }
    return $key;
  }

  /**
  * Set the cursor to the next element
  */
  public function next() {
    $this->_current++;
  }

  /**
  * Check whether an element exists at a specific offset
  *
  * @param mixed $offset
  * @return boolean TRUE if it exists, FALSE otherwise
  */
  public function offsetExists($offset) {
    return isset($this->_parameters[$offset]);
  }

  /**
  * Get the value at a specific offset
  *
  * @param mixed $offset
  * @return mixed
  */
  public function offsetGet($offset) {
    return isset($this->_parameters[$offset]) ? $this->_parameters[$offset] : NULL;
  }

  /**
  * Set a value for the specified offset
  *
  * @param mixed $offset
  * @param mixed $value
  */
  public function offsetSet($offset, $value) {
    $this->_parameters[$offset] = $value;
  }

  /**
  * Unset the value at the specified offset
  *
  * @param mixed $offset
  */
  public function offsetUnset($offset) {
    unset($this->_parameters[$offset]);
  }

  /**
  * Set the cursor to the first element
  */
  public function rewind() {
    $this->_current = 0;
  }

  /**
  * Check whether an element exists at the cursor
  *
  * @return boolean TRUE if it exists, FALSE otherwise
  */
  public function valid() {
    $keys = array_keys($this->_parameters);
    return isset($keys[$this->_current]);
  }

  /**
  * Get the string representation of all fields
  *
  * The return value is suitable as a query string (without the leadin '?')
  * or as application/x-www-form-urlencoded post data
  *
  * @return string
  */
  public function __toString() {
    $output = array();
    foreach ($this->_parameters as $key => $value) {
      $output[] = sprintf('%s=%s', $key, rawurlencode($value));
    }
    $r = implode('&', $output);
    return $r;
  }
}