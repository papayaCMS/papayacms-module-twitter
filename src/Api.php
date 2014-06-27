<?php
/**
* Twitter API helper
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
* @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
*/

/**
* Twitter API helper class
*
* This box module displays twitter statuses of a specified twitter user. The number of
* displayed statuses can also be speciefied in the content section of each box.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterApi {
  /**
  * OAuth access token
  * @var string
  */
  private $_accessToken = '';

  /**
  * OAuth access token secret
  * @var string
  */
  private $_accessSecret = '';

  /**
  * OAuth consumer key
  * @var string
  */
  private $_consumerKey = '';

  /**
  * OAuth consumer secret
  * @var string
  */
  private $_consumerSecret = '';

  /**
  * Request method
  * @var string
  */
  private $_method = 'GET';

  /**
  * URL to send requests to
  * @var string
  */
  private $_url = '';

  /**
  * Override URL (for testing purposes)
  * @var string
  */
  protected $_overrideUrl = '';

  /**
  * Current time
  * @var integer
  */
  private $_time = NULL;

  /**
  * Request parameters (for query string or post data)
  * @var PapayaModuleTwitterApiParameters
  */
  private $_parameters = NULL;

  /**
  * Available URLs for different request modes
  * @var array
  */
  private $_availableUrls = array(
    'user_timeline' => 'https://api.twitter.com/1.1/statuses/user_timeline.json',
    'update' => 'https://api.twitter.com/1.1/statuses/update.json'
  );

  /**
  * Current mode of operation
  * @var string
  */
  private $_mode = 'user_timeline';

  /**
  * Constructor
  *
  * Configuration must be an associative array, containing the
  * 'access_token', 'access_secret', 'consumer_key', and 'consumer_secret' keys
  * with their respective values.
  *
  * @param array $configuration
  * @param string $mode optional, default 'user_timeline'
  * @throws InvalidArgumentException
  */
  public function __construct($configuration, $mode = 'user_timeline') {
    if (!is_array($configuration)) {
      throw new InvalidArgumentException('Array with OAuth configuration data expected.');
    }
    $this->setConfiguration($configuration);
    $this->mode($mode);
  }

  /**
  * Set the API configuration
  *
  * @param array $configuration
  * @throws InvalidArgumentException
  */
  public function setConfiguration($configuration) {
    $keys = array('access_token', 'access_secret', 'consumer_key', 'consumer_secret');
    foreach ($keys as $key) {
      if (!isset($configuration[$key])) {
        throw new InvalidArgumentException(sprintf('Expected key %s not found.', $key));
      }
    }
    $this->_accessToken = $configuration['access_token'];
    $this->_accessSecret = $configuration['access_secret'];
    $this->_consumerKey = $configuration['consumer_key'];
    $this->_consumerSecret = $configuration['consumer_secret'];
  }

  /**
  * Set/get the URL
  *
  * @param string $url optional, default NULL
  * @return string
  */
  public function url($url = NULL) {
    if (!empty($this->_overrideUrl)) {
      return $this->_overrideUrl;
    }
    if ($url !== NULL) {
      $this->_url = $url;
    }
    return $this->_url;
  }

  /**
  * Set/get the mode of operations
  *
  * If setting another mode, the URL must be adjusted as well.
  *
  */
  public function mode($mode = NULL) {
    if ($mode !== NULL) {
      if (array_key_exists($mode, $this->_availableUrls)) {
        $this->_mode = $mode;
      } else {
        $this->_mode = 'user_timeline';
      }
      $this->url($this->_availableUrls[$this->_mode]);
    }
    return $this->_mode;
  }

  /**
  * Get (and optionally override) system time
  *
  * @param integer $time optional, default NULL
  * @return integer
  */
  public function time($time = NULL) {
    if ($time !== NULL) {
      $this->_time = $time;
    }
    $result = time();
    if ($this->_time !== NULL) {
      $result = $this->_time;
    }
    return $result;
  }

  /**
  * Set/get request method
  *
  * @param string $method optional, default NULL
  * @return string
  */
  public function method($method = NULL) {
    if ($method !== NULL && in_array(strtoupper($method), array('GET', 'POST'))) {
      $this->_method = strtoupper($method);
    }
    return $this->_method;
  }

  /**
   * Set/get parameters
   *
   * @param array|PapayaModuleTwitterApiParameters $parameters
   * @param boolean $replace optional, default TRUE
   * @return PapayaModuleTwitterApiParameters
   */
  public function parameters($parameters = NULL, $replace = TRUE) {
    if ($parameters instanceof PapayaModuleTwitterApiParameters) {
      $this->_parameters = $parameters;
    } elseif ($this->_parameters === NULL) {
      $this->_parameters = new PapayaModuleTwitterApiParameters();
    }
    if (is_array($parameters)) {
      $this->_parameters->data($parameters, $replace);
    }
    return $this->_parameters;
  }

  /**
  * Get the base info to be signed using the composite key
  *
  * @param array $oauth
  * @return string
  */
  public function getBaseInfo($oauth) {
    $result = array();
    ksort($oauth);
    foreach ($oauth as $key => $value) {
      $result[] = sprintf('%s=%s', $key, $value);
    }
    return sprintf(
      '%s&%s&%s',
      $this->_method,
      rawurlencode($this->url()),
      rawurlencode(implode('&', $result))
    );
  }

  /**
  * Create OAuth authorization data
  *
  * @see https://dev.twitter.com/docs/api/1.1
  *
  * @return array
  */
  public function getOauth() {
    $oauth = array(
      'oauth_consumer_key' => $this->_consumerKey,
      'oauth_nonce' => $this->time(),
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_token' => $this->_accessToken,
      'oauth_timestamp' => $this->time(),
      'oauth_version' => '1.0'
    );
    $oauth = array_merge($oauth, $this->parameters()->data());
    $baseInfo = $this->getBaseInfo($oauth);
    $compositeKey = sprintf(
      '%s&%s',
      rawurlencode($this->_consumerSecret),
      rawurlencode($this->_accessSecret)
    );
    $signature = base64_encode(hash_hmac('sha1', $baseInfo, $compositeKey, TRUE));
    $oauth['oauth_signature'] = $signature;
    return $oauth;
  }

  /**
  * Create authorization header
  *
  * @see https://dev.twitter.com/docs/api/1.1
  *
  * @return string
  */
  public function getAuthHeader() {
    $oauth = $this->getOauth();
    $result = 'OAuth ';
    $fields = array();
    foreach ($oauth as $key => $value) {
      $fields[] = sprintf('%s="%s"', $key, rawurlencode($value));
    }
    $result .= implode(', ', $fields);
    return $result;
  }

  /**
  * Send the request
  *
  * @return mixed string JSON on success, boolean FALSE otherwise
  */
  public function send() {
    $parameters = $this->parameters();
    $header = 'Authorization: '.$this->getAuthHeader()."\r\n";
    if (count($parameters) > 0 && $this->method() == 'GET') {
      $this->url($this->url().'?'.$parameters->__toString());
    }
    $url = $this->url();
    $streamOptions = array(
      'method' => $this->method(),
      'header' => $header,
      'verify_peer' => FALSE
    );
    if (count($this->parameters()) > 0 && $this->method() == 'POST') {
      $streamOptions['content'] = $this->parameters()->__toString();
    }
    $context = stream_context_create(
      array('http' => $streamOptions)
    );
    $json = FALSE;
    if ($stream = @fopen($url, 'r', FALSE, $context)) {
      $json = stream_get_contents($stream);
      fclose($stream);
    }
    return $json;
  }

  /**
  * Update timeline (tweet something)
  *
  * @param string $text The text to be tweeted
  * @return mixed string JSON on success, boolean FALSE otherwise
  */
  public function update($text) {
    $previousMode = $this->mode();
    $this->mode('update');
    $this->method('POST');
    $text = rawurlencode(substr($text, 0, 140));
    $body = sprintf('status=%s', $text);
    $this->parameters(array('status' => $text));
    $header = 'Authorization: '.$this->getAuthHeader()."\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= 'Content-Length: '.strlen($body)."\r\n";
    $url = $this->url();
    $streamOptions = array(
      'method' => $this->method(),
      'header' => $header,
      'verify_peer' => FALSE,
      'content' => $body
    );
    $context = stream_context_create(
      array('http' => $streamOptions)
    );
    $json = FALSE;
    if ($stream = fopen($url, 'r', FALSE, $context)) {
      $json = stream_get_contents($stream);
      fclose($stream);
    }
    $this->mode($previousMode);
    return $json;
  }
}