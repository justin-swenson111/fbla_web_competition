  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyB0d-Rk22Rixcq-U1gqGUuAWWxnBYbE8_8",
    authDomain: "fbla-database-abd1e.firebaseapp.com",
    databaseURL: "https://fbla-database-abd1e-default-rtdb.firebaseio.com",
    projectId: "fbla-database-abd1e",
    storageBucket: "fbla-database-abd1e.appspot.com",
    messagingSenderId: "38832358961",
    appId: "1:38832358961:web:2e795bb42cd661f9f03710",
    measurementId: "G-T3Y9S7B550"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);

  import {getDatabase, ref, get, set, child, update, remove}
  from "https://fbla-database-abd1e-default-rtdb.firebase-database.js"

  const db = getDatabase()

  var getStudentID = document.querySelector("#studentEmail")
  var getStudentPass = document.querySelector("#studentPassword")
  var getEmployerEmail = document.querySelector("#employerEmail")
  var getEmployerPass = document.querySelector("#employerPassword")
