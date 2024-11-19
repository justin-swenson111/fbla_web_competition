import {app, getDatabase, ref, set, get, child, update, remove}
from "./RequestDB.js"

const db =getDatabase()


var empCheck = document.getElementById("emp")
var stuCheck = document.getElementById("stu")
var Email = document.getElementById("empEmail")
var idNUm = document.getElementById("idNum")
var Pass = document.getElementById("Pass")

var AccType=""
var passlength =""
var idlength =""
var IdDoesntexist

var create = document.getElementById("create")

create.addEventListener('click', createUser)

// create.onclick=function(){createUser()}

function createUser(){
  passlength=Pass.value
  idlength=idNUm.value
  alert(idlength.length)
  alert(passlength.length)
      if((stuCheck.checked || empCheck.checked) && Email.value!=""){
        if(idlength.length==5){
          if(passlength.length>=8){
            IDexists()
          }
          else{
            alert("Password must be at least 8 characters")

          }
          }
        else{
          alert("ID must be 5 characters")
        }
      }
      else{
        alert("please fill out all fields")
      }
  }
  function IDexists(){
    const dbref = ref(db);

    let IDcheck =get(child(dbref,"Requests/"+ idNUm.value))
    .then((snapshot)=>{
      snapshot.val().ID
    })
    alert(string(IDcheck)+"hi")

  }
  function addUser(){
    if(stuCheck.checked){
      AccType="Student"
      set(ref(db, "Requests/"+ idNUm.value),{
        ID: idNUm.value,
        Password: Pass.value,
        Type: AccType,
        Email: Email.value
    })
      .then(()=>{
        alert("Data added successfully");
    })
    .catch((error)=>{
        alert(error);
    });
    }

    else{

      AccType="Employer"
      set(ref(db, "Requests/"+ idNUm.value),{
        ID: idNUm.value,
        Password: Pass.value,
        Type: AccType,
        Email: Email.value
    })
      .then(()=>{
        alert("Data added successfully");
    })
    .catch((error)=>{
        alert(error);
    });
    }
  }