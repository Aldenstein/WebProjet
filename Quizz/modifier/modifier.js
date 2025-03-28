// Charger les quiz depuis le serveur
function loadQuizzes() {
    fetch("modifier.php?action=getQuizzes")
        .then(response => response.text())
        .then(html => {
            const quizList = document.getElementById("quiz-list");
            quizList.innerHTML = html; // Injecter le HTML directement
            const quizTitles = quizList.querySelectorAll(".quiz-title");
            quizTitles.forEach(title => {
                title.addEventListener("click", () => {
                    const quizId = title.dataset.id;
                    const quizTitle = title.textContent;
                    loadQuestions(quizId, quizTitle);
                });
            });
        })
        .catch(error => console.error("Erreur lors du chargement des quizzes :", error));
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
        .then(response => response.text())
        .then(html => {
            document.getElementById("quiz-title").innerText = `Questions du Quiz : ${quizTitle}`;
            const questionList = document.getElementById("question-list");
            questionList.innerHTML = html; // Injecter le HTML directement
            document.getElementById("question-editor").style.display = "block";
        })
        .catch(error => console.error("Erreur lors du chargement des questions :", error));
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
document.getElementById("question-form").addEventListener("submit", event => {
    event.preventDefault();
    const questions = Array.from(document.querySelectorAll(".question-item")).map(item => {
        const id = item.querySelector(".question-text").dataset.id;
        const text = item.querySelector(".question-text").value;
        const type = item.dataset.type;

        const questionData = { id, text, type };

        if (type === "QCM") {
            questionData.option1 = item.querySelector('[data-option="1"]').value;
            questionData.option2 = item.querySelector('[data-option="2"]').value;
            questionData.option3 = item.querySelector('[data-option="3"]').value;
            questionData.correct_option = item.querySelector(".correct-option").value;
        } else if (type === "Ouverte") {
            questionData.formatted_answer = item.querySelector(".formatted-answer").value;
        }

        return questionData;
    });

    fetch("modifier.php?action=saveQuestions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(questions)
    }).then(() => alert("Modifications sauvegardées avec succès !"));
});

// Charger les quizzes au chargement de la page
document.addEventListener("DOMContentLoaded", loadQuizzes);