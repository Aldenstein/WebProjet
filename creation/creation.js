document.addEventListener('DOMContentLoaded', function() {
    fetchQuestions();
});

function fetchQuestions() {
    fetch('../fetch_questions.php')
        .then(response => response.json())
        .then(data => {
            const questionsContainer = document.getElementById('questions-container');
            const questionsList = document.createElement('ul');
            data.forEach(question => {
                const listItem = document.createElement('li');
                listItem.textContent = `Q${question.numero_question}: ${question.question}`;
                questionsList.appendChild(listItem);
            });
            questionsContainer.appendChild(questionsList);
        })
        .catch(error => console.error('Error fetching questions:', error));
}