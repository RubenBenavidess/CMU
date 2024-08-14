// JavaScript Document
document.getElementById('formLogin').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
	
    fetch('../scripts/php/login.php', {
        method: 'POST',
        body: formData
    })
	
    .then(response => response.json())
    .then(data => {
		if(!data.success){
			window.location.href = 'errorBDD.html';
		}
		
        if (data.errorUsuario) {
            document.getElementById('msgInvUser').style.display = 'block';
        }
        else if (data.errorPassword) {
            document.getElementById('msjInvPwd').style.display = 'block';
        }
		else {
            window.location.href = 'main.html';
        }
    });
});

document.getElementById('usuario').addEventListener('input', function(){
	document.getElementById('msj_usr_inv').style.display = 'none';
});

document.getElementById('password').addEventListener('input', function(){
	document.getElementById('msj_pwd_inv').style.display = 'none';
});