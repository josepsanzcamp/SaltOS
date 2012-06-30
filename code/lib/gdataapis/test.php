<?php
error_reporting(0);
/**
 * ---------------------------------------------------------------------
 * Google Calendar Feed Request Examples
 * ---------------------------------------------------------------------
 */

require_once 'Google/Calendar.php';
$service = new Google_Calendar;
$queries = array(
    'orderby' => 'starttime',
    'start-min' => date('Y-m-d\TH:i:s+09:00', time())
);

/**
 * ---------------------------------------------------------------------
 * AuthSub Example
 * ---------------------------------------------------------------------
 */
if (isset($_GET['token']) and strlen($_GET['token']) > 0) {

    // Exchange single-use token to a session token.
    if (!$service->requestAuthSubSessionToken($_GET['token'])) {
        exit("AuthSubSessionToken has failed.\n".$service->getResponseBody());
    }

    // Request a feed.
    if (!$service->requestFeed($queries)) {
        exit("Requesting a feed has failed.\n".$service->getResponseBody());
    }
    $feed = $service->getResponseBody();

    // Revoke the token.
    if (!$service->requestAuthSubRevokeToken($service->token)) {
        exit("AuthSubRevokeToken has failed.\n".$service->getResponseBody());
    }

    // Display the XML feed as it is.
    header('Content-Type:application/xml;charset=UTF-8');
    exit($feed);
}

/**
 * ---------------------------------------------------------------------
 * ClientLogin Example
 * ---------------------------------------------------------------------
 */
if (isset($_POST['email']) and strlen($_POST['email']) > 0) {

    // Request ClientLogin.
    if (!$service->requestClientLogin($_POST['email'], $_POST['password'])) {
        exit("ClientLogin has failed.\n".$service->getResponseBody());
    }

    // Request a feed.
    if (!$service->requestFeed($queries)) {
        exit("Requesting a feed has failed.\n".$service->getResponseBody());
    }

    // Parse the feed into hashed structure.
    require_once 'XMLParseIntoStruct.php';
    $parser = new XMLParseIntoStruct($service->getResponseBody());
    $parser->parse();

    // Display the parsed hashed structure.
    header('Content-Type:text/plain;charset=UTF-8');
    print_r($parser->getResult());
    exit;
}

header('Content-Type:text/html;charset=UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <title>Google Calendar Feed Request Examples</title>
    </head>
    <body>
        <h1>Google Calendar Feed Request Examples</h1>
        <h2>A. Request with AuthSub</h2>
        <p>Please click the link below to proceed to the Google Account website,<br>
        and grant <em>this website</em> to access to your Google Calendar.</p>
        <p><a href="<?php
            $url = new Net_URL($_SERVER['SCRIPT_NAME']);
            echo $service->getAuthSubRequestUrl($url->getURL());
        ?>">Access Request at Google Account</a></p>
        <p><em>This website will <strong>NOT</strong> have access to your password or any personal information.</em></p>
        <hr>
        <h2>B. Request with ClientLogin</h2>
        <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
            <fieldset>
                <legend>Submit your Google Accout email address and the password.</legend>
                <p>Email <input type="text" name="email" value="" tabindex="1">
                Password <input type="password" name="password" value="" tabindex="2">
                <input type="submit" tabindex="3"></p>
            </fieldset>
        </form>
        <p><em>Your email address and password are being sent to <strong>this website</strong>.</em></p>
    </body>
</html>
