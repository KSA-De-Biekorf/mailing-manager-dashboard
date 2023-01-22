// only use whole numbers!
const DB_VERSION = 4;

// Database can be removed with: indexedDB.deleteDatabase('auth').onsuccess=(function(e){console.log("Delete OK");})
function db__init() {
	var db = new Dexie("auth");
	db.version(DB_VERSION).stores({
    keys: `
			id,
      pubkey,
      privkey,
      token`,
	});
	return db;
}

function db__set_auth(db, privkey, pubkey, token, userID, errHandle) {
  if ("indexedDB" in window) {
    console.debug("IndexedDB supported");
  } else {
    console.error("IndexedDB is not supported in this browser");
    errHandle("Deze browser wordt niet ondersteund");
  }
  
  db.keys.put({
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

function db__clear(db, errHandle) {
  db.keys.clear()
    .then(() => {
      console.info("auth database cleared");
    }).catch((err) => {
      errHandle(err);
    });
}
