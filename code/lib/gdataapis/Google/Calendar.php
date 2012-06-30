<?php

/**
 * ---------------------------------------------------------------------
 * Google Calendar Data API specific extended class
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

require_once 'Google/GData.php';

/**
 * Google Calendar Data API specific extended class
 *
 * @version    0.2 (alpha) released 2006/10/12
 * @author     ucb.rcdtokyo http://www.rcdtokyo.com/ucb/
 * @license    GNU LGPL v2.1+ http://www.gnu.org/licenses/lgpl.html
 * @see        http://code.google.com/apis/gdata/calendar.html
 *
 * Example A. - Request a feed with magicCookie (without authentication)
 * <code>
 * $username = 'example@google.com';
 * $magic_cookie = 'private-magic-cookie';
 *
 * $service = new Google_Calendar;
 * $service->setFeedUrl(array(), '', 'basic', $magic_cookie, $username);
 * if (!$service->requestFeed()) {
 *   exit("Requesting a feed has failed.\n".$service->getResponseBody());
 * }
 * header('Content-Type:application/xml;charset=UTF-8');
 * echo $service->getResponseBody();
 * </code>
 *
 * Example B. - Request a feed with authentication
 * <code>
 * $username = 'example@gmail.com';
 * $password = 'password';
 *
 * $service = new Google_Calendar;
 * if (!$service->requestClientLogin($username, $password)) {
 *   exit("ClientLogin has failed.\n".$service->getResponseBody());
 * }
 * if (!$service->requestFeed()) {
 *   exit("Requesting a feed has failed.\n".$service->getResponseBody());
 * }
 * header('Content-Type:application/xml;charset=UTF-8');
 * echo $service->getResponseBody();
 * </code>
 */
class Google_Calendar extends Google_GData
{
    /**
     * The service name for the ClientLogin authentication.
     * This must be "cl" for Google Calendar Data API.
     *
     * @var string
     * @access protected
     */
    var $serviceName = 'cl';

    /**
     * The scope URL for the AuthSub authentication.
     * This must be "http://www.google.com/calendar/feeds/"
     * for Google Calendar Data API.
     *
     * @var string
     * @access protected
     */
    var $scopeUrl = 'http://www.google.com/calendar/feeds/';

    /**
     * The default feed URL
     * used when requesting a feed or inserting an entry.
     *
     * @var string
     * @access protected
     */
    var $feedUrl = 'http://www.google.com/calendar/feeds/default/private/full';

    /**
     * Set/change the default feed URL
     * used when requesting a feed or inserting an entry.
     *
     * @param  array   $queries
     * @param  string  $entry_id
     * @param  string  $projection
     * @param  string  $visibility
     * @param  string  $username
     * @return void
     * @access public
     */
    function setFeedUrl($queries = array(), $entry_id = '', $projection = 'full', $visibility = 'private', $username = 'default')
    {
        $username = urlencode($username);
        $url = new Net_URL("http://www.google.com/calendar/feeds/$username/$visibility/$projection/$entry_id");
        if (!empty($queries)) {
            foreach ($queries as $key => $value) {
                $url->addQueryString($key, $value);
            }
        }
        $this->feedUrl = $url->getURL();
    }


    /**
     * This method requests a feed that contains
     * the information of all calendars belonged to the user.
     *
     * @param  string  $username
     * @return boolean
     * @access public
     */
    function requestFeedList($username = 'default')
    {
        $this->request->HTTP_Request("http://www.google.com/calendar/feeds/$username", $this->requestParams);
        $this->addAuthorizationHeader();
        $this->request->sendRequest();
        switch ($this->request->getResponseCode()) {
            case 200:
                return true;
                break;
            default:
                return false;
        }
    }
}

?>
