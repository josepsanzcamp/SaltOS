<?php

/**
 * ---------------------------------------------------------------------
 * Your Cuisine Recipe at Google Base
 * A sample program to access the Google Base data API using PHP.
 * ---------------------------------------------------------------------
 * This is a modified version of Google's own PHP demo for Google Base
 * Data API http://code.google.com/apis/base/samples/php/php-sample.html
 * with the Google_GData class.
 *
 * Other changes from the original:
 * - store the token and the parsed feed in PHP session.
 * - add exit function to revoke current token and PHP session.
 * - separate logic and design, and capsulate elemental functions.
 * ---------------------------------------------------------------------
 */

// To run this program on your web server, you must sign up for an API
// key at http://code.google.com/apis/base/signup.html and register the
// key as the value of the following constant.

define('GOOGLE_BASE_DEVELOPER_KEY', '');

require_once 'Google/GData.php';

/**
 * ---------------------------------------------------------------------
 * XMLParser class
 * A SAX based parser used in the Recipe class.
 * ---------------------------------------------------------------------
 */

class XMLParser
{
    /**
     * @var string
     * @access protected
     */
    var $current = '';

    /**
     * @var boolean
     * @access protected
     */
    var $found = false;

    /**
     * @var array
     * @access protected
     */
    var $result = array();

    /**
     * @access public
     */
    function XMLParser()
    {
        $this->__construct();
    }

    /**
     * @access public
     */
    function __construct()
    {
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'characterData');
    }

    /**
     * @param  string  $xml_data
     * @access public
     */
    function parse($xml_data)
    {
        xml_parse($this->parser, $xml_data);
        xml_parser_free($this->parser);
        return $this->result;
    }

    /**
     * @param  object  $parser
     * @param  string  $name
     * @param  array   $attribs
     * @access protected
     */
    function startElement($parser, $name, $attribs) {
        $this->current = $name;
        if ($this->current == 'ENTRY') {
            $this->found = true;
            $this->result[count($this->result)] = array();
        } elseif ($this->found and $this->current == 'LINK') {
            $this->result[count($this->result) - 1][$attribs['REL']] = $attribs['HREF'];
        }
    }

    /**
     * @param  object  $parser
     * @param  string  $name
     * @access protected
     */
    function endElement($parser, $name) {
        if ($name == 'ENTRY') {
            $this->found = false;
        }
    }

    /**
     * @param  object  $parser
     * @param  string  $data
     * @access protected
     */
    function characterData($parser, $data) {
        if ($this->found) {
            $this->result[count($this->result) - 1][strtolower($this->current)] = $data;
        }
    }
}

/**
 * ---------------------------------------------------------------------
 * The Recipe class
 * ---------------------------------------------------------------------
 */

class Recipe
{
    /**
     * @var object  XMLParser class object
     * @access protected
     */
    var $parser;

    /**
     * @var object  Google_GData class object
     * @access protected
     */
    var $service;

    /**
     * @access public
     */
    function Recipe()
    {
        $this->__construct();
    }

    /**
     * @access public
     */
    function __construct()
    {
        $this->parser = new XMLParser;
        $this->service = new Google_GData(null, 'http://www.google.com/base/feeds/items');
        $this->service->setFeedUrl('http://www.google.com/base/feeds/items');
        if (isset($_SESSION['token'])) {
            $this->service->setToken($_SESSION['token'], 'authsub');
        }
        if (defined('GOOGLE_BASE_DEVELOPER_KEY')) {
            $this->service->setAdditionalHeader('X-Google-Key', 'key='.GOOGLE_BASE_DEVELOPER_KEY);
        }
    }

    /**
     * @access public
     */
    function auth()
    {
        $single_use_token = $_GET['token'];
        if (!$this->service->requestAuthSubSessionToken($single_use_token)) {
            return $this->service->getResponseBody();
        }
        $_SESSION['token'] = $this->service->token;
        $this->service->setToken($_SESSION['token'], 'authsub');
        return "<p>Here's your <strong>single use token:</strong>
            <code>$single_use_token</code><br>
            And here's the <strong>session token:</strong>
            <code>$_SESSION[token]</code></p>";
    }

    /**
     * @access public
     */
    function logout()
    {
        if (isset($_SESSION['token'])) {
            $this->service->requestAuthSubRevokeToken($_SESSION['token']);
        }
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() -42000, '/');
        }
        @session_destroy();
    }

    /**
     * @access public
     */
    function requestFeed()
    {
        if (!$this->service->requestFeed()) {
            return $this->service->getResponseBody();
        }
        $_SESSION['entries'] = $this->parser->parse($this->service->getResponseBody());
        return true;
    }

    /**
     * @access public
     */
    function insert() {
        if (!$this->service->insert($this->buildXML())) {
            return $this->service->getResponseBody();
        }
        $this->requestFeed();
        return true;
    }

    /**
     * @access public
     */
    function update()
    {
        if (!$this->service->update($this->buildXML(), $_POST['link'])) {
            return $this->service->getResponseBody();
        }
        $this->requestFeed();
        return true;
    }

    /**
     * @access public
     */
    function delete()
    {
        if (!$this->service->delete($_POST['link'])) {
            return $this->service->getResponseBody();
        }
        $this->requestFeed();
        return true;
    }

    /**
     * @access public
     */
    function batchDelete() {
        $this->service->batch($this->buildBatchXML('delete'), $this->service->feedUrl.'/batch');
        $this->requestFeed();
        return true;
    }

    /**
     * @access protected
     */
    function buildXML()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<entry
    xmlns="http://www.w3.org/2005/Atom"
    xmlns:g="http://base.google.com/ns/1.0">
    <category
        scheme="http://base.google.com/categories/itemtypes"
        term="Recipes" />
    <title type="text">$_POST[recipe_title]</title>
    <g:cuisine>$_POST[cuisine]</g:cuisine>
    <g:item_type type="text">Recipes</g:item_type>
    <g:cooking_time type="intUnit">$_POST[time_val] $_POST[time_units]</g:cooking_time>
    <g:main_ingredient type="text">$_POST[main_ingredient]</g:main_ingredient>
    <g:serving_count type="number">$_POST[serves]</g:serving_count>
    <content>$_POST[recipe_text]</content>
</entry>
XML;
    }

    /**
     * @param  string  $operation
     * @access protected
     */
    function buildBatchXML($operation) {
        $result = '<?xml version="1.0" encoding="UTF-8"?><feed
            xmlns="http://www.w3.org/2005/Atom"
            xmlns:g="http://base.google.com/ns/1.0"
            xmlns:batch="http://schemas.google.com/gdata/batch">';
        $i = 0;
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 5) == "link_") {
                $result .= "<entry><id>$value</id>
                    <batch:operation type=\"$operation\" />
                    <batch:id>$i</batch:id></entry>";
                $i++;
            }
        }
        $result .= '</feed>';
        return $result;
    }
}

/**
 * ---------------------------------------------------------------------
 * Main logic
 * ---------------------------------------------------------------------
 */

$cuisines = array('African', 'American', 'Asian', 'Caribbean', 'Chinese',
    'French', 'Greek', 'Indian', 'Italian', 'Japanese', 'Jewish', 
    'Mediterranean', 'Mexican', 'Middle Eastern', 'Moroccan', 
    'North American', 'Spanish', 'Thai', 'Vietnamese', 'Other');

session_start();
$recipe = new Recipe;
$content = 'default';
$message = '';
if (!isset($_SESSION['token']) and isset($_GET['token']) and strlen($_GET['token']) > 0) {
    $message = $recipe->auth();
}
if (!isset($_SESSION['entries'])) {
    if (!isset($_SESSION['token'])) {
        $content = 'intro';
    } else {
        $result = $recipe->requestFeed();
    }
}
if (isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'logout':
            $content = 'intro';
            $recipe->logout();
            break;
        case 'refresh':
            $result = $recipe->requestFeed();
            break;
    }
} elseif (isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'edit':
            $content = 'edit';
            break;
        case 'insert':
            $result = $recipe->insert();
            $message = '<p>Recipe inserted!</p>';
            break;
        case 'update':
            $result = $recipe->update();
            $message = '<p>Item successfully updated.</p>';
            break;
        case 'delete':
            $result = $recipe->delete();
            $message = '<p>Item deleted.</p>';
            break;
        case 'delete_all':
            $result = $recipe->batchDelete();
            $message = '<p>All items deleted.</p>';
            break;
    }
}
if (isset($result) and true !== $result) {
    $message = $result;
    $content = 'intro';
    $recipe->logout();
}

/**
 * ---------------------------------------------------------------------
 * HTML output
 * ---------------------------------------------------------------------
 */

header('Content-Type:text/html;charset=UTF-8');
echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta http-equiv="Content-Style-Type" content="text/css">
        <meta name="robots" content="noindex,nofollow,noarchive">
        <title>Google Base API Demo</title>
        <style type="text/css">
            <!--
            body {
                font-family:Verdana, Sans-serif;
                font-size:small;
                margin:0;
            }
            #wrapper {
                margin:2em;
            }
            #banner {
            }
            #content {
            }
            #left {
                float:left;
                margin-right:1em;
            }
            #right {
            }
            #footer {
                clear:both;
                padding-top:1em;
            }
            a {
                color:#00f;
                text-decoration:none;
            }
            
            a:hover {
                color:#f00;
                text-decoration:underline;
            }
            form {
                margin:0;
                padding:0;
            }
            input, select, textarea {
                font-family:Verdana, Sans-serif;
            }
            table {
                border-collapse:collapse;
            }
            caption {
                font-weight:bold;
                padding:1em;
            }
            th {
                text-align:center;
                background-color:#eee;
            }
            th, td {
                vertical-align:top;
                padding:.5em;
                border:1px solid #000;
            }
            th.left {
                text-align:left;
            }
            td.center {
                text-align:center;
            }
            th.right, td.right {
                text-align:right;
            }
            -->
        </style>
    </head>
    <body>
        <div id="wrapper">
            <div id="banner">
                <h1>Your Cuisine Recipe at <a href="http://www.google.com/base">Google Base</a></h1>
                $message
            </div>
HTML;
switch ($content) {

/**
 * ---------------------------------------------------------------------
 * HTML output: INTRO
 * ---------------------------------------------------------------------
 */

    case 'intro':
        if (!defined('GOOGLE_BASE_DEVELOPER_KEY') or strlen(GOOGLE_BASE_DEVELOPER_KEY) == 0) {
            echo <<<HTML
            <p>To run this program on your web server,
            you must <a href="http://code.google.com/apis/base/signup.html">sign up for API key</a>
            and register the key as the value of the constant
            <strong>&ldquo;GOOGLE_BASE_DEVELOPER_KEY&rdquo;</strong> in this script file.</p>
HTML;
        } else {
            $url = new Net_URL($_SERVER['SCRIPT_NAME']);
            $url = $recipe->service->getAuthSubRequestUrl($url->getURL());
            echo <<<HTML
            <p>Please click the link below to proceed to the Google Accounts website,<br>
            and grant <em>this website</em> to access to your Google Base.</p>
            <h2><a href="$url">Access Request at Google Accounts website</a></h2>
            <p><em>This website will not have access to your password or any personal information.</em></p>
HTML;
        }
        break;

/**
 * ---------------------------------------------------------------------
 * HTML output: EDIT
 * ---------------------------------------------------------------------
 */

    case 'edit':
        $tags = '';
        foreach ($cuisines as $cuisine) {
            $tags .= "<option value=\"$cuisine\"".
                (($cuisine == $_POST['g:cuisine'])? ' selected': '').
                ">$cuisine</option>";
        }
        $splitCookingTime = split(' ', $_POST['g:cooking_time']);
        echo <<<HTML
            <form method="post" action="$_SERVER[SCRIPT_NAME]">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="link" value="$_POST[edit]">
                <table summary="Edit recipe:">
                    <caption>Edit recipe:</caption>
                    <tr>
                        <th class="right">Title:</th>
                        <td><input type="text" name="recipe_title" value="$_POST[title]"></td>
                    </tr>
                    <tr>
                        <th class="right">Main ingredient:</th>
                        <td><input type="text" name="main_ingredient" value="{$_POST['g:main_ingredient']}"></td>
                    </tr>
                    <tr>
                        <th class="right">Cuisine:</th>
                        <td><select name="cuisine">$tags</select></td>
                    </tr>
                    <tr>
                        <th class="right">Cooking Time:</th>
                        <td>
                            <input type="text" name="time_val" size="2" maxlength="2" value="$splitCookingTime[0]">
                            <select name="time_units">
HTML;
        if ($splitCookingTime[1] == 'minutes') {
            echo '<option value="minutes" selected>minutes</option><option value="hours">hours</option>';
        } else {
            echo '<option value="minutes">minutes</option><option value="hours" selected>hours</option>';
        }
        echo <<<HTML
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th class="right">Serves:</th>
                        <td><input type="text" name="serves" value="{$_POST['g:serving_count']}" size="2" maxlength="3"></td>
                    </tr>
                    <tr>
                        <th class="right">Recipe:</th>
                        <td><textarea name="recipe_text" rows="4" cols="20">$_POST[content]</textarea></td>
                    </tr>
                    <tr><td colspan="2" class="center"><input type="submit" value="Update"></td></tr>
                </table>
            </form>
            <p>Click here to go back to the <a href="$_SERVER[SCRIPT_NAME]">recipe list</a>.</p>
HTML;
        break;

/**
 * ---------------------------------------------------------------------
 * HTML output: MAIN
 * ---------------------------------------------------------------------
 */

    default:
        $entry_count = count($_SESSION['entries']);
        echo <<<HTML
            <div id="content">
                <div id="left">
                    <table summary="Recipes you have inserted">
                        <caption>Recipes you have inserted</caption>
HTML;
        if ($entry_count == 0) {
            echo '<tbody><tr><td colspan="5" class="center"><i>(none)</i></td></tr>';
        } else {
            echo <<<HTML
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Cuisine</th>
                                <th>Serves</th>
                                <th colspan="2">Actions</th>
                            </tr>
                        </thead>
HTML;
            foreach ($_SESSION['entries'] as $entry) {
                $tags = '';
                foreach ($entry as $key => $value) {
                    $tags .= "<input type=\"hidden\" name=\"$key\" value=\"$value\">";
                }
                echo <<<HTML
                        <tbody>
                            <tr>
                                <td><strong><a href="$entry[alternate]">$entry[title]</a></strong></td>
                                <td>{$entry['g:cuisine']}</td>
                                <td class="right">{$entry['g:serving_count']}</td>
                                <td>
                                    <form method="post" action="$_SERVER[SCRIPT_NAME]">
                                        <input type="hidden" name="action" value="edit">
                                        $tags
                                        <input type="submit" value="Edit">
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="$_SERVER[SCRIPT_NAME]">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="link" value="$entry[id]">
                                        <input type="submit" value="Delete">
                                    </form>
                                </td>
                            </tr>
HTML;
            }
        }
        $tags = '';
        for ($i = 0; $i < $entry_count; $i++) {
            $tags .= "<input type=\"hidden\" name=\"link_$i\" value=\"{$_SESSION['entries'][$i]['id']}\">";
        }
        $attrib = ($entry_count == 0)? ' disabled': '';
        echo <<<HTML
                            <tr>
                                <td colspan="5" class="center">
                                    <form method="post" action="$_SERVER[SCRIPT_NAME]">
                                        <input type="hidden" name="action" value="delete_all">
                                        $tags
                                        <input type="submit" value="Delete All"$attrib>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
HTML;
        $tags = '';
        foreach ($cuisines as $cuisine) {
            $tags .= "<option value=\"$cuisine\">$cuisine</option>";
        }
        echo <<<HTML
                <div id="right">
                    <form method="post" action="$_SERVER[SCRIPT_NAME]">
                        <input type="hidden" name="action" value="insert">
                        <input type="hidden" name="token" value="$_SESSION[token]">
                        <table summary="Insert a new recipe">
                            <caption>Insert a new recipe</caption>
                            <tbody>
                                <tr>
                                    <th class="right">Title:</th>
                                    <td><input type="text" name="recipe_title" value=""></td>
                                </tr>
                                <tr>
                                    <th class="right">Main ingredient:</th>
                                    <td><input type="text" name="main_ingredient" value=""></td>
                                </tr>
                                <tr>
                                    <th class="right">Cuisine:</th>
                                    <td>
                                        <select name="cuisine">
                                            $tags
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="right">Cooking Time:</th>
                                    <td>
                                        <input type="text" name="time_val" size="2" maxlength="2" value="">
                                        <select name="time_units">
                                            <option value="minutes">minutes</option>
                                            <option value="hours">hours</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="right">Serves:</th>
                                    <td><input type="text" name="serves" size="2" maxlength="3" value=""></td>
                                </tr>
                                <tr>
                                    <th class="right">Recipe:</th>
                                    <td><textarea name="recipe_text" rows="4" cols="20"></textarea></td>
                                </tr>
                                <tr><td colspan="2" class="center"><input type="submit" value="Submit"></td></tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div id="footer">
                <p>Click here to <a href="$_SERVER[SCRIPT_NAME]?action=logout">exit this application</a>
                or go to <a href="https://www.google.com/accounts">Google Accounts</a>.</p>
            </div>
HTML;
}
echo <<<HTML
        </div>
    </body>
</html>
HTML;

?>
