<?php
/**
* Twitter API connector
*
* @copyright by dimensional Software GmbH, Cologne, Germany - All rights reserved.
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
* @version $Id: Connector.php 39861 2014-06-27 09:38:58Z kersken $
*/

/**
* Twitter API connector class
*
* Usage:
* $connector = $this->papaya()->plugins->get('3239c62be16c65bc389f45f95cfef6e8');
* $connector->getOptions();
* $connector->setConfiguration(
*   array(
*     'access_token' => 'Your access token',
*     'access_secret' => 'Your access token secret',
*     'consumer_key' => 'Your consumer key',
*     'consumer_secret' => 'Your consumer secret'
*   )
* );
* $connector->getTweets($screenName, $options = array());
*    Options for getTweets(): 'count' => number of tweets,
*                             'include_rts' => include retweets?
* $connector->update($message);
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterConnector extends base_connector {
  /**
  * The twitter API object to be used
  * @var PapayaModuleTwitterApi
  */
  private $_api = NULL;
  
  /**
  * Configuration array
  * @var array
  */
  private $_configuration = NULL;

  public $pluginOptionFields = array(
    'consumer_key' => array('Conumer Key', 'isNoHTML', FALSE, 'input', 255),
    'consumer_secret' => array('Consumer Secret', 'isNoHTML', FALSE, 'input', 255),
    'access_token' => array('Access Token', 'isNoHTML', FALSE, 'input', 255),
    'access_secret' => array('Access Token Secret', 'isNoHTML', FALSE, 'input', 255)
  );

  /**
  * Set/initialize/get the API object
  *
  * @param PapayaModuleTwitterApi $api optional, default NULL
  * @throws RuntimeException
  * @return PapayaModuleTwitterApi
  */
  public function api(PapayaModuleTwitterApi $api = NULL) {
    if ($api !== NULL) {
      $this->_api = $api;
    } else if ($this->_api === NULL) {
      if ($this->_configuration === NULL) {
        throw new RuntimeException('Twitter API cannot be used without configuration.');
      }
      $this->_api = new PapayaModuleTwitterApi($this->_configuration);
    }
    return $this->_api;
  }

  /**
  * Set configuration
  *
  * @param array $configuration optional
  * @throws InvalidArgumentException
  */
  public function setConfiguration($configuration) {
    $this->_configuration = $configuration;
    $this->api()->setConfiguration($configuration);
  }

  /**
  * Get the module's options
  *
  * @return array
  */
  public function getOptions() {
    $result = array();
    $options = $this->papaya()->plugins->options['3239c62be16c65bc389f45f95cfef6e8'];
    $fields = array('access_token', 'access_secret', 'consumer_key', 'consumer_secret');
    foreach ($fields as $key) {
      if (isset($options[$key]) && !empty($options[$key])) {
        $result[$key] = $options[$key];
      }
    }
    return $result;
  }

  /**
  * Get tweets of a specific user
  *
  * @param string $screenName
  * @param array $options optional, default array()
  * @return string JSON
  */
  public function getTweets($screenName, $options = array()) {
    $arrParams = array(
      'screen_name' => $screenName
    );
    if (isset($options['count'])) {
      $arrParams['count'] = $options['count'];
    }
    if (isset($options['include_rts'])) {
      $arrParams['include_rts'] = $options['include_rts'];
    }
    $api = $this->api();
    $api->mode('user_timeline');
    $api->method('GET');
    $api->parameters($arrParams);
    return $api->send();
  }

  /**
  * Send a tweet
  *
  * @param string $message
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function update($message) {
    $api = $this->api();
    return FALSE !== $api->update($message);
  }
}