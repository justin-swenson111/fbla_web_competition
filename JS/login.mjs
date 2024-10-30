  // Import the functions you need from the SDKs you need
  // import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
  // import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  import { getFirestore, doc, getDoc, getDocs, collection } from "https://www.gstatic.com/firebasejs/9.4.0/firebase-firestore.js";
  import { initializeApp } from "https://www.gstatic.com/firebasejs/9.4.0/firebase-app.js";

  // TODO: Replace the following with your app's Firebase project configuration
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
  
  const app = initializeApp(firebaseConfig)

  import {getDatabase, ref, get, set, child, update, remove}
  from "https://www.gstatic.com/firebasejs/10.14.1/firebase-database.js"

  const db = getDatabase()

  var getStudentID = document.getElementById("studentEmail")
  var getStudentPass = document.getElementById("studentPassword")
  var getEmployerEmail = document.getElementById("employerEmail")
  var getEmployerPass = document.getElementById("employerPassword")
  var submitButton = document.getElementById("StudentSubmit")

  getStudentID.onkeyup = function(){test()}
  submitButton.addEventListener('click',findUser)
  submitButton.onclick= function(){findUser()}
  function test(){
    alert(getStudentID.value)
    // alert(getStudentPass)
    // alert(getEmployerEmail)
    // alert(getEmployerPass)
  }
  function findUser(){
    alert("hi")
  }
