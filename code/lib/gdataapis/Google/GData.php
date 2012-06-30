<?php

/**
 * ---------------------------------------------------------------------
 * Google Data APIs common class
 * ---------------------------------------------------------------------
 * PHP versions 4 and 5
 * ---------------------------------------------------------------------
 * LICENSE: This source file is subject to the GNU Lesser General Public
 * License as published by the Free Software Foundation;
 * either version 2.1 of the License, or any later version
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/lgpl.html
 * If you did not have a copy of the GNU Lesser General Public License
 * and are unable to obtain it through the web, please write to
 * the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 * ---------------------------------------------------------------------
 */

require_once 'Google/Account.php';

/**
 * Google Data APIs common class
 *
 * @version    0.2 (alpha) released 2006/10/12
 * @author     ucb.rcdtokyo http://www.rcdtokyo.com/ucb/
 * @license    GNU LGPL v2.1+ http://www.gnu.org/licenses/lgpl.html
 * @see        http://code.google.com/apis/gdata/
 */
class Google_GData extends Google_Account
{
    /**
     * The default feed URL
     * used when requesting a feed or inserting an entry.
     *
     * @var string
     * @access protected
     */
    var $feedUrl = null;

    /**
     * An array contains the default HTTP headers.
     * see setAdditionalHeader().
     *
     * @var array
     * @access protected
     */
    var $requestHeaders = array();

	/**
     * Set/change the default feed URL
     * used when requesting a feed or inserting an entry.
     *
	 * @param  string  $url
	 * @return void
	 * @aceess puiblic
	 */
/*
	function setFeedUrl($url)
	{
		$this->feedUrl = $url;
	}
*/

	/**
	 * Set/change the current token.
	 * The second parameter value must be "clientlogin" or "authsub".
	 *
	 * @param  string  $token
	 * @param  string  $auth_type
	 * @return void
	 * @aceess puiblic
	 */
	function setToken($token, $auth_type)
	{
		$this->token = $token;
		$this->authType = $auth_type;
	}

	/**
	 * May use to add API specific HTTP headers if any.
	 * 'X-Google-Key' at Google Base API for example.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @return void
	 * @aceess puiblic
	 */
	function setAdditionalHeader($key, $value)
	{
		$this->requestHeaders[$key] = $value;
	}

    /**
     * Request a feed.
     *
     * @param  array   $queries
     * @param  string  $feed_url
     * @return boolean
     * @access public
     */
    function requestFeed($queries = array(), $feed_url = null)
    {
        if (isset($feed_url)) {
            $this->request->HTTP_Request($feed_url, $this->requestParams);
        } else {
            $this->request->HTTP_Request($this->feedUrl, $this->requestParams);
        }
        $this->addAuthorizationHeader();
        $this->addAdditionalHeaders();
        if (!empty($queries)) {
            foreach ($queries as $key => $value) {
                $this->request->addQueryString($key, $value);
            }
        }
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Insert an entry.
     *
     * @param  string  $feed_data
     * @param  string  $feed_url
     * @return boolean
     * @access public
     */
    function insert($feed_data, $feed_url = null)
    {
        if (isset($feed_url)) {
            $this->request->HTTP_Request($feed_url, $this->requestParams);
        } else {
            $this->request->HTTP_Request($this->feedUrl, $this->requestParams);
        }
        $this->addAuthorizationHeader();
        $this->addAdditionalHeaders();
        $this->request->setMethod('POST');
        $this->request->addHeader('Content-Type', 'application/atom+xml');
        $this->request->setBody($feed_data);
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 201:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Update an entry.
     *
     * @param  string  $feed_data
     * @param  string  $edit_url
     * @return boolean
     * @access public
     */
    function update($feed_data, $edit_url)
    {
        $this->request->HTTP_Request($edit_url, $this->requestParams);
        $this->addAuthorizationHeader();
        $this->addAdditionalHeaders();
        // HTTP_Request as of v1.45 discards Content-Type header if the method is PUT.
        // So we have to use the alternative method.
        $this->request->setMethod('POST');
        $this->request->addHeader('X-Http-Method-Override', 'PUT');
        $this->request->addHeader('Content-Type', 'application/atom+xml');
        $this->request->setBody($feed_data);
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Delete an entry.
     *
     * @param  string  $edit_url
     * @return boolean
     * @access public
     */
    function delete($edit_url)
    {
        $this->request->HTTP_Request($edit_url, $this->requestParams);
        $this->addAuthorizationHeader();
        $this->addAdditionalHeaders();
        $this->request->setMethod('DELETE');
        /*
        // Use following instead of above if HTTP DELETE is not allowed.
        $this->request->setMethod('POST');
        $this->request->addHeader('X-Http-Method-Override', 'DELETE');
        $this->request->setBody('dummy');
        */
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Perform a batch processing.
     *
     * @param  string  $feed_data
     * @param  string  $batch_url
     * @return boolean
     * @access public
     */
    function batch($feed_data, $batch_url)
    {
        $this->request->HTTP_Request($batch_url, $this->requestParams);
        $this->addAuthorizationHeader();
        $this->addAdditionalHeaders();
        $this->request->setMethod('POST');
        $this->request->addHeader('Content-Type', 'application/atom+xml');
        $this->request->setBody($feed_data);
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * @return boolean
     * @access protected
     */
    function addAuthorizationHeader()
    {
        if (isset($this->token) and isset($this->authType)) {
            switch ($this->authType) {
                case 'authsub':
                    $this->request->addHeader(
                        'Authorization',
                        "AuthSub token=\"$this->token\""
                    );
                    break;
                default:
                    $this->request->addHeader(
                        'Authorization',
                        "GoogleLogin auth=$this->token"
                    );
            }
            return true;
        }
        return false;
    }

    /**
     * @return void
     * @aceess protected
     */
    function addAdditionalHeaders()
    {
        if (!empty($this->requestHeaders)) {
            foreach ($this->requestHeaders as $key => $value) {
                $this->request->addHeader($key, $value);
            }
        }
    }
}

?>
