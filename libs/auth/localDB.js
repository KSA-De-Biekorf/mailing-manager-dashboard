// only use whole numbers!
<<<<<<< HEAD
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
=======
const DB_VERSION = 2;

function db__set_auth(privkey, pubkey, token, userID, errHandle) {
  const request = window.indexedDB.open("auth", DB_VERSION);
  request.onerror = (e) => {
    console.error(e);
    if (errHandle != null) errHandle(e.target.errorCode);
  }
  request.onsuccess = (e) => {
    const db = e.target.result;

    const authObjectStore = db
      .transaction("auth", "readwrite")
      .objectStore("auth");
    authObjectStore.put({auth: 1, pubkey: pubkey, privkey: privkey, token: token});
  }
  request.onupgradeneeded = (e) => {
    const db = e.target.result;

    const objStore = db.createObjectStore("auth", { keypath: "auth", autoIncrement: true });
    objStore.createIndex("pubkey", "pubkey", { unique: true });
    objStore.createIndex("privkey", "privkey", { unique: true });
    objStore.createIndex("token", "token", { unique: true });
    objStore.createIndex("id", "id", { unique: true });

    objStore.transaction.oncomplete = (_e) => {
      const authObjectStore = db
        .transaction("auth", "readwrite")
        .objectStore("auth");
      authObjectStore.add({auth: 1, pubkey: pubkey, privkey: privkey, token: token, id: userID});
    }
>>>>>>> master
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
