<?php

/**
 * ---------------------------------------------------------------------
 * Google Account Authentication APIs class
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

define('GOOGLE_ACCOUNT_CLIENTLOGIN_SOURCE', 'example-test-1');

require_once 'HTTP/Request.php';

/**
 * Google Account Authentication APIs class
 *
 * @version    0.2 (alpha) released 2006/10/12
 * @author     ucb.rcdtokyo http://www.rcdtokyo.com/ucb/
 * @license    GNU LGPL v2.1+ http://www.gnu.org/licenses/lgpl.html
 * @see        http://code.google.com/apis/accounts/Authentication.html
 */
class Google_Account
{
    /**
     * Current token.
     *
     * @var string
     * @access public
     */
    var $token = null;

    /**
     * Name of the authentication API to acquire current token.
     * The value is either "clientlogin" or "authsub".
     *
     * @var string
     * @access public
     */
    var $authType = null;

    /**
     * Hashed structure available if the response body
     * of the last request is key=value format plain text.
     *
     * @var array
     * @access public
     */
    var $keyValuePairs = array();

    /**
     * The service name for the ClientLogin authentication.
     *
     * @var string
     * @access protected
     */
    var $serviceName = null;

    /**
     * The scope URL for the AuthSub authentication.
     *
     * @var string
     * @access protected
     */
    var $scopeUrl = null;

    /**
     * HTTP_Request instance.
     *
     * @var object
     * @access protected
     */
    var $request;

    /**
     * An array contains the default parameters of HTTP_Request.
     * The allowRedirects shall be always TRUE.
     *
     * @var array
     * @access protected
     */
    var $requestParams = array('allowRedirects' => true);

    /**
     * Constructor for PHP4.
     * The first parameter is the service name for the ClientLogin.
     * The second parameter is the scope URL for the AuthSub.
     *
     * @access public
     */
/*
    function Google_Account($service_name = null, $scope_url = null)
    {
        $this->__construct($service_name, $scope_url);
    }
*/

    /**
     * Constructor.
     * The first parameter is the service name for the ClientLogin.
     * The second parameter is the scope URL for the AuthSub.
     *
     * @access public
     */
    function __construct($service_name = null, $scope_url = null)
    {
        if (!empty($service_name)) {
            $this->serviceName = $service_name;
        }
        if (!empty($scope_url)) {
            $this->scopeUrl = $scope_url;
        }
        $this->request = new HTTP_Request;
    }

    /**
     * Request ClientLogin token.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $captcha_token
     * @param  string  $captcha_answer
     * @param  string  $account_type
     * @return boolean
     * @access public
     */
    function requestClientLogin($username, $password, $captcha_token = null, $captcha_answer = null, $account_type = null)
    {
        $this->request->HTTP_Request(
            'https://www.google.com/accounts/ClientLogin',
            $this->requestParams
        );
        $this->request->setMethod('POST');
        $this->request->addPostData('Email', $username);
        $this->request->addPostData('Passwd', $password);
        $this->request->addPostData('source', GOOGLE_ACCOUNT_CLIENTLOGIN_SOURCE);
        $this->request->addPostData('service', $this->serviceName);
        if (!empty($captcha_token)) {
            $this->request->addPostData('logintoken', $captcha_token);
        }
        if (!empty($captcha_answer)) {
            $this->request->addPostData('logincaptcha', $captcha_answer);
        }
        if (!empty($account_type)) {
            $this->request->addPostData('accountType', $account_type);
        } else {
            $this->request->addPostData('accountType', 'HOSTED_OR_GOOGLE');
        }
        $this->request->sendRequest();
        $this->keyValuePairs = $this->findKeyValuePairs($this->request->getResponseBody());
        switch ($this->request->getResponseCode()) {
            case 200:
                if (!isset($this->keyValuePairs['auth'])) {
                    return false;
                }
                $this->token = $this->keyValuePairs['auth'];
                $this->authType = 'clientlogin';
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Return the URL of the Google Accounts "Access Request" webpage.
     *
     * @param  string  $next
     * @param  string  $scope_url
     * @param  boolean $session
     * @return string
     * @access public
     */
    function getAuthSubRequestUrl($next, $scope_url = null, $session = true)
    {
        $url = new Net_URL('https://www.google.com/accounts/AuthSubRequest');
        $url->addQueryString('next', $next);
        $url->addQueryString('scope', (!empty($scope_url)? $scope_url: $this->scopeUrl));
        $url->addQueryString('session', $session? 1: 0);
        return preg_replace('/&(?!(?:[a-zA-Z]+|#[0-9]+|#x[0-9a-fA-F]+);)/', '&amp;', $url->getURL());
    }

    /**
     * Exchange one-time/single-use AuthSub token
     * with multi-use session token.
     *
     * @param  string  $token
     * @return boolean
     * @access public
     */
    function requestAuthSubSessionToken($token)
    {
        $this->request->HTTP_Request(
            'https://www.google.com/accounts/AuthSubSessionToken',
            $this->requestParams
        );
        $this->request->addHeader('Authorization', "AuthSub token=\"$token\"");
        $this->request->sendRequest();
        $this->keyValuePairs = $this->findKeyValuePairs($this->request->getResponseBody());
        switch ($this->request->getResponseCode()) {
            case 200:
                if (!isset($this->keyValuePairs['token'])) {
                    return false;
                }
                $this->token = $this->keyValuePairs['token'];
                $this->authType = 'authsub';
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Validate AuthSub token.
     *
     * @param  string  $token
     * @return boolean
     * @access public
     */
    function requestAuthSubTokenInfo($token)
    {
        $this->request->HTTP_Request(
            'https://www.google.com/accounts/AuthSubTokenInfo',
            $this->requestParams
        );
        $this->request->addHeader('Authorization', "AuthSub token=\"$token\"");
        $this->request->sendRequest();
        $this->keyValuePairs = $this->findKeyValuePairs($this->request->getResponseBody());
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Revoke AuthSub token.
     *
     * @param  string  $token
     * @return boolean
     * @access public
     */
    function requestAuthSubRevokeToken($token)
    {
        $this->request->HTTP_Request(
            'https://www.google.com/accounts/AuthSubRevokeToken',
            $this->requestParams
        );
        $this->request->addHeader('Authorization', "AuthSub token=\"$token\"");
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
     * Alias to HTTP_Request::getResponseCode().
     *
     * @return mixed
     * @access public
     */
    function getResponseCode()
    {
        return $this->request->getResponseCode();
    }

    /**
     * Alias to HTTP_Request::getResponseHeader().
     *
     * @param  string
     * @return mixed
     * @access public
     */
    function getResponseHeader($name = null)
    {
        return $this->request->getResponseHeader($name);
    }

    /**
     * Alias to HTTP_Request::getResponseBody().
     *
     * @return mixed
     * @access public
     */
    function getResponseBody()
    {
        return $this->request->getResponseBody();
    }

    /**
     * @param  string  $data
     * @return array
     * @access protected
     */
    function findKeyValuePairs($data)
    {
        $pairs = array();
        $lines = preg_split('/(?:\r\n|\n|\r)/', $data);
        foreach ($lines as $line) {
            if (preg_match('/^\s*([^\s=]+)\s*=\s*(.+)$/', $line, $matches)) {
                $pairs[strtolower($matches[1])] = trim($matches[2]);
            }
        }
        return $pairs;
    }
}

?>
