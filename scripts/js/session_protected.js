// JavaScript Document
document.addEventListener('DOMContentLoaded', async () => {
	document.body.style.visibility = 'hidden';
    try {
        const response = await fetch('../scripts/php/get_session_data.php');
        const data = await response.json();
        
        if (data.loggedin) {
            window.location.href = 'main.html';
        }else{
			document.body.style.visibility = 'visible';
		}
    } catch (error) {
        console.error('Error checking session');
    }
});