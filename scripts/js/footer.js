// JavaScript Document
function adjustFooterPosition() {
    const contentHeight = document.body.scrollHeight;
    const viewportHeight = window.innerHeight;
    const footer = document.querySelector('footer');
    const footerHeight = footer.offsetHeight; 

    console.log('contenido: ', (contentHeight + footerHeight));
    console.log('viewport: ', viewportHeight);
    if ((contentHeight + footerHeight) >= viewportHeight) {
        footer.style.position = 'absolute';
        footer.style.bottom = 'auto';
    } else {
        footer.style.position = 'fixed';
        footer.style.bottom = '0';
    }
}

window.addEventListener('load', adjustFooterPosition);

window.addEventListener('resize', adjustFooterPosition);

window.addEventListener('orientationchange', adjustFooterPosition);
