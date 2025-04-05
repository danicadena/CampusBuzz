//logging in 
const urlBase = 'http://Campusbuzzz.xyz/api/';
const extension = 'php';

async function doLogin(){
     let user = document.getElementById("usernameInp").value;
     let password = document.getElementById("passwordInp").value;

     document.getElementById("loginRes").innerHTML = "";

     let userInfo = {
        Username: user,
        Password : password
    };

     let url = urlBase + 'Login.' + extension;
     console.log("url: " + url);

     console.log("sending data to backend: ", userInfo);

     try{
        const response = await fetch(url,{
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json'
            },
            body:JSON.stringify(userInfo),
            mode: 'no-cors'
        });

        const data = await response.json();
        console.log("data recieved: ", data);

        if (data.error && data.error !== ""){
            document.getElementById("loginRes").innerHTML = "User does not exist: Please Check User/Password";
            //reset the input fields if unsuccessful login
            document.getElementById("usernameInp") = "";
            document.getElementById("passwordInp") = "";
        } else{
            localStorage.setItem("UID", data.UID);
            localStorage.setItem("first_name", data.firstName);
            localStorage.setItem("user_type", data.user_type);
            window.location.href = "dashboard.html";
        }

     }catch(error){
        console.log("Error fetching data: ", error);
        document.getElementById("loginRes").innerHTML = "failed login";
     }

}

async function doRegister(){
    //get the user input from regsiter page 
    let email = document.getElementById("emailInp").value;
    let firstName = document.getElementById("firstNameInp").value;
    let lastName = document.getElementById("lastNameInp").value;
    let userType = getSelectedUser();//check since dropdown:
    let username = document.getElementById("userInp").value;
    let password = document.getElementById("passInp").value;
    let university = getSelectedUni(); //check since dropdown:

    document.getElementById("registerRes").innerHTML= "";
    console.log("user type: " + userType);


    let registerInfo = {
        Email: email,
        First : firstName,
        Last : lastName,
        Username: username,
        Password: password,
        User_Type : userType,
        University_name : university
    };

    console.log("info being sent to backend: ",  registerInfo);
    let url = urlBase + 'Register.'+ extension;
    console.log("url: " , url);

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(registerInfo),
            mode : 'no-cors'
        });

        const data = await response.json();
        console.log("data recieved: ", data);
        
        if (data.error && data.error !== ""){
            //if error registering make all the fields blank
            document.getElementById("emailInp").value= "";
            document.getElementById("firstNameInp").value = "";
            document.getElementById("lastNameInp").value = "";
            document.getElementById("userSelect").value = ""; 
            document.getElementById("userInp").value = "";
            document.getElementById("passInp").value = "";
            document.getElementById("userSelectSpacing").value = ""; 
        }
        else{
            //otherwise automatically log in a user 
            window.location.href = "dashboard.html";
        }

    }catch(error){
        document.getElementById("registerRes").innerHTML= "failed register";
    }

}

// add an event
async function doAddEvent(){
	let uni = document.getElementById("uniInput").value
	let time = document.getElementById("timeInput").value
	let date = document.getElementById("dateInput").value
	let desc = document.getElementById("descInput").value
	let name = document.getElementById("nameInp").value
	let type = getSelectedEvent()
    let adminID = localStorage.getItem("UID");

    let locID;

    document.getElementById("eventRes").innerHTML = "";

    let url = urlBase + 'GetLocID.' + extension;
	console.log("url: ", url);

    let uniName = {
        Lname: uni
    }

	// get LocID with api
	try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(uniName),
            mode : 'no-cors'
        });

        locID = await response.json();
        console.log("data recieved: ", locID);
        
        if (data.error && data.error !== ""){
            //if error finding location make fields blank
            document.getElementById("uniInput").value= "";
        }

    }catch(error){
        document.getElementById("eventRes").innerHTML= "choose a different location!";
        return;
    }

	let eventInfo = {
		LocID: locID,
		Event_time: time,
		Date: date,
		Event_name: name,
		Description: desc,
		Event_type: type,
		Admins_ID: adminID
	}

	console.log("info being sent to backend: ", eventInfo);
	let url1 = urlBase + 'CreateEvent.' + extension;
	console.log("url: ", url1);

	// add event api
	try{
        const response = await fetch (url1, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(eventInfo),
            mode : 'no-cors'
        });

        const data = await response.json();
        console.log("data recieved: ", data);

        if (data.error && data.error !== ""){
            //if error adding an event make all the fields blank
            document.getElementById("uniInput").value= "";
            document.getElementById("timeInput").value = "";
            document.getElementById("dateInput").value = "";
            document.getElementById("descInput").value = ""; 
            document.getElementById("nameInput").value = "";
            document.getElementById("eventSelect").value = "";
        }
        else{
            //otherwise go back to dashboard
            window.location.href = "dashboard.html";
            showToast("Add event successful!", 3000);
        }

    }catch(error){
        document.getElementById("eventRes").innerHTML= "failed to add event!";
    }

}

// option to add more members to RSO when creating
async function doAddMemberField(){
    let count = 4;

    document.getElementById("addMemberBtn").addEventListener("click", () => {
        count++;

        const header = document.createElement("h2");
        header.textContent = `Member ${count}:`;

        const input = document.createElement("input");
        input.type = "text";
        input.id = `m${count}Input`;
        input.classList.add("memberInp");

        const container = document.getElementById("fields");
        const button = document.getElementById("addMemberBtn");

        container.insertBefore(header, button);
        container.insertBefore(input, button);
    })
}

// create an RSO
async function doCreateRSO(){
    let admin = document.getElementById("adminInput").value;

    let members = []
    members.push(admin);
    let inputs = document.querySelectorAll(".memberInp").value;

    inputs.forEach(input => {
        if(input.value !== ""){
            members.push(input.value);
        }
    });

    let name = document.getElementById("nameInput").value;
    let phone = document.getElementById("phoneInput").value;

    document.getElementById("rsoRes").innerHTML= "";

    let uids = {
        Emails: members
    };

    console.log("info being sent to backend: ",  uids);
    let url = urlBase + 'GetUserIDforRSO.'+ extension;
    console.log("url: " , url);

    let data;
    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(uids),
            mode : 'no-cors'
        });

        data = await response.json();
        console.log("data recieved: ", data);
        
        if (data.error && data.error !== ""){
            //if error finding uids make all email fields blank
            document.getElementById("adminInput").value= "";
            
            let inputs = document.querySelectorAll(".memberInp");
            inputs.forEach(input => input.value = "")
                return;
        }
    }catch(error){
        document.getElementById("rsoRes").innerHTML= "need at least 4 other members!";
        return;
    }

    let rsoInfo = {
        UIDs: data.uids,
        Student_promoted: uids.Emails[0],
        Admin_phone: phone,
        RSO_name: name
    };

    console.log("info being sent to backend: ",  rsoInfo);
    let url1 = urlBase + 'CreateRSO.'+ extension;
    console.log("url: " , url1);

    try{
        const response = await fetch (url1, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(rsoInfo),
            mode : 'no-cors'
        });

        const data = await response.json();
        console.log("data recieved: ", data);
        
        if (data.error && data.error !== ""){
            //if error creating an rso make all fields blank
            document.getElementById("adminInput").value= "";
            inputs.forEach(input => input.value = "");
            document.getElementById("phoneInput");
            document.getElementById("nameInput");
        }
        else{
            //otherwise go back to dashboard
            window.location.href = "dashboard.html";
            showToast("Add rso successful!", 3000);
        }
    }catch(error){
        document.getElementById("rsoRes").innerHTML= "failed to add rso!";
    }

}

// for the university dropdown we will connect to uni locations from db
async function fetchUniversities(){
    try{
        let url = urlBase + 'GetLocations.'+extension;
        console.log("url: " + url);
        const response = await fetch(url,{
            method: 'GET',
            headers: {
                'Accept' : 'application/json'
            }, 
        });

        const data = await response.json();
        const uniSelect = document.getElementById('userSelectSpacing');

        //a default option before user inputs something
        uniSelect.innerHTML='';
        const defaultOption = document.createElement('option');
        defaultOption.textContent= 'Select University';
        defaultOption.value = '';
        uniSelect.appendChild(defaultOption);

        if (data.results && Array.isArray(data.results)){
            data.results.forEach(university => {
                const option = document.createElement('option');
                option.textContent = university;
                option.value = university;
                uniSelect.appendChild(option);

            })
        } else{
            console.log("no unis found in the response");
        }

    }catch(error){
        console.log("error fetching unis");
    }
}

// temporary pop up message for success
function showToast(message = "Success!", duration = 3000) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.remove("hidden");
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
        toast.classList.add("hidden");
    }, duration);
}

function getSelectedUni(){
    const uniSelect = document.getElementById('userSelectSpacing');
    return uniSelect.value;
}

function getSelectedUser(){
    const userSelect = document.getElementById("userSelect");
    return userSelect.value;
}

function getSelectedEvent(){
    const eventSelect = document.getElementById('eventSelect');
    return eventSelect.value;
}

window.onload = fetchUniversities;