<!DOCTYPE html>
<html>
<head>
    <title>login</title>
</head>
<body>
  <form id="login-form">
      <label for="user">Username:</label><br>
      <input type="text" id="user" name="user"><br>
      <label for="pass">Password:</label><br>
      <input type="password" id="pass" name="pass"><br>
      <input type="submit" value="inloggen">
  </form>
  <p id="errors"></p>

  <script src="../libs/jsencrypt.min.js"></script>
  <script src="../libs/auth/localDB.js"></script>
  <!-- handle form -->
  <script type="text/javascript">
    // Server public key
    const base64PublicKey = "<?php require_once("../../mailing-manager/rsa.php"); echo base64_encode($session_keypair->public_key_pem); ?>";
    const publicKey = atob(base64PublicKey);

    // init encryption
    let encrypt_server = new JSEncrypt({log: true});
    encrypt_server.setPublicKey(publicKey);
    let encrypt_client = new JSEncrypt({log: true});
    // client public key, base64 encoded
    const client_key = encrypt_client.getKey(); // UNOPTIMIZED: getKey blocking version
    const client_pubkey = client_key.getPublicKey().replaceAll("\n", ""); 
    const client_privkey = client_key.getPrivateKey().replaceAll("\n", "");

    class ErrorHandler {
      constructor() {
        this.err_elem = document.querySelector("#errors");
      }

      addError(err) {
        this.err_elem.innerHTML += `${err}<br/>`;
      }

      clear() {
        this.err_elem.innerHTML = ""
      }
    }

    let errHandler = new ErrorHandler();

    // login
    const form = document.querySelector("#login-form");

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      errHandler.clear();

      const user = form.elements['user'].value;
      const pass = form.elements['pass'].value;

      const user_details = JSON.stringify({
        user: user,
        pass: pass,
      });

      let encryptedBase64 = encrypt_server.encrypt(user_details);
      // console.log(encryptedBase64);

      // Send login details
      fetch("/cgi-bin/login-callback.php", {
        method: "POST",
        headers: {
          "auth": encryptedBase64,
          "pubkey": client_pubkey,
        },
      }).then(resp => {
        console.log(resp);
        
        const status = resp.status;
        const statusText = resp.statusText;
        if (status != 200) {
          console.error(statusText);
          console.error(resp.headers.get("reason"));
          errHandler.addError(resp.headers.get("reason"));
          return;
        }

        const token = resp.headers.get("auth-token");
        console.log("token", token);
        const userID = resp.headers.get("user-id");
        console.log("userID", userID);
        if (userID == "" || userID == null) {
          errHandler.addError("(server error) invalid user id.<br/>Contacteer de server admin");
          return;
        }

        db__set_auth(cient_privkey, client_pubkey, token, userID, errHandler.addError);
      });
    });

  </script>
</body>
</html>
