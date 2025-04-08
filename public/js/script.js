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

        if (data.error && data.error === "An event already exists at this location and time!"){
           alert("An event already exists at this time and location");
            //if error adding an event make all the selected fields empty
            document.getElementById("uniInput").value= "";
            document.getElementById("timeInput").value = "";
            document.getElementById("dateInput").value = "";
        }
        else if (data.error !== ""){
             //if error adding an event make all the fields blank
             document.getElementById("uniInput").value= "";
             document.getElementById("timeInput").value = "";
             document.getElementById("dateInput").value = "";
             document.getElementById("descInput").value = ""; 
             document.getElementById("nameInput").value = "";
             document.getElementById("eventSelect").value = "";
        }    
        else {
            //otherwise go back to dashboard
            alert("Event added successfully!")
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
            alert("Add RSO successful!");
            window.location.href = "dashboard.html";
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

function getSelectedUni(){
    const uniSelect = document.getElementById('userSelectSpacing');
    return uniSelect.value;
}

function getSelectedUniToCreate(){
    const uniSelect = document.getElementById('userSelectUniversity');
    return uniSelect.value;
}

function getSelectedUser(){
    const userSelect = document.getElementById("userSelect");
    return userSelect.value;
}

function getUserID(){
    return localStorage.getItem("id");
}

function getUserType() {
    return localStorage.getItem("user_type");
}

function getAdminId(){
    return localStorage.getItem("AdminId");
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

async function getUniversityProfiles(){
    let url = urlBase + 'GetUniversityProfile.' + extension;

    try{
        const response = await fetch (url,{
            method: 'GET',
            headers:{
                'Content-type': 'application/json'
            }
        });

        const data = await response.json();
        console.log("API response:", data);

        if(data.error && data.error !== ""){
            console.log('api error:', data.error);
        }
        else{
            if (Array.isArray(data.results) && data.results.length > 0){
                const superContainer = document.getElementById("superCont");
                if (!superContainer) {
                    console.error("Super container element not found");
                    return;
                }
                superContainer.innerHTML = ''; 

                data.results.forEach(uni => {
                    const superDiv = document.createElement('div');
                    superDiv.classList.add('uniCard');

                    superDiv.innerHTML = `
                        <div class="uniCardClass">
                            <div class="cardTitle">${uni.Uni_name}</div>
                            <div class="cardImageWrapper">
                                <img src="${uni.Profile_pic}" alt="${uni.Uni_name} Logo">
                            </div>
                            <div class="bottomRow">
                                <div class="cardInfo">Enrollment: ${uni.Student_num}</div>
                                <a href="universityProfile.html?uni_id=${uni.Uni_ID}" class="viewLink">View</a>
                            </div>
                        </div>
                    `;

                    superContainer.appendChild(superDiv);
                });

            }else {
                console.log('No results found');
            }
        }
    }catch(error){
        console.log('Error fetching Universities');
    }
}

async function getAllRSOs(){
    const domain = getEmail();
    const uid = getUserID();
    if(!domain || !uid) return;

    let url = urlBase + 'GetRSOs.' + extension;
    
    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                UID: uid,
                Domain: domain
            }),
        });

        const data = await response.json();
        console.log("API response:", data);

        if(data.error && data.error !== ""){
            console.log('api error:', data.error);
        }
        else{
            if (Array.isArray(data.results) && data.results.length > 0){
                const rsoContainer = document.getElementById("rsoCont");
                if (!rsoContainer) {
                    console.error("RSO container element not found");
                    return;
                }
                rsoContainer.innerHTML = ''; 

                data.results.forEach(rso => {
                    const rsoDiv = document.createElement('div');
                    rsoDiv.classList.add('rsoCard');

                    let statusClass = 'request';
                    let status = 'Request';
                    let disabled = false;

                    if(rso.status == 'approved'){
                        statusClass = 'joined';
                        status = 'Joined';
                        disabled = true;
                    }
                    else if (rso.status == 'pending'){
                        statusClass = 'pending';
                        status = 'Pending';
                        disabled = true;
                    }

                    rsoDiv.innerHTML = `
                        <div class="card">
                            <div class="cardTitle">${rso.name}</div>
                            <div class="cardButtonWrapper">
                                <button class="rsoButton ${statusClass}" 
                                    ${status === 'Request' ? `onclick='requestJoin("${rso.id}")'` : 'disabled'}>
                                    ${status}
                                </button>
                            </div>
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

async function requestJoin(rsoID){
    const button = event.target;
    const card = button.closest('.card');

    // change state of button
    button.classList.remove('request');
    button.classList.add('pending');
    button.textContent = 'Pending';
    button.disabled = true;

    const uid = getUserID();

    let url = urlBase + 'RequestJoin.'+ extension;

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                UID: uid,
                RSO_name: rsoID
            }),
        });

        const data = await response.json();
        console.log("Request join response:", data);

        if(data.error && data.error !== ""){
            console.log('Error requesting to join:', data.error);
            // Revert button state if there's an error
            button.classList.remove('pending');
            button.classList.add('request');
            button.textContent = 'Request';
            button.disabled = false;
        } else {
            alert("Request to join successful!");
        }
    } catch(error) {
        console.log('Error requesting to join RSO:', error);
        // Revert button state if there's an error
        button.classList.remove('pending');
        button.classList.add('request');
        button.textContent = 'Request';
        button.disabled = false;
    }
}

async function getEvents(){
    const id = getUserID();

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
        console.log("API response (list of events):", eventRes);

        if (eventRes.error && eventRes.error !== ""){
            console.log('api error:', eventRes.error);
        } else{
            if (Array.isArray(eventRes.results) && eventRes.results.length > 0){
                const eventContainer = getUserType() === "Admin"? document.getElementById("adminEventCardContainer"): document.getElementById("eventCardContainer");                
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

                    if(getUserType() === "Admin"){
                        const adminBtns = document.createElement("div");
                        adminBtns.innerHTML = `
                            <button class="eventOptionsBtn" onclick='deleteEvent(${event.Events_ID});'>Delete</button>
                        `;
                        eventDiv.appendChild(adminBtns);
                    }
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
                const rsoContainer = getUserType() === "Admin" ? document.getElementById("rsoListAdminContainer") : document.getElementById("rsoListContainer");
                rsoContainer.innerHTML =''; 

                rsoRes.results.forEach(rso => {

                    let rsoButtons = "";
                    let rsoTitle= `<h5 class="card-title">${rso.RSO_name} | ${rso.Status} </h5>
`

                    if (getUserType() === "Student"){
                        const rsoButtons = document.createElement("div");
                        rsoButtons.innerHTML=  `
                            <button class="rsoOptionBtn" onclick='deleteRSO(${rso.RSOs_ID});'> Leave RSO </button>
                        `;
                    } else if(getUserType() === "Admin"){
                        rsoTitle=`
                            <h5 class="card-title">
                                <a href="RSOStatus.html?rsoId=${rso.RSOs_ID}" class="admin-rso-link">
                                ${rso.RSO_name}
                                </a>
                            </h5>
                        `;
                    }
                    
                    const rsoDiv = document.createElement('div');
                    rsoDiv.classList.add('col-md-4', 'mb-3'); 
                    rsoDiv.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                ${rsoTitle}
                                ${rsoButtons}
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

//TODO: 
async function deleteRSO(rsoId){
    const confirmRsoDel = confirm("Are you sure you want to delete this RSO?") 
    if (!confirmRsoDel) return;

    const uid = getUserID();
    console.log("userId: " , uid);

    const delRSOPayload = {
        UID : uid, 
        RSOs_ID : rsoId
    }

    let url = urlBase + 'LeaveRSO.' + extension;
    console.log("delete rso payload ", delRSOPayload);

    try{
        const response = await fetch (url, {
            method: 'POST',
            headers: {
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(delRSOPayload)
        });

        const delRes = await response.json();

        if (delRes.error && delRes.error !== ""){
            alert("Error deleting RSO");
            getRsos();
        } else{
            alert("RSO Deleted successfully");
            
        }
    } catch(error){
        console.log("error with rso del: ", error);
    }
}

//TODO: 
async function getAdmin(){
    const uid = getUserID();

    const getAdminPayload = { UID: uid};

    let url = urlBase + 'GetAdminID.' + extension;

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers: {
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(getAdminPayload)
        });

        const data = await response.json();

        if (data.error !== ""){
            console.log("Error fetching admin id");
        } else {
            localStorage.setItem("AdminId", data.Admins_ID);
        }

    } catch (error){
        console.log('error fetching error');
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

        const userType = getUserType(); 
        if (userType === "Admin") {
            document.getElementById("editBtn").style.display = "inline-block";  
        }

      
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

                const currentUserId = getUserID();
                console.log('currentUserId: ', currentUserId); 

                commentInfo.results.forEach(comment => {
                    console.log('Checking comment UID:', comment.UID); 

                    const commentOwner = Number (comment.UID) === Number (currentUserId);
                    console.log('comment owner:', commentOwner); 


                    const buttonsHTML = commentOwner
                    ? `
                    <button class="btn btn-danger" onclick="deleteComment(${comment.UID}, ${eventId})">Delete</button>
                    <button 
                        class="btn btn-warning editBtn"
                            data-uid="${comment.UID}"
                            data-eventid="${eventId}"
                            data-rating="${comment.Rating}"
                            data-text="${encodeURIComponent(comment.Text)}"
                        >Edit</button>
                    ` : '';

                    const commentDiv = document.createElement('div');
                    commentDiv.classList.add('col-md-4', 'mb-3'); 

                    
                    commentDiv.innerHTML = `
                        <div class="commentCard">
                            <div class="card-body">
                                <h5 class="commentText">${comment.Text}</h5>
                                <p class="card-text"><small class="text-muted">${comment.Timestamp}</small></p>
                                <p class="card-text">${comment.Rating}</p>
                                ${buttonsHTML}
                            </div>
                        </div>
                    `;
                    commentCont.appendChild(commentDiv);
                });

                document.querySelectorAll('.editBtn').forEach(button => {
                    console.log('Adding event listener for Edit button'); 
                    button.addEventListener('click', () => {
                        const uid = Number(button.dataset.uid);
                        const eid = Number(button.dataset.eventid);
                        const rating = button.dataset.rating;
                        const text = decodeURIComponent(button.dataset.text);

                        console.log(`Edit button clicked for comment UID: ${uid}, Event ID: ${eid}`); 
                        editComment(uid, eid, rating, text);
                    });
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


async function sendComment(){
    const userId = Number(localStorage.getItem("id"));

    const urlParams = new URLSearchParams(window.location.search);
    const eventId = Number(urlParams.get('eventId'));

    const rating = Number(document.getElementById("ratingInfo").value.trim());
    const commentText = document.getElementById("comment").value.trim();

    const addCommentPayload={
        UID : userId,
        Events_ID : eventId, 
        Rating : rating, 
        Text : commentText
    };

    const url = urlBase + 'AddComment.'+ extension;

    console.log('info sent to backend for adding comment: ', addCommentPayload);

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-Type': 'application/json'
            }, 
            body: JSON.stringify(addCommentPayload)
        })

        const sendCommentInfo = await response.json();
        
        //in the case that you can
        if (sendCommentInfo && sendCommentInfo.error !== ""){
            console.log('comment could not be made');

        }else{
            console.log('success adding comment');
            getComments();
        }

    }catch (error){
        console.log('Error adding comment:' , error);
    }

}

async function deleteEvent(eventId){   
    const confirmDel = confirm("Are you sure you want to delete this event?") 
    if (!confirmDel) return;

    const url = urlBase + 'DeleteEvent.' + extension;

    const delPayload ={
        Events_ID: eventId
    };

    console.log('del payload: ', delPayload);

    try {
        const response = await fetch (url, {
            method: 'POST',
            headers:{
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(delPayload)
        })

        const data = await response.json();
        if (data.error && data.error !== ""){
            alert("Error deleting event: " + result.error);
        } else{
            alert("Event deleted successfully!"); 
            getEvents();
        }


    }catch (error){
        console.log('Error deleting event');
        alert("Something went wrong when deleting");

    }
}

async function deleteComment(userId, eventId){
    const confirmDel = confirm("Are you sure you want to delete this comment?") 
    if (!confirmDel) return;

    const delPayload={
        UID: userId,
        Events_ID: eventId
    };

    console.log("Sending delete payload:", delPayload);

    const url = urlBase + 'DeleteComment.' + extension;

    try {
        const response = await fetch (url, {
            method: 'POST',
            headers:{
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(delPayload)
        })

        const data = await response.json();
        console.log("Delete response:", data);

        if (data.error == ""){
            getComments();
            alert('Comment Deleted successfully!');
        } else{
            alert('Error deleting comment!');
        }
    
    }catch (error){
        console.log('error deleting comment');
    }
}
 
async function updateEvent(){
    console.log('updateEvent function called');
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('eventId');
    console.log('Event ID:', eventId);

    if (!eventId)  return;

    document.getElementById("updateEventModal").style.display = "block";
    const url = urlBase + 'GetEvent.' + extension;
    const eventPayload = { Events_ID: eventId };

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(eventPayload)
    })
        .then(response => response.json())
        .then(eventInfoData => {
            console.log('Event Info:', eventInfoData);
            document.getElementById('eventNameInput').value = eventInfoData.results.Event_name || '';
            document.getElementById('eventTimeInput').value = eventInfoData.results.Event_time || '';
            document.getElementById('eventDateInput').value = eventInfoData.results.Date || '';
            document.getElementById('eventDescInput').value = eventInfoData.results.Description || '';
        })
        .catch(error => console.log('Error fetching event info:', error));

    document.getElementById('saveEventButton').addEventListener('click', async () => {
        const eventPayload = {
            Events_ID: eventId,
            Admins_ID: getAdminId(),
            Event_name: document.getElementById('eventNameInput').value,
            Event_time: document.getElementById('eventTimeInput').value,
            Date: document.getElementById('eventDateInput').value,
            Description: document.getElementById('eventDescInput').value
        };

        const updateUrl = urlBase + 'UpdateEvent.' + extension;

        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(eventPayload)
            });

            const updateData = await response.json();
            console.log('Update response:', updateData);

            if (updateData.error === "") {
                alert('Event updated successfully!');
                document.getElementById("updateEventModal").style.display = "none";
                getEventInfo();
            } else if (updateData.error === "Not authorized to update this event!"){
                alert('Not authorized to update this event!');
            }
            else {
                console.log('Failed update response data:', updateData);
                alert('Failed to update event');
            }
        } catch (error) {
            alert('An error occurred while updating the event');
        }
    });

    document.getElementById('cancelButton').addEventListener('click', () => {
        document.getElementById("updateEventModal").style.display = "none";
    });
}

function editComment(userId, eventId, currentRating, currentText) {
    document.getElementById("ratingInfo").value = currentRating;
    document.getElementById("comment").value = currentText;

    const submitButton = document.querySelector('.submitComment');
    submitButton.innerText = "Update Comment";
    submitButton.removeEventListener('click', sendComment);
    submitButton.addEventListener('click', () => updateComment(userId, eventId, document.getElementById("ratingInfo").value, document.getElementById("comment").value));
}


async function updateComment(userId, eventId, newRating, newText){
    const updatePayload = {
        UID: userId,
        Events_ID: eventId,
        Rating: newRating,
        Text: newText
    };

    const url = urlBase + 'EditComment.' + extension;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatePayload)
        });

        const data = await response.json();
        console.log('API Response:', data);

        if (data.error === "") {
            alert("Comment updated sucessfully!")
            getComments();
            
            const submitButton = document.querySelector('.submitComment');
            submitButton.innerText = "Submit";
            submitButton.removeEventListener('click', updateComment);
            submitButton.addEventListener('click', sendComment);
            document.getElementById("ratingInfo").value = "";
            document.getElementById("comment").value = "";

        } else {
             alert("Error updating comment");
        }
    } catch (error) {
        console.error('Error updating comment', error);
    }
}

// drop down menu for university profiles to create
async function fetchUniversitiesForProfile(){
    try{
        let url = urlBase + 'GetUncreatedUniversity.'+extension;
        console.log("url: " + url);
        const response = await fetch(url,{
            method: 'GET',
            headers: {
                'Accept' : 'application/json'
            }, 
        });

        const data = await response.json();
        console.log("Uncreated Universities: ", data);
        const uniSelect = document.getElementById('userSelectUniversity');

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
        console.log("Error fetching universities to create");
    }
}

async function doAddUniversity(){
	let enrollment = document.getElementById("studentInp").value
	let uni = getSelectedUniToCreate();
    let uid = Number(localStorage.getItem("id"));

    document.getElementById("universityRes").innerHTML = "";

    const imagePathMap = {
        "University of Central Florida": "/public/images/ucf.png",
        "University of Texas": "/public/images/texas.png",
        "University of Cincinnati": "/public/images/cincy.png",
        "University of Florida": "/public/images/florida.png",
        "Baylor University": "/public/images/baylor.png",
        "Iowa State University": "/public/images/iowa.png",
        "University of Kansas": "/public/images/kansas.png",
        "Kansas State University": "/public/images/kstate.png",
        "Stanford University": "/public/images/stanford.png",
        "Harvard University": "/public/images/harvard.png",
        "Arizona State University": "/public/images/asu.png",
        "University of Houston": "/public/images/houston.png",
        "Brigham Young University": "/public/images/byu.png",
        "Texas Christian University": "/public/images/tcu.png",
        "West Virginia University": "/public/images/west.png"
    };
    
    const path = imagePathMap[uni] || "";

    let url = urlBase + 'GetSuperID.' + extension;
	console.log("url: ", url);

    let superAdmin;
    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                UID: uid
            })
        });

        const superID = await response.json();
        console.log("data recieved: ", superID);

        if (superID.error && superID.error !== ""){
            document.getElementById("studentInp").value= "";
            document.getElementById("userSelectUniversity").value = "";
            return;
        }

        superAdmin = superID.SuperAdmins_ID;
    }
    catch(error){
        console.error("Failed to get super admin");
        return;
    }

    let url1 = urlBase + 'CreateUniversity.' + extension;
	console.log("url: ", url1);

	try{
        const response = await fetch (url1, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                Uni_name: uni,
                Student_num: enrollment,
                Profile_pic: path,
                SuperAdmins_ID: superAdmin
            })
        });

        const data = await response.json();
        console.log("data recieved: ", data);
        
        if (data.error && data.error !== ""){
            document.getElementById("studentInp").value= "";
            document.getElementById("userSelectUniversity").value = "";
        }
        else{
            alert("University profile created!");
            window.location.href = "dashboard.html";
        }
    }catch(error){
        document.getElementById("universityRes").innerHTML= "failed to add university!";
    }
}

async function fetchUniversityInfo(uniID){
    let url = urlBase + 'GetAUniversityByID.' + extension;
	console.log("url: ", url);

    try{
        const response = await fetch (url, {
            method: 'POST', 
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                Uni_ID: uniID
            })
        });

        const data = await response.json();
        console.log("data recieved: ", data);

        if (data.error && data.error !== ""){
            console.error("Failed to fetch university info");
            return;
        }
        else{
            document.getElementById("profileName").innerHTML = `Name: <span style="color: black;">${data.result.Uni_name}</span>`;
            document.getElementById("profileEnrollment").innerHTML = `Student Enrollment: <span style="color: black;">${data.result.Student_num}</span>`;
            document.getElementById("profileImg").src = data.result.Profile_pic;
        }
    }catch(error){
        console.error("Failed to fetch university info");
    }
}

async function approveEvent(eventID, button){
    let url = urlBase + 'ApproveEvents.' + extension;

    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                Events_ID: eventID
            })
        });

        const data = await response.json();
        console.log("API response:", data);

        if(data.error && data.error !== ""){
            console.log('api error:', data.error);
        }
        else{
            button.textContent = "Approved";
            button.classList.remove("approveBtn");
            button.classList.add("approvedBtn");
            button.disabled = true;
        }
    }catch(error){
        console.error("Error approving event");
    }
}

async function getSuperEvents(){
    const params = new URLSearchParams(window.location.search);
    const uniID = params.get('uni_id');
    if(!uniID){
        console.error("uni_id not found");
        return;
    }

    let url = urlBase + 'GetSuperEvents.' + extension;

    try{
        const response = await fetch (url,{
            method: 'POST',
            headers:{
                'Content-type': 'application/json'
            },
            body: JSON.stringify({
                Uni_ID: uniID
            })
        });

        const data = await response.json();
        console.log("API response:", data);

        if(data.error && data.error !== ""){
            console.log('api error:', data.error);
        }
        else{
            if (Array.isArray(data.results) && data.results.length > 0){
                const eventContainer = document.getElementById("eventCont");
                if (!eventContainer) {
                    console.error("Event container not found");
                    return;
                }
                eventContainer.innerHTML = ''; 

                data.results.forEach(event => {
                    // format date and time
                    const formattedDate = new Date(event.Date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                
                    const formattedTime = new Date(`1970-01-01T${event.Event_time}`).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    const needsApproval = event.Approval_Status !== "approved";

                    const eventDiv = document.createElement('div');
                    eventDiv.classList.add('eventCard');

                    const approvedMarkup = `
                        <div class="approveWrapper">
                            <button class="approvedBtn" disabled>Approved</button>
                        </div>
                    `;

                    const pendingMarkup = `
                        <div class="approveWrapper">
                            <button class="approveBtn" onclick="approveEvent(${event.Events_ID}, this)">Approve</button>
                        </div>
                    `;

                    eventDiv.innerHTML = `
                        <div class="eventCardClass">
                            <div class="cardHeader">
                                <div class="cardTitle">${event.Event_name}</div>
                                <div class="cardType">${event.Event_type}</div>
                            </div>
                            <div class="cardDate">${formattedDate} at ${formattedTime}</div>
                            <div class="cardDescription">${event.Description}</div>
                            ${event.Approval_Status === "approved" ? approvedMarkup : event.Approval_Status === "pending" ? pendingMarkup : ""}
                        </div>
                    `;

                    eventContainer.appendChild(eventDiv);
                });

            }else {
                console.log('No results found');
            }
        }
    }catch(error){
        console.log('Error fetching events');
    }
}

/*
document.getElementById("searchEvents").addEventListener("input", (e) => {
    const searchText = e.target.value.trim();

    if (searchText === "") {
        //show all the events
        getEvents();
    } else {
        //otherwise show the events relevant to the search
        searchEvents(searchText); 
    }
});


async function searchEvents(query) {
    const id = getUserID();
    const url = urlBase + 'SearchEvent.' + extension;

    const searchPayload = {
        UID: id,
        search: query
    };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(searchPayload)
        });

        const result = await response.json();
        console.log("Search API response:", result);

        const container = getUserType() === "Admin" ? document.getElementById("adminEventCardContainer") : document.getElementById("eventCardContainer");
        container.innerHTML = "";

        if (Array.isArray(result.results) && result.results.length > 0) {
            result.results.forEach(event => {
                const eventDiv = document.createElement('div');
                eventDiv.classList.add('rsoCard');
                eventDiv.innerHTML = `
                    <div class="eventcard">
                        <div class="card-body">
                            <h5 class="event-title">
                                <a href="eventInfo.html?eventId=${event.Events_ID}" class="event-link">
                                    ${event.Event_name}
                                </a>
                            </h5>
                            <p class="card-text"><small class="text-muted">${event.Date} | ${event.Event_time}</small></p>
                            <p class="card-text">${event.Description}</p>
                        </div>
                    </div>
                `;

                if (getUserType() === "Admin") {
                    const adminBtns = document.createElement("div");
                    adminBtns.innerHTML = `<button class="eventOptionsBtn" onclick='deleteEvent(${event.Events_ID});'>Delete</button>`;
                    eventDiv.appendChild(adminBtns);
                }

                container.appendChild(eventDiv);
            });
        } else {
            container.innerHTML = "<p>No events match your search.</p>";
        }
    } catch (error) {
        console.error("Search error:", error);
    }
}
*/

async function getRsoStudentInfo(){
    const params = new URLSearchParams(window.location.search);
    const rsoId = params.get('rsoId');
    console.log("rsoid: ", rsoId);

    let url = urlBase + 'GetStudentsInRSO.' + extension;

    const getRsoStudPayload = {
        RSOs_ID : rsoId
    };

    try{
        const response = await fetch (url, {
            method: 'POST',
            headers: {
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(getRsoStudPayload)
        })

        const rsoStudRes = await response.json();

        if (rsoStudRes.error !== ""){
            document.getElementById("studentRes").value = 'No students Found'
        }
        else{
            if (Array.isArray(rsoStudRes.results) && rsoStudRes.results.length > 0){
                const rsoStud = document.getElementById("rsoStudentCont");                
                rsoStud.innerHTML = ''; 

                rsoStud.results.forEach(student => {
                    const studentDiv = document.createElement('div');

                    studentDiv.innerHTML = `
                            <p><strong>Student:</strong> <span>${student.First} ${student.Last}</span></p>
                            <p><strong>Approval Status:</strong> <span>${student.Approval_Status}</span></p>

                    `;
                    
                    if (student.Approval_Status.toLowerCase() === 'pending'){
                        const approveButton = document.createElement('button');
                        approveButton.textContent = 'Approve Request';
                        approveButton.classList.add('btn', 'btn-success', 'mt-2'); 

                        approveButton.addEventListener('click', () => { 
                            approveStudentRequest(student.UID);
                        });

                        studentDiv.appendChild(approveButton);
                    }
                    rsoStud.appendChild(studentDiv);
                });
            }
            else{
                console.log('No results found or invalid response structure');
            }
        }    
    } catch (error) {
        console.log('error loading students: ', error);
    }
}

async function approveStudentRequest(studentID){
    const params = new URLSearchParams(window.location.search);
    const rsoId = params.get('rsoId');
    const adminId = getAdminId();

    const approvePayload= {
        Admins_ID : adminId, 
        UID : studentID, 
        RSOs_ID :rsoId
    }

    let url = urlBase + 'ApproveJoin.' + extension;

    console.log("approvePayload: ", approvePayload);

    try{
        const response = await fetch (url, {
            method : 'POST', 
            headers: {
                'Content-Type' : 'application/json'
            },
            body: JSON.stringify(approvePayload)
        })

        const data = await response.json();

        if (data.error === "You are not the admin of this RSO!"){
            alert("You are not the admin of this RSO!");
        } else if (data.error !== ""){
            alert("Error letting this student join")
        } else{
            alert("Student successfully added");
            getRsoStudentInfo();
        }
    }catch(error){
        console.log("approve error: ", error);
    }
}

window.onload = function (){
    getEvents();
    getRsos();
    fetchUniversities();
    getSuperEvents();
}

// on window load
document.addEventListener("DOMContentLoaded", () => {
    if(window.location.pathname.includes('createRSO.html')){
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
    }

    if(window.location.pathname.includes('joinRSORequest.html')){
        console.log(" DOM loaded, running getAllRSOs()");
        getAllRSOs();
    }

    if(window.location.pathname.includes('addUniversity.html')){
        console.log(" DOM loaded, running fetchUniversitiesForProfile()");
        fetchUniversitiesForProfile();
    }

    if(window.location.pathname.includes('dashboard.html')){
        getUniversityProfiles();
    }
})


if (window.location.pathname.includes('eventInfo.html')) {
    getEventInfo();
    getComments();
}

if (window.location.pathname.includes('RSOStatus.html')) {
    getRsoStudentInfo();
}
