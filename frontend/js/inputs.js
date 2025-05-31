// JavaScript Document
function setBorderColor(inputId, color) {
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
		inputElement.style.border = color + ' solid 3px';
    }
}