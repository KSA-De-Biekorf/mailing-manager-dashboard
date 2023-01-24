<?php

$signed_token = $_SERVER["HTTP_TOKEN"];


header("t: $signed_token");

?>
