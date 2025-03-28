// Fonction pour afficher dynamiquement les champs en fonction du type de question
function updateQuestionFields() {
    const questionType = document.getElementById("question_type").value;
    document.getElementById("qcm-options").style.display = questionType === "QCM" ? "block" : "none";
    document.getElementById("true-false-options").style.display = questionType === "Vrai/Faux" ? "block" : "none";
    document.getElementById("open-answer").style.display = questionType === "Ouverte" ? "block" : "none";
}

// Ajouter un écouteur d'événement pour le champ "question_type"
document.getElementById("question_type").addEventListener("change", updateQuestionFields);

// Fonction pour vider les champs après l'ajout d'une question
function clearFields() {
    document.getElementById("question").value = "";
    document.getElementById("option1").value = "";
    document.getElementById("option2").value = "";
    document.getElementById("option3").value = "";
    document.getElementById("correct_option").value = "";
    document.getElementById("formatted_answer").value = "";
}

// Ajouter un écouteur d'événement pour vider les champs après soumission
document.getElementById("quizz-form").addEventListener("submit", function (event) {
    clearFields();
});