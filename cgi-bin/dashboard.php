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
		require_once("../../mailing-manager/auth.php");

		$conn = new_connection();
		$auth = $GLOBALS["AUTH"];

		$is_authenticated = false;
		
		if (isset($_GET["token"]) && isset($_GET["signature"]) && isset($_GET["userid"])) {
			# URL parameters
			$token = url_safe_to_base64($_GET["token"]);
			$signature64 = url_safe_to_base64($_GET["signature"]);
			$signature = base64_decode($signature64);
			$userID = $_GET["userid"];

			try {
				$is_authenticated = $auth->verify_request($conn, $token, $signature, $userID);
			} catch (Exception $e) {
				http_response_code($e->getCode());
				die($e->getMessage());
			}
			
			# retrieve token entry from DB
			// $token_entries = query_token($conn, $userID);
			// if (!$token_entries) {
			// 	http_response_code(401);
			// 	# TODO: handle
			// 	die("User is unauthorized, no valid entries found");
			// }
			// $token_entry = $token_entries->fetch_assoc();
			// $token = $token_entry["token"];
			// $public_key_pem = base64_decode($token_entry["public_key"]); # is base64 encoded in database
			// $public_key = openssl_get_publickey($public_key_pem);
			// if (!$public_key) {
			// 	http_response_code(400);
			// 	error_log(openssl_error_string());
			// 	die("invalid public key");
			// }

			// #$array=openssl_pkey_get_details($public_key);
			
			// $is_verified = openssl_verify($token, $signature, $public_key_pem, "sha256");
			// if (!$is_verified) {
			// 	http_response_code(401);
			// 	die("User is unauthorized<br/><a href='https://email.ksadebiekorf.be/cgi-bin/login.php'>Opnieuw inloggen</a>");
			// }

			// $is_authenticated = true;

			// echo "User logged in!";
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
			<?php
				$nav_items = ["db_viewer", "account"];
				foreach ($nav_items as $item) {
					echo "<li>";
					echo "<a id='nav-$item'>$item</a>";
					echo "</li>";
				}
			?>
		</ul>
	</div>

	<script>
		<?php
		foreach ($nav_items as $item) {
			echo "const nav_$item = document.querySelector('#nav-$item');";
			echo "nav_$item.href = set_param(new URL(window.location.href), 'nav', '$item');";
		}
		?>
	</script>

	<?php
		$page = null;
		if (isset($_GET["nav"])) {
			$page = $_GET["nav"];
		} else {
			$page = "db_viewer";
		}
	?>

	<main id="<?php echo $page; ?>">
		<?php
			require_once("../../mailing-manager/pages/$page.php");
			print_page();
		?>
	</main>

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
