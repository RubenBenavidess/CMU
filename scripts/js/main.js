window.addEventListener('load', () => {

    //fetch is actually an API. This function allows to make a HTTP Request to the URL specified,
    //responding with the object 'response', same that we use.
    //Also, we can manage the response with .then() and .catch()
    //We use .then() to manage the response with their respective methods as
    //response.json() that converts the response to json and it is stored
    //in the data object, same that we use.

    fetch('../scripts/php/get_resources.php', {
        method: 'GET'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();    
    })                            
    .then(data => {                         
        if (data.success) {

            const resources_container = document.getElementById("resources-container");

            if(data.logged_in){

                // Selecciona el elemento <link> que deseas eliminar
                const link = document.querySelector('link[rel="stylesheet"][href="../css/medias_styles.css"]');

                // Verifica si el elemento existe y luego elimínalo
                if (link) {
                    link.parentNode.removeChild(link);
                }


                //The prev div of the main is hidden to show the user div
                const prev_div = document.querySelector("div[class='non-mobile col-7']");
                prev_div.style.display = "none";

                //Visual Change
                const span_username = document.getElementById('username');
                span_username.textContent = "@" + data.username;

                const new_div = document.querySelector("div[class='logged-in col-7']");
                new_div.style.display = 'block';

                const profile_pic = document.getElementById("profile-img");

                //TODO: SETEO DE LA IMAGEN

                //Logic Change
                const button_log_out = document.getElementById('close-btn');

                button_log_out.removeAttribute('onclick');

                button_log_out.addEventListener('click', function() {
                    
                    console.log("testing");

                    fetch('../scripts/php/close_session.php', {
                        method: 'POST'
                    })
                    .then(response => {
                        if(response.ok){
                            console.log("testing");
                            window.location.href = '../html/main.html';
                        }else{
                            console.log("Hubo un error en la respuesta.");
                        }
                    })
                    .catch(() => {
                        console.log("Hubo un error en la operación fetch.");
                    });
                    
                });




            }else{
                
                const subjects = [];
                
                let div;
                let span;
                let button;

                data.resources.forEach(resource => {

                    div = document.createElement('div');
                    div.className = "button-resource-container";
                    div.style.display = "flex";
                    div.style.justifyContent = "center";
                    div.style.padding = "8px";
                    div.style.minWidth = "20%";

                    for(let subject in resource){

                        subjects.push(resource[subject]);

                        button = document.createElement('button');
                        button.className = "button-resource";
                        button.style.padding = "6px";
                        button.className = "nav-button";
                        button.style.background = "transparent";
                        button.style.border = "none";

                        span = document.createElement("span");
                        span.className = "text";
                        span.textContent = resource[subject];

                        span.style.fontWeight = "500";
                        

                        button.appendChild(span);

                        div.appendChild(button);
                        
                    }

                    resources_container.appendChild(div);

                });

            }

        }
        else{
            if(data.error){
                console.log(data.error_desc);
            }
        }
        
    })
    .catch(() => console.error('Error'));

});