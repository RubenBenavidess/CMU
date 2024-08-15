// JavaScript Document
window.addEventListener('load', function() {
    const contentHeight = document.body.offsetHeight;
    const viewportHeight = window.innerHeight;

    const footer = document.querySelector('footer');

    if (contentHeight >= viewportHeight) {
        footer.style.position = 'relative';
    } else {
        footer.style.position = 'fixed';
    }
});