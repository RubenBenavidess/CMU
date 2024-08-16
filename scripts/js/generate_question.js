// JavaScript Document
async function generate_questions(inputtedFile){
    try {
        // Crear un objeto FormData
        const formData = new FormData();
        formData.append('file', inputtedFile);

        // Hacer la solicitud a la API
        const response = await fetch('https://textprocess-api.onrender.com/process-file', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        console.log('Response:', data);

        const question = data.question;
        const answer = data.answer;
        const options = data.options;

        // Retornar un objeto con la pregunta, la respuesta y las opciones
        return {
            question: question,
            answer: answer,
            options: options
        };
    } catch (error) {
        console.error('Error:', error);
    }
}