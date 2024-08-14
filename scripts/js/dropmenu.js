let isClicked = false;

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