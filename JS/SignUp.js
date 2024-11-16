import {app, getDatabase, ref, set, get, child, update, remove}
from "./RequestDB.js"

const db =getDatabase()


var empCheck = document.getElementById("emp")
var stuCheck = document.getElementById("stu")
var empEmail = document.getElementById("empEmail")
var idNUm = document.getElementById("idNum")
var Pass = document.getElementById("Pass")

var create = document.getElementById("create")

create.addEventListener('click', createUser)
// create.onclick=function(){createUser()}

function createUser(){
    set(ref(db, "Students/"+ idNUm.value),{
      ID: idNUm.value,
      Password: Pass.value
  })
    .then(()=>{
      alert("Data added successfully");
  })
  .catch((error)=>{
      alert(error);
  });
  }