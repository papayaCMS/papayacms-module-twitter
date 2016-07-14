<?php
/**
* Twitter Statuses Box, base class
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
* @version $Id: Base.php 39770 2014-04-30 15:30:58Z weinert $
*/

/**
* Twitter Statuses Box Module
*
* This box module displays twitter statuses of a specified twitter user. The number of
* displayed statuses can also be speciefied in the content section of each box.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterBoxBase extends PapayaObject {
  /**
  * Twitter API object
  * @var PapayaModuleTwitterApi
  */
  protected $_twitterApi = NULL;

  /**
  * papaya Cache object
  * @var PapayaCache service
  */
  protected $_cacheService = NULL;

  /**
  * Owner object
  * @var PapayaModuleTwitterBox
  */
  protected $_owner = NULL;

  /**
  * Page configuration data
  * @var array
  */
  protected $_data = array();

  /**
  * Set owner object
  *
  * @param PapayaModuleTwitterBox $owner
  */
  public function setOwner($owner) {
    $this->_owner = $owner;
  }

  /**
  * Set page configuration data
  *
  * @param array $data
  */
  public function setBoxData($data) {
    $this->_data = $data;
  }

  /**
  * Get box output XML
  *
  * @return string XML
  */
  public function getBoxXml() {
    $result = '';
    if (!empty($this->_data['title'])) {
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->_data['title'])
      );
    }
    $result .= sprintf(
      '<follow-link href="%s%s">%s</follow-link>',
      'http://twitter.com/',
      urlencode($this->_data['screen_name']),
      papaya_strings::escapeHTMLChars($this->_data['follow_caption'])
    );
    $apiJson = $this->getApiJson();
    if (!empty($apiJson)) {
      $data = json_decode($apiJson);
      if (!empty($data) && is_array($data)) {
        foreach ($data as $status) {
          $created = strtotime($status->created_at);
          $result .= sprintf(
            '<status id ="%s" created="%s">',
            papaya_strings::escapeHTMLChars($status->id),
            papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', $created))
          );
          $text = $status->text;

          $result .= sprintf(
            '<text>%s</text>',
            $this->_owner->getXHTMLString($this->_addTwitterLinks($text))
          );
          $result .= sprintf(
            '<source>%s</source>',
            $this->_owner->getXHTMLString($status->source)
          );
          if (!empty($status->in_reply_to_user_id)) {
            $result .= sprintf(
              '<reply-to user-id="%s" status-id="%s" screen-name="%s" />',
              papaya_strings::escapeHTMLChars(
                $status->in_reply_to_user_id
              ),
              papaya_strings::escapeHTMLChars(
                $status->in_reply_to_status_id
              ),
              papaya_strings::escapeHTMLChars(
                $status->in_reply_to_screen_name
              )
            );
          }
          $result .= '</status>';
        }
      }
    }
    return sprintf(
      '<twitter screen-name="%s">%s</twitter>',
      $this->_data['screen_name'],
      $result
    );
  }

  /**
  * Get API JSON
  *
  * Looks for cached response JSON. If the cached data is out of date, a new API request will be
  * sent, and the retrieved data will be added to the cache. If the request fails too, we'll try to
  * get older cache data as a fallback. The maximum time for the regular cache is defined in the
  * edit fields with the key <var>cache_time</var>. The fallback cache time is set to three weeks.
  *
  * @return string JSON
  */
  protected function getApiJson() {
    $cache = $this->getCacheService();
    $arrParams = array(
      'screen_name' => $this->_data['screen_name'],
      'count' => $this->_data['count'],
      'include_rts' => $this->_data['include_rts']
    );
    if (
      is_object($cache) &&
      $json = $cache->read(
        'twitter',
        $this->_data['screen_name'],
        $arrParams,
        $this->_data['cache_time']
      )
    ) {
      return $json;
    }
    $twitter = $this->twitterApi();
    $twitter->parameters($arrParams);
    $json = '';
    $responseData = $twitter->send();
    if (!empty($responseData)) {
      $json = $responseData;
    }

    // Fallback for broken twitter API
    if (is_object($cache) && !$json) {
      $json = $cache->read('twitter', $this->_data['screen_name'], $arrParams, 604800);
    }
    // Refresh cache
    if (is_object($cache) && $json) {
      $cache->write('twitter', $this->_data['screen_name'], $arrParams, $json);
    }
    return $json;
  }

  /**
  * Add Twitter Links
  *
  * Parse a twitter status message and add links for replys, hashtags and urls.
  * What should be linked, can be configured in the Edit fields...
  *
  * @param string $text
  * @return string
  */
  protected function _addTwitterLinks($text) {
    $pattern = array();
    $replacement = array();
    if ($this->_data['link_replies'] == 1) {
      $pattern[] = "((^|[\\s])@([a-zA-Z0-9_]{2,15}))";
      $replacement[] = '$1<a class="twitterReply"'.
        ' target="_blank" href="http://twitter.com/$2">@$2</a>';
    }
    if ($this->_data['link_tags'] == 1) {
      $pattern[] = "((^|[\\s])#([^\\s.,;!?]+))";
      $replacement[] = '$1<a class="twitterHashtag"'.
        ' target="_blank" href="http://twitter.com/search?q=$2">#$2</a>';
    }
    if ($this->_data['link_urls'] == 1) {
      // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
      // xxxx can only be alpha characters.
      // yyyy is anything up to the first space, newline, comma, double quote or <
      $pattern[] = "(
        (^|[\\s])
        ([\w]+?://)
        (([\w\#$%&~.\-;:=,?@\[\]+]*)
        (/?)
        ([\w\#$%&~.\-;:=,?@\[\]+/]*))
        )x";
      if ($this->_data['remove_link_protocols'] == 1) {
        $replacement[] = '$1<a class="twitterLink" href="$2$3" target="_blank">$3</a>';
      } else {
        $replacement[] = '$1<a class="twitterLink" href="$2$3" target="_blank">$2$3</a>';
      }
      // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
      // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
      // zzzz is optional.. will contain everything up to the first space, newline,
      // comma, double quote or <.
      $pattern[] = "(
        (^|[\\s])
        ((www|ftp)\.[\w\#$%&~.\-;:=,?@\[\]+]*)
        (/?)
        ([\w\#$%&~.\-;:=,?@\[\]+/]*)
        )x";
      $replacement[] = '$1<a class="twitterLink" href="http://$2" target="_blank">$2</a>';
    }
    if ($this->_data['link_mailaddresses'] == 1) {
      // matches an email@domain type address at the start of a line, or after a space.
      // Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
      $pattern[] = '((^|[\\s])((?:[a-z0-9&_.-]+?)@(?:[\\pL-]+\\.)+[a-zA-Z]{2,}))u';
      $replacement[] = '$1<a class="twitterMail" href="mailto:$2">$2</a>';
    }
    return preg_replace($pattern, $replacement, $text);
  }

  /**
  * Return a cache service instance
  *
  * @return PapayaCacheService Cache Service
  */
  protected function getCacheService() {
    if ($this->_cacheService === NULL) {
      $this->setCacheService(
        PapayaCache::get(PapayaCache::DATA, $this->papaya()->options)
      );
    }
    return $this->_cacheService;
  }

  /**
   * Set the cache service object instanz
   *
   * @param $service
   */
  public function setCacheService($service) {
    $this->_cacheService = $service;
  }

  /**
  * Set/initialize/get Twitter API object
  *
  * @param PapayaModuleTwitterApi $api optional, default NULL
  * @return PapayaModuleTwitterApi
  */
  public function twitterApi($api = NULL) {
    if ($api !== NULL) {
      $this->_twitterApi = $api;
    } elseif ($this->_twitterApi === NULL) {
      $this->_twitterApi = new PapayaModuleTwitterApi($this->_data);
    }
    return $this->_twitterApi;
  }
}
