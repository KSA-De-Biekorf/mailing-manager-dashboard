// only use whole numbers!
const DB_VERSION = 3;

var db = new Dexie("auth")
db.version(DB_VERSION).stores({
  keys: `
    pubkey,
    privkey,
    token,
    id`,
});

function db__set_auth(privkey, pubkey, token, userID, errHandle) {
  if ("indexedDB" in window) {
    console.debug("IndexedDB supported");
  } else {
    console.error("IndexedDB is not supported in this browser");
    errHandle("Deze browser wordt niet ondersteund");
  }
  
  db.auth.put({
    id: userID,
    pubkey: pubkey,
    privkey: privkey,
    token: token
  }).then(() => {
    console.debug("Keys added to database");
  }).catch((err) => {
    errHandle(err);
  });
}

function db__clear(errHandle) {
  db.auth.clear()
    .then(() => {
      console.info("auth database cleared");
    }).catch((err) => {
      errHandle(err);
    });
}
