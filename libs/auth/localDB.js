// only use whole numbers!
const DB_VERSION = 1;

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

    const objStore = db.createObjectStore("auth", { keypath: "auth"});
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
  }
}
