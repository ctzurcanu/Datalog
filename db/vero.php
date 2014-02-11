<?php


require_once __DIR__.'/vendor/Google/Google_Client.php';

/**
 * Replace this with the client ID you got from the Google APIs console.
 */
const CLIENT_ID = '';

/**
 * Replace this with the client secret you got from the Google APIs console.
 */
const CLIENT_SECRET = '';

/**
  * Optionally replace this with your application's name.
  */
const APPLICATION_NAME = "Google+ PHP Token Verification";

$client = new Google_Client();
$client->setApplicationName(APPLICATION_NAME);
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);

//$id_token = $_POST["id_token"];
$access_token = $_POST["access_token"];

$token_status = Array();

$id_status = Array();
if (!empty($id_token)) {
  // Check that the ID Token is valid.
  try {
    // Client library can verify the ID token.
    $jwt = $client->verifyIdToken($id_token, CLIENT_ID)->getAttributes();
    $gplus_id = $jwt["payload"]["sub"];
    $id_status["valid"] = true;
    $id_status["gplus_id"] = $gplus_id;
    $id_status["message"] = "ID Token is valid.";
    $id_status["email"] = $jwt["payload"]["email"];
  } catch (Google_AuthException $e) {
    $id_status["valid"] = false;
    $id_status["gplus_id"] = NULL;
    $id_status["message"] = "Invalid ID Token.";
  }
  $token_status["id_token_status"] = $id_status;
}

$access_status = Array();
if (!empty($access_token)) {
  $access_status["valid"] = false;
  $access_status["gplus_id"] = NULL;
  // Check that the Access Token is valid.
  $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' .
          $access_token;
  $req = new Google_HttpRequest($reqUrl);

  $tokenInfo = json_decode(
      $client::getIo()->authenticatedRequest($req)
          ->getResponseBody());

  if (property_exists($tokenInfo, "error") ) {
    // This is not a valid token.
    $access_status["message"] = "Invalid Access Token.";
  } else if ($tokenInfo->audience != CLIENT_ID) {
    // This is not meant for this app. It is VERY important to check
    // the client ID in order to prevent man-in-the-middle attacks.
    $access_status["message"] = "Access Token not meant for this app.";
  } else {
    $access_status["valid"] = true;
    $access_status["gplus_id"] = $tokenInfo->user_id;
    $access_status["email"] = $tokenInfo->email;
    $access_status["message"] = "Access Token is valid.";
  }
  $token_status["access_token_status"] = $access_status;
}

print json_encode($token_status);


?>