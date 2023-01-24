<html>
<head>
	<title>Mailinglijsten Dashboard</title>
</head>
<body>
	<p id="info"></p>

	<?php
		require_once("../../mailing-manager/PersonDBLib/auth/queries.php");
		require_once("../../mailing-manager/PersonDBLib/connect.php");
		require_once("../../mailing-manager/url_safe.php");

		$conn = new_connection();

		$is_authenticated = false;
		
		if (isset($_GET["token"]) && isset($_GET["signature"]) && isset($_GET["userid"])) {
			# URL parameters
			$token = url_safe_to_base64($_GET["token"]);
			$signature64 = url_safe_to_base64($_GET["signature"]);
			$signature = base64_decode($signature64);
			$userID = $_GET["userid"];
			
			# retrieve token entry from DB
			$token_entries = query_token($conn, $userID);
			if (!$token_entries) {
				http_response_code(401);
				# TODO: handle
				die("User is unauthorized, no valid entries found");
			}
			$token_entry = $token_entries->fetch_assoc();
			$token = $token_entry["token"];
			$public_key_pem = base64_decode($token_entry["public_key"]); # is base64 encoded in database
			$public_key = openssl_get_publickey($public_key_pem);
			if (!$public_key) {
				http_response_code(400);
				error_log(openssl_error_string());
				die("invalid public key");
			}

			#$array=openssl_pkey_get_details($public_key);
			
			$is_verified = openssl_verify($token, $signature, $public_key_pem, "sha256");
			if (!$is_verified) {
				http_response_code(401);
				die("User is unauthorized<br/><a href='https://email.ksadebiekorf.be/cgi-bin/login.php'>Opnieuw inloggen</a>");
			}

			$is_authenticated = true;

			echo "User logged in!";
		}
	?>

	<script src="../libs/jsencrypt.min.js"></script>
	<script src="https://unpkg.com/dexie/dist/dexie.js"></script>
	<script src="../libs/auth/localDB.js"></script>
	<script>
		const db = db__init();
		const info_elem = document.querySelector("#info");
	</script>
	
	<script src="../libs/http/url_params.js"></script>
	<div class="horizontal-menu-bar">
		<ul>
			<li><a id="db_viewer">db viewer</a></li>
			<li><a id="accout">account</a></li>
		</ul>
	</div>

	<script>
	const db_viewer_a = document.querySelector("#db_viewer");
	db_viewer_a.href = set_param(window.href, 'nav', 'db_viewer');
	const account_a = document.querySelector("#account");
	db_viewer_a.href = set_param(window.href, 'nav', 'account');
	</script>

	<script>
		const authenticated = <?php echo($is_authenticated); ?>
		
		// Handle requests without url parameters
		if (authenticated == 0) {
			// user is not authenticated
			info_elem.innerHTML += "authenticating...<br/>";	
			db__get_keys(db, (entry) => {
				const sign = new JSEncrypt();
	      sign.setPrivateKey(entry["privkey"]);
				// sign token and send to server
	      const signature = base64_to_url_safe(sign.sign(entry["token"], CryptoJS.SHA256, "sha256"));
				const safe_token = base64_to_url_safe(token);
				const id = entry["id"];
				window.location.href = `https://email.ksadebiekorf.be/cgi-bin/dashboard.php?token=${safe_token}&signature=${signature}&userid=${id}`;
			});
		}

	</script>	
</body>
</html>
