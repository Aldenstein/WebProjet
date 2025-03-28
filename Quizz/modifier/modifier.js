// Charger les quiz depuis le serveur
function loadQuizzes() {
    fetch("modifier.php?action=getQuizzes")
        .then(response => response.json())
        .then(data => {
            const quizList = document.getElementById("quiz-list");
            quizList.innerHTML = ""; // Réinitialiser la liste
            data.forEach(quiz => {
                const quizItem = document.createElement("div");
                quizItem.className = "quiz-item";
                quizItem.innerHTML = `
                    <span class="quiz-title" data-id="${quiz.id}">${quiz.title}</span>
                    <button onclick="deleteQuiz(${quiz.id})">Supprimer</button>
                `;
                quizItem.querySelector(".quiz-title").addEventListener("click", () => loadQuestions(quiz.id, quiz.title));
                quizList.appendChild(quizItem);
            });
        });
}

// Supprimer un quiz
function deleteQuiz(quizId) {
    if (confirm("Voulez-vous vraiment supprimer ce quiz ?")) {
        fetch(`modifier.php?action=deleteQuiz&id=${quizId}`, { method: "GET" })
            .then(() => loadQuizzes());
    }
}

// Charger les questions d'un quiz
function loadQuestions(quizId, quizTitle) {
    fetch(`modifier.php?action=getQuestions&quizId=${quizId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("quiz-title").innerText = `Questions du Quiz : ${quizTitle}`;
            const questionList = document.getElementById("question-list");
            questionList.innerHTML = ""; // Réinitialiser la liste
            data.forEach(question => {
                const questionItem = document.createElement("div");
                questionItem.className = "question-item";
                questionItem.innerHTML = `
                    <input type="text" value="${question.text}" data-id="${question.id}" />
                    <button onclick="deleteQuestion(${question.id})">Supprimer</button>
                `;
                questionList.appendChild(questionItem);
            });
            document.getElementById("question-editor").style.display = "block";
        });
}

// Supprimer une question
function deleteQuestion(questionId) {
    if (confirm("Voulez-vous vraiment supprimer cette question ?")) {
        fetch(`modifier.php?action=deleteQuestion&id=${questionId}`, { method: "GET" })
            .then(() => {
                const questionItem = document.querySelector(`input[data-id="${questionId}"]`).parentElement;
                questionItem.remove();
            });
    }
}

// Sauvegarder les modifications des questions
document.getElementById("save-changes").addEventListener("click", () => {
    const questions = Array.from(document.querySelectorAll("#question-list input")).map(input => ({
        id: input.dataset.id,
        text: input.value
    }));
    fetch("modifier.php?action=saveQuestions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(questions)
    }).then(() => alert("Modifications sauvegardées avec succès !"));
});

// Charger les quiz au chargement de la page
document.addEventListener("DOMContentLoaded", loadQuizzes);