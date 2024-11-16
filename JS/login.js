 import {app, getDatabase, ref, set, get, child, update, remove}
 from './firebase.js'
 //importing the functions from a different js script where the db is imported

 const db = getDatabase()
 //easier way to reference the database
//waiting until after everything is loaded before getting variables
  document.addEventListener("DOMContentLoaded", () => {
    var getStudentID = document.getElementById("studentEmail")
    var getStudentPass = document.getElementById("studentPassword")
    var getEmployerEmail = document.getElementById("employerEmail")
    var getEmployerPass = document.getElementById("employerPassword")
    var submitButton = document.getElementById("StudentSubmit")
  
    getStudentID.onkeyup = function(){test()}
    submitButton.addEventListener('click',findUser)
    submitButton.onclick= function(){findUser}

  });

  var getStudentID = document.getElementById("studentEmail")
  var getStudentPass = document.getElementById("studentPassword")
  var checkID
  var checkPass
  var getEmployerEmail = document.getElementById("employerEmail")
  var getEmployerPass = document.getElementById("employerPassword")
  var submitButton = document.getElementById("StudentSubmit")

  submitButton.addEventListener('click',findUser)

  function findUser(){
    const dbref = ref(db);

    get(child(dbref, "Students/" + getStudentID.value))
    .then((snapshot)=>{
        if(snapshot.exists()){
            checkID = "ID: " + snapshot.val().ID;
            checkPass = "Password: " + snapshot.val().Password;
        } else {
            alert("No data found");
        }
    })
    .catch((error)=>{
        alert(error)
    })
    alert(checkID.value)
  }


  

