//logging in 
const urlBase = 'http://Campusbuzzz.xyz/api/';
const extension = 'php';

let memberCount = 4;

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
            localStorage.setItem("id", data.id);
            localStorage.setItem("first_name", data.firstName);
            localStorage.setItem("user_type", data.user_type);
            localStorage.setItem("email", data.email);
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
    let adminID = Number(localStorage.getItem("id"));

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

        const data = await response.json();
        console.log("data recieved: ", data);
        
        if (data.error && data.error !== ""){
            //if error finding location make fields blank
            document.getElementById("uniInput").value= "";
        }

        locID = data.LocID;

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
            showToast("Add event successful!", 3000);
            window.location.href = "dashboard.html";
        }

    }catch(error){
        document.getElementById("eventRes").innerHTML= "failed to add event!";
    }

}

// option to add more members to RSO when creating
async function doAddMemberField(){
    memberCount++;

    const header = document.createElement("h2");
    header.textContent = `Member ${memberCount}:`;

    const input = document.createElement("input");
    input.type = "text";
    input.id = `m${memberCount}Input`;
    input.classList.add("memberInp");
    input.style.marginBottom = "50px";
    input.style.width = "500px";
    input.style.height = "40px";
    
    const br = document.createElement("br");

    const container = document.getElementById("fields");
    const button = document.getElementById("addMemberBtn");

    container.insertBefore(header, button);
    container.insertBefore(input, button);
    container.insertBefore(br, button);
}

// create an RSO
async function doCreateRSO(){
    // enfore all fields must be filled
    let filled = true;
    let fields = document.querySelectorAll("input[type='text']");

    fields.forEach(field => {
        if(field.value == ""){
            filled = false;
        }
    })

    let resBox = document.getElementById("rsoResStudent") || document.getElementById("rsoResAdmin");

    if(!filled){
        if(resBox){
            resBox.innerHTML = "there are missing fields!";
        }        return;
    }

    let members = []
    let inputs = document.querySelectorAll(".memberInp");

    inputs.forEach(input => {
        if(input.value !== ""){
            members.push(input.value);
        }
    });

    let name = document.getElementById("nameInput").value;

    let phoneElement = document.getElementById("phoneInput");
    let phone = phoneElement ? phoneElement.value : "";

    if (resBox) resBox.innerHTML = "";

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
            let inputs = document.querySelectorAll(".memberInp");
            inputs.forEach(input => input.value = "")
            return;
        }
    }catch(error){
        if (resBox){
            resBox.innerHTML = "need at least 4 other members!";
        }
        return;
    }

    let rsoInfo = {
        UIDs: data.results,
        Student_promoted: data.results[0],
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

// kate need this one
function getUserType() {
    return localStorage.getItem("user_type");
}

function getEmail(){
    const email = localStorage.getItem("email");
    if(!email){
        console.error("Email not found");
        return null;
    }
    return email.split('@')[1];
}

function getSelectedEvent(){
    const eventSelect = document.getElementById('eventSelect');
    return eventSelect.value;
}

async function getAllRSOs(){
    const domain = getEmail();
    if(!domain) return;

    let url = urlBase + 'GetRSOs.' + extension;
    
    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({Domain: domain}),
        });

        const data = await response.json();
        console.log("API response:", data);

        if(data.error && data.error !== ""){
            console.log('api error:', data.error);
        }
        else{
            if (Array.isArray(data.results) && data.results.length > 0){
                const rsoContainer = document.getElementById("rsoCont");  
                rsoContainer.innerHTML = ''; 

                data.results.forEach(rso => {
                    const rsoDiv = document.createElement('div');
                    rsoDiv.classList.add('rsoCard');

                    let statusClass = 'request';
                    let status = 'Request';

                    if(rso.status == 'joined'){
                        statusClass = 'joined';
                        status = 'Joined';
                    }
                    else if (rso.status == 'pending'){
                        statusClass = 'pending';
                        status = 'Pending';
                    }

                    rsoDiv.innerHTML = `
                        <div class="card">
                            <h5 class="cardTitle>${rso.name}</h5>
                            <button class="rsoButton ${statusClass}" ${status === 'Request' ? `onclick='requestJoin(${rso.id})'` : 'disabled'}>
                                ${status}
                            </button>
                        </div>
                    `;

                    rsoContainer.appendChild(rsoDiv);
                });

            }else {
                console.log('No results found');
            }
        }
    }catch(error){
        console.log('Error fetching RSOs');
    }

}

async function requestJoin(){

}

async function getEvents(){
    const id = localStorage.getItem("id");

    let url = urlBase + 'GetFilteredEvents.' + extension;

    let search = { UID : id};

    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify(search),
        });

        const eventRes = await response.json();
        console.log("API response:", eventRes);

        if (eventRes.error && eventRes.error !== ""){
            console.log('api error:', eventRes.error);
        } else{
            if (Array.isArray(eventRes.results) && eventRes.results.length > 0){
                const eventContainer = document.getElementById("eventCardContainer");  
                eventContainer.innerHTML = ''; 

                eventRes.results.forEach(event => {
                    //make a rso card for each event and send each to the event info page 
                    const eventDiv = document.createElement('div');
                    eventDiv.classList.add('rsoCard'); 
                    eventDiv.innerHTML = `
                        <div class="eventcard">
                            <div class="card-body">
                                <h5 class="event-title" >
                                    <a href="eventInfo.html?eventId=${event.Events_ID}" class="event-link">
                                        ${event.Event_name}
                                     </a>
                                </h5>
                                <p class="card-text"><small class="text-muted">${event.Date} | ${event.Event_time} </small></p>
                                <p class="card-text">${event.Description}</p>
                            </div>
                        </div>
                    `;
                    eventContainer.appendChild(eventDiv);

                });
            }
            else{
                console.log('No results found or invalid response structure');
            }
        }
    }catch(error){
        console.log('Error fetching Events');

    }
    
}

async function getRsos(){
    let url = urlBase + 'GetRSOGroups.'+ extension;

    const id = localStorage.getItem("id");

    let userInf = {UID : id};

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userInf),
        });
        
        const rsoRes = await response.json();

        if (rsoRes.error && rsoRes.error !== ""){
            console.log('error: ', rsoRes.error);
        }else{
            //if an rso is found make elements for each one 
            if (Array.isArray(rsoRes.results) && rsoRes.results.length > 0) {
                const rsoContainer = document.getElementById("rsoListContainer");
                rsoContainer.innerHTML =''; 

                rsoRes.results.forEach(rso => {
                    const rsoDiv = document.createElement('div');
                    rsoDiv.classList.add('col-md-4', 'mb-3'); 
                    rsoDiv.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${rso.RSO_name}</h5>
                            </div>
                        </div>
                    `;
                    rsoContainer.appendChild(rsoDiv);
                });
            } else {
                console.log('No results found or invalid response structure');
                document.getElementById('rsoCont').innerHTML = `<p>No RSOs found.</p>`;
            }
        }
    } catch(error){
        console.log('Error fetching RSOS');
    }
}

async function getEventInfo(){
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('eventId');

    if (!eventId) return;
    const url = urlBase + 'GetEvent.'+ extension;

    const eventPayload = {
        Events_ID : eventId
    };

    console.log('event id ', eventPayload);


    try{
        const response = await fetch ( url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eventPayload)
        })

        const eventInfoData = await response.json();
        console.log('event info: ', eventInfoData);

        document.getElementById('eventName').innerText = eventInfoData.results.Event_name;
        document.getElementById('eventDate').innerText = eventInfoData.results.Date;
        document.getElementById('eventTime').innerText = eventInfoData.results.Event_time;
        document.getElementById('eventDescription').innerText = eventInfoData.results.Description;
        document.getElementById('eventType').innerText = `Type: ${eventInfoData.results.Event_type}`;

      
    } catch (error){
        console.log('error fetching event info');
    }

}

async function getComments(){
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('eventId');

    if (!eventId) return;
    const url = urlBase + 'GetComments.'+ extension;

    const commentPayload={
        Events_ID : eventId
    };

    console.log('comment info sent to api: ', commentPayload);

    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(commentPayload)
        })

        const commentInfo = await response.json();

        if (commentInfo.error && commentInfo.error !== ""){
            console.log('error: ', commentInfo.error);
        }else{
            //if a comment or multiple comments iterate through the array 
            if (Array.isArray(commentInfo.results) && commentInfo.results.length > 0) {
                document.getElementById('commentStatus').innerText = ''

                const commentCont = document.getElementById("commentCont");
                commentCont.innerHTML =''; 

                commentInfo.results.forEach(comment => {
                    const commentDiv = document.createElement('div');
                    commentDiv.classList.add('col-md-4', 'mb-3'); 
                    commentDiv.innerHTML = `
                        <div class="commentCard">
                            <div class="card-body">
                                <h5 class="commentText">${comment.Text}</h5>
                                <p class="card-text"><small class="text-muted">${comment.Timestamp}</small></p>
                                <p class="card-text">${comment.Rating}</p>
                            </div>
                        </div>
                    `;
                    commentCont.appendChild(commentDiv);
                });
            } else {
                console.log('No results found or invalid response structure');
                document.getElementById('commentStatus').innerHTML = `<p>No Comments found.</p>`;
            }
        }



    }catch(error){
        console.log('Error fetching comments');
    }
}

window.onload = function (){
    getEvents();
    getRsos();
    fetchUniversities();
}

// on window load
document.addEventListener("DOMContentLoaded", () => {
    const userType = getUserType();
    console.log("User Type Detected:", userType);

    if(userType == "Admin"){
        console.log("Hidding student");
        document.getElementById("studentOnly")?.classList.add("hidden");
    }
    else{
        console.log("Hidding admin");
        document.getElementById("adminOnly")?.classList.add("hidden");
    }
})


if (window.location.pathname.includes('eventInfo.html')) {
    getEventInfo();
    getComments();
}

if(window.location.pathname.includes('joinRSORequest.html')){
    getAllRSOs();
}