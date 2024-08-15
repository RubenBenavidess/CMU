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
            // Redirige al usuario si el registro fue exitoso
            window.location.href = 'main.html';
        } else {
            // Muestra mensajes de error específicos
            if (data.invalidUser) {
                console.log("Nombre de usuario ya existe.");
                // Aquí puedes mostrar un mensaje en la interfaz de usuario
                // Ejemplo: document.getElementById('userError').innerText = 'Nombre de usuario ya existe.';
            }
            if (data.invalidEmail) {
                console.log("Correo electrónico ya existe.");
                // Aquí puedes mostrar un mensaje en la interfaz de usuario
                // Ejemplo: document.getElementById('emailError').innerText = 'Correo electrónico ya existe.';
            }
            if (data.error) {
                console.log("Error: " + data.error);
                // Aquí puedes mostrar un mensaje genérico en la interfaz de usuario
                // Ejemplo: document.getElementById('generalError').innerText = 'Hubo un problema al registrar. Inténtalo de nuevo más tarde.';
            }
        }
    })
    .catch(error => {
        console.error('Hubo un problema con la operación fetch');
        alert('Hubo un problema al intentar registrar. Inténtalo de nuevo más tarde.');
    });
});


//Configuracion de maxdate
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
const maxDate = `${yyyy}-${mm}-${dd}`;

document.getElementById('bornDate').setAttribute('max', maxDate);