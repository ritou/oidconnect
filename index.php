<?php
include("lib/OpenIDConnect.php");

// configration
$client_id = "26633205769-0s9fgairnfs1j9qdsobdjr06lvo8o2bv.apps.googleusercontent.com";
$client_secret = "-----This is Client Secret-----";
$redirect_uri = "http://www8322u.sakura.ne.jp/oidconnect/";

$authz_endpoint = "https://accounts.google.com/o/oauth2/auth";
$token_endpoint = "https://accounts.google.com/o/oauth2/token";
$resource_endpoint = "https://www.googleapis.com/oauth2/v1/userinfo";
$scope = "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile";
$tokeninfo_endpoint = "https://accounts.google.com/o/oauth2/tokeninfo";

session_name("oidconnect");
session_start();

if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    $_SESSION = array();
    // session_destroy();
    header("Location : ./");
}

$authz_link = null;
$code = @$_GET["code"];
if (empty($code) && $_SESSION['state'] == 1)
    $_SESSION['state'] = 0;

$client = new OpenIDConnect_Code($client_id, $client_secret, $redirect_uri);
try {

    if (empty($code) && !$_SESSION['state'] || $_SESSION['state'] == 0) {

        $authz_link = $client->getRequestAuthUrl($authz_endpoint, $scope);
        $_SESSION['state'] = 1;
    } else {

        if ($_SESSION['state'] == 1) {
            $access_token_info = $client->getAccessToken($token_endpoint, $code);
            $token_req = $client->getLastRequestHeader();
            $token_res = $client->getLastResponse();
            $_SESSION['state'] = 2;
            $_SESSION['atoken'] = $access_token_info->access_token;
            $_SESSION['rtoken'] = $access_token_info->refresh_token;
        }

        if (isset($_GET['refresh']) && $_GET['refresh'] == 1) {
            // refresh Access Token
            $access_token_info = $client->refreshAccessToken($token_endpoint, $_SESSION['rtoken']);
            $token_req = $client->getLastRequestHeader();
            $token_res = $client->getLastResponse();
            if (isset($access_token_info->access_token)) {
                $_SESSION['atoken'] = $access_token_info->access_token;
            } else {
                $_SESSION = array();
                header("Location : ./");
            }
        }

        // Resource Access
        $method = "GET";
        $client->setToken($_SESSION['atoken']);
        $client->enableResponseHeader(); // for debug
        $client->sendRequest($method, $tokeninfo_endpoint);
        $tokeninfo_send_header = $client->getLastRequestHeader();
        $tokeninfo_res = $client->getLastResponse();

        $method = "GET";
//        $client->setToken($_SESSION['atoken']);
        $client->enableResponseHeader(); // for debug
        $client->sendRequest($method, $resource_endpoint);
        $send_header = $client->getLastRequestHeader();
        $res = $client->getLastResponse();
    }
} catch (OAuthException $E) {
    print_r($E);
}
?>
<html>
    <head xmlns:og="http://opengraphprotocol.org/schema/">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Cache-Control" content="no-cache">
        <meta charset="utf-8" />
        <meta property="og:title" content="OpenID Connect Sample RP" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://www8322u.sakura.ne.jp/oidconnect/" />
        <meta property="og:image" content="http://www8322u.sakura.ne.jp/oidconnect/images/logo.png" />
        <meta property="og:description" content="This is OpenID Connect Sample RP  using Google(OP)." />
        <title>OpenID Connect Sample RP</title>
        <link rel="shortcut icon" href="./images/favicon.ico">
    </head>
    <body>
        <h1>OpenID Connect Sample RP Authorization Code Flow</h1>
        <pre>
This is OpenID Connect Sample RP  using Google(OP).
Flow : Authorization Code Flow (Implicit Flow is <a href="http://www8322u.sakura.ne.jp/oidconnect/implicit.html">here</a>)

Google's sample RP and Document : <a href="http://oauthssodemo.appspot.com/step/1" target="_blank">http://oauthssodemo.appspot.com/step/1</a>
        </pre>

        <div id="link">
            <?php if (!empty($authz_link)) { ?>
                <a href="<?php echo $authz_link; ?>">START DEMO</a>
            <?php } else { ?>
                <a href="./">RELOAD</a>
                <a href="./?refresh=1">Refresh Access Token</a>
                <a href="./?clear=1">RESTART</a>
            </div>
            <div id="assertion">
            <?php if (isset($_GET['refresh']) && $_GET['refresh'] == 1) {
 ?>
                    <h2>Refresh Access Token</h2>
<?php } else { ?>
                    <h2>Obtain Access Token</h2>
<?php } ?>
                <h3>Request : </h3>
                <pre><?php echo htmlspecialchars(@$token_req); ?></pre>
                <h3>Response : </h3>
                <pre><?php echo htmlspecialchars(@$token_res); ?></pre>
            </div>
            <div id="tokeninfo">
                <h2>Token Info Endpoint</h2>
                <h3>Request : </h3>
                <pre><?php echo htmlspecialchars(@$tokeninfo_send_header); ?></pre>
                <h3>Response : </h3>
                <pre><?php echo htmlspecialchars(@$tokeninfo_res); ?></pre>
            </div>
            <div id="info">
                <h2>Resource Access</h2>
                <h3>Request : </h3>
                <pre><?php echo htmlspecialchars(@$send_header); ?></pre>
                <h3>Response : </h3>
                <pre><?php echo htmlspecialchars(@$res); ?></pre>
            </div>
<?php } ?>
        <div>&nbsp;</div>
        <div id="fb">
            <iframe src="http://www.facebook.com/plugins/like.php?app_id=225511477484508&amp;href=http%3A%2F%2Fwww8322u.sakura.ne.jp%2Foidconnect%2F&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>
            <div id="fb-root"></div>
            <script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
            <fb:comments href="http://www8322u.sakura.ne.jp/oidconnect/" num_posts="5" width="500"></fb:comments>
        </div>
        <hr>
    <footer>
        <small class="copytight">&copy; 2011 <a href="http://twitter.com/#!/ritou">@ritou</a></small>
    </footer>
</body>
</html>
