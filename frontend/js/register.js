// JavaScript Document
document.getElementById('formRegister').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    
    fetch('../scripts/php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'main.html';
        } else {
            if (data.invalidUser) {
				document.getElementById('msgInvUser').style.display = 'block';
				setBorderColor("username", "#FF6254");
            }
			else{
				setBorderColor("username", "#88FF57");
			}
			if (data.invalidEmail) {
				document.getElementById('msgInvEmail').style.display = 'block';
				setBorderColor("email", "#FF6254");
			}
			else{
				setBorderColor("email", "#88FF57");
			}
            if (data.error) {
                console.log("Error: " + data.error);
            }
        }
    })
    .catch(error => {
        console.error('Hubo un problema con la operación fetch');
    });
});

document.getElementById('username').addEventListener('input', function(){
	document.getElementById('msgInvUser').style.display = 'none';
	this.style.border = 'none';
});

document.getElementById('email').addEventListener('input', function(){
	document.getElementById('msgInvEmail').style.display = 'none';
	this.style.border = 'none';
});

document.getElementById('password').addEventListener('input', function(){
	document.getElementById('msgInvPwd').style.display = 'none';
	this.style.border = 'none';
});

document.getElementById('password').addEventListener('blur', function(){
	if (this.value.trim() === "") {
        return;
    }
	
	const pattern = new RegExp(this.getAttribute('pattern'));
    
    if (!pattern.test(this.value)) {
		setBorderColor("password", "#FF6254");
        document.getElementById('msgInvPwd').style.display = 'block';
    } else {
        setBorderColor("password", "#88FF57");
        document.getElementById('msgInvPwd').style.display = 'none';
    }
});

document.getElementById('bornDate').addEventListener('input', function(){
	document.getElementById('msgInvBornDate').style.display = 'none';
	this.style.border = 'none';
});

document.getElementById('bornDate').addEventListener('blur', function(){
    if (!validateBornDate(this.value)) {
		setBorderColor("bornDate", "#FF6254");
        document.getElementById('msgInvBornDate').style.display = 'block';
    } else {
        setBorderColor("bornDate", "#88FF57");
        document.getElementById('msgInvBornDate').style.display = 'none';
    }
});

function validateBornDate(inputDate){
	const todayDate = new Date();
	const inputtedDate = new Date(inputDate);
	
	return (todayDate >= inputtedDate);
}

//Configuracion de maxdate
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
const maxDate = `${yyyy}-${mm}-${dd}`;

document.getElementById('bornDate').setAttribute('max', maxDate);