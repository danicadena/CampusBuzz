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
    let userType = document.getElementById("userSelect").value; //check since dropdown:
    let username = document.getElementById("userInp").value;
    let password = document.getElementById("passInp").value;
    let university = document.getElementById("userSelectSpacing").value; //check since dropdown:

    document.getElementById("registerRes").innerHTML= "";


    let registerInfo = {
        Email: email,
        First : firstName,
        Last : lastName,
        Username: username,
        Password: password,
        User_Type : userType,
        University_name : university
    };

    console.log("info being sent to backend: " + registerInfo);
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
            document.getElementById("emailInp") = "";
            document.getElementById("firstNameInp") = "";
            document.getElementById("lastNameInp") = "";
            document.getElementById("userSelect") = ""; 
            document.getElementById("userInp") = "";
            document.getElementById("passInp") = "";
            document.getElementById("userSelectSpacing") = ""; 
        }
        else{
            //otherwise automatically log in a user 
            window.location.href = "dashboard.html";
        }

    }catch(error){
        document.getElementById("registerRes").innerHTML= "failed register";
    }

}

//for the university dropdown we will connect to uni locations from db
async function fetchUniversities(){
    try{
        let url = urlBase + 'GetLocations.'+extension;
        console.log("url: " + url);
        const response = await fetch(url,{
            method: 'GET',
            headers: {
                'Accept' : 'application/json'
            }, 
            mode: 'no-cors'
        });

        const data = await response.json();
        const uniSelect = document.getElementById('userSelectSpacing');

        //a default option before user inputs something
        universitySelect.innerHTML='';
        const defaultOption = document.createElement('option');
        defaultOption.textContent= 'Select University';
        defaultOption.value = '';
        universitySelect.appendChild(defaultOption);

        if (data.results && Array.isArray(data.results)){
            data.results.forEach(university => {
                const option = document.getElementById('option');
                option.textContent = university;
                option.value = university;
                universitySelect.appendChild(option);

            })
        } else{
            console.log("no unis found in the response");
        }

    }catch(error){
        console.log("error fetching unis");
    }
}

window.onload = fetchUniversities;