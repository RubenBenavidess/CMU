// JavaScript Document
window.addEventListener('load', function() {
    const contentHeight = document.querySelector('.content').offsetHeight;
    const viewportHeight = window.innerHeight;

    const footer = document.getElementById('footer');

    if (contentHeight > viewportHeight) {
        footer.style.position = 'sticky';
        footer.style.bottom = '0';
    } else {
        footer.style.position = 'fixed';
    }
});