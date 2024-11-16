// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.2/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.0.2/firebase-analytics.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyCxHSeEH7p8O1ZqcLpwnvX6RA3YJuU94jY",
  authDomain: "fbla-database-requests.firebaseapp.com",
  projectId: "fbla-database-requests",
  storageBucket: "fbla-database-requests.firebasestorage.app",
  messagingSenderId: "1059383425555",
  appId: "1:1059383425555:web:15a6f529e43f079103dde6",
  measurementId: "G-8V48CK5W4J"
};

import { getDatabase, ref, set, get, child, update, remove } from "https://www.gstatic.com/firebasejs/11.0.2/firebase-database.js"

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export {app, getDatabase, ref, set, get, child, update, remove}