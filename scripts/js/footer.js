// JavaScript Document
function adjustFooterPosition() {
    const contentHeight = document.body.scrollHeight;
    const viewportHeight = window.innerHeight;
    const footer = document.querySelector('footer');
    const footerHeight = footer.offsetHeight; 

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
