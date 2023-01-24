<?php

require_once("../../mailing-manager/rsa.php");
require_once("../../mailing-manager/auth.php");
require_once("../../mailing-manager/PersonDBLib/connect.php");
require_once("../../mailing-manager/url_safe.php");

$auth = $GLOBALS["AUTH"];
$session_keypair = $GLOBALS["SESSION_KEYPAIR"];

http_response_code(500); // if anything unexpected happens

# User and pass
$authBase64 = $_SERVER["HTTP_AUTH"];
$decrypted = $session_keypair->decrypt($authBase64);
// user details of type object. Has `user` and `pass` fields.
$userd = json_decode($decrypted, false);

# Client public key
$client_pubkey = url_safe_to_base64($_SERVER["HTTP_PUBKEY"]); # base64 encoded

# Connect to database
$conn = new_connection();

if ($auth->authenticate($userd->user, $userd->pass)) {
  http_response_code(200);
  $data = $auth->new_token($conn, $userd, $client_pubkey);
  $token = $data->token; // is base64 encoded
  header("auth-token: $token");
  $userID = $data->userID;
  header("user-id: $userID");
} else {
  $code = 500;
  $reason = "";
  if ($userd->user == "" || $userd->user == null) {
    $code = 400;
    $reason = "Gebruikersnaam niet ingevuld";
  } else if ($userd->pass == "" || $userd->pass == null) {
    $code = 400;
    $reason = "Wachtwoord niet ingevuld";
  } else {
    $code = 401;
    $reason = "Verkeerde gebruikersnaam of wachtwoord";
  }

  http_response_code($code);
  header("reason: $reason");
}

?>
