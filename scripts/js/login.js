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
		if (data.success) {
            window.location.href = 'main.html';
        } else {
            if (data.invalidUser) {
				document.getElementById('msgInvUser').style.display = 'block';
				setBorderColor("user", "#FF6254");
            }
			else{
				setBorderColor("user", "#88FF57");
				if (data.invalidPassword) {
					document.getElementById('msgInvPwd').style.display = 'block';
					setBorderColor("password", "#FF6254");
				}
				else{
					setBorderColor("password", "#88FF57");
				}
			}
            if (data.error) {
                console.log("Error: " + data.error);
            }
        }
    }).catch(error => {
		 console.error('Error');
	});
});

document.getElementById('user').addEventListener('input', function(){
	document.getElementById('msgInvUser').style.display = 'none';
	this.style.border = 'none';
});

document.getElementById('password').addEventListener('input', function(){
	document.getElementById('msgInvPwd').style.display = 'none';
	this.style.border = 'none';
});