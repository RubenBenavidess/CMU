let isClicked = false;
let isUserClicked = false;

function display_drop_menu(){
    
    const my_drop_container = document.getElementById("mobile-container");
    const my_submenu_container = document.getElementById("mobile-menu-container");
    const my_button = document.getElementById("drop-button");

    if(!isClicked){

        my_drop_container.style.position = 'absolute';
        my_drop_container.style.backgroundColor = '#34495E';
        my_submenu_container.style.display = 'flex';
        my_button.style.marginBottom = "40px";
        isClicked = true;

    }else{  
        my_drop_container.style.position = 'relative';
        my_submenu_container.style.display = 'none';
        my_drop_container.style.backgroundColor = 'transparent';
        my_button.style.marginBottom = "0";
        isClicked = false;
    }
    
}

function display_user_drop_menu(){

    const my_drop_container = document.querySelector("div[class='logged-in col-7']");
    const my_submenu_container = document.getElementById("logged-in-submenu");
    const my_button = document.getElementById("button-profile-img");

    const ul_adjusted = document.querySelector(".logged-in #nav-right-ul");

    if(!isClicked){

        my_drop_container.style.position = 'absolute';
        my_drop_container.style.backgroundColor = '#34495E';
        
        //Pendiente: Es mejor hacer un media query
        if(window.innerWidth <= 1250){
            my_drop_container.style.width = "40%";
        }else{
            my_drop_container.style.width = "15%";
        }
        

        my_submenu_container.style.display = 'flex';
        my_submenu_container.style.justifyContent = 'center';
        my_button.style.marginBottom = "40px";
        ul_adjusted.style.flexDirection = "column";
        ul_adjusted.style.justifyContent = "flex-start";
        isClicked = true;

    }else{  
        my_drop_container.style.position = 'relative';
        my_submenu_container.style.display = 'none';
        my_drop_container.style.width = "100%";
        my_drop_container.style.backgroundColor = 'transparent';
        my_button.style.marginBottom = "0";
        ul_adjusted.style.flexDirection = "row";
        ul_adjusted.style.justifyContent = "right";
        isClicked = false;
    }

}