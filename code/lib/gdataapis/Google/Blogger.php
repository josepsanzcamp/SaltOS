<?php

/**
 * ---------------------------------------------------------------------
 * Blogger Data API specific extended class
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
 * Blogger Data API specific extended class
 *
 * @version    0.2 (alpha) released 2006/10/12
 * @author     ucb.rcdtokyo http://www.rcdtokyo.com/ucb/
 * @license    GNU LGPL v2.1+ http://www.gnu.org/licenses/lgpl.html
 * @see        http://code.google.com/apis/gdata/blogger.html
 *
 * Example - Request a feed with authentication
 * <code>
 * $username = 'example@gmail.com';
 * $password = 'password';
 * $blog_id = '12345678';
 *
 * $service = new Google_Blogger($blog_id);
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
class Google_Blogger extends Google_GData
{
    /**
     * The service name for the ClientLogin authentication.
     * This must be "blogger" for Blogger Data API.
     *
     * @var string
     * @access protected
     */
    var $serviceName = 'blogger';

    /**
     * The scope URL for the AuthSub authentication.
     * This must be "http://beta.blogger.com/feeds"
     * for Blogger Data API.
     * Note that the AuthSub is not available for Blogger's own account.
     * The AuthSub is available for the users using a Google account.
     *
     * @var string
     * @access protected
     */
    var $scopeUrl = 'http://beta.blogger.com/feeds';

    /**
     * Constructor.
     * The parameter is the ID of the target blog.
     * The value is used to set the default feed URL
     * used when requesting a feed or inserting an entry.
     * You may use setFeedUrl() to set/change it later.
     *
     * @access public
     */
    function __construct($blog_id = null)
    {
        if (!empty($blog_id)) {
        	$this->feedUrl = "http://www.blogger.com/feeds/$blog_id/posts/full";
        }
        $this->request = new HTTP_Request;
    }

    /**
     * Set/change the default feed URL
     * used when requesting a feed or inserting an entry.
     *
     * @param  string  $blog_id
     * @param  array   $queries
     * @param  string  $entry_id
     * @param  string  $feed_type (full|summary)
     * @return void
     * @access public
     */
    function setFeedUrl($blog_id, $queries = array(), $entry_id = '', $feed_type = 'full')
    {
        $url = new Net_URL("http://www.blogger.com/feeds/$blog_id/posts/$feed_type/$entry_id");
        if (!empty($queries)) {
            foreach ($queries as $key => $value) {
                $url->addQueryString($key, $value);
            }
        }
        $this->feedUrl = $url->getURL();
    }

    /**
     * Blogger users can have multiple blogs.
     * This method requests a feed that contains
     * the information of all blogs belonged to the user.
     *
     * @param  string  $username
     * @return boolean
     * @access public
     */
    function requestFeedList($username = 'default')
    {
        $this->request->HTTP_Request("http://www.blogger.com/feeds/$username/blogs", $this->requestParams);
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
