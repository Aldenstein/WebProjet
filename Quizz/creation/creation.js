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

// Fonction pour gérer la soumission du formulaire sans rechargement
function submitQuestion() {
    const form = document.getElementById("quizz-form");
    const formData = new FormData(form);

    fetch("creation.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.text())
        .then((data) => {
            console.log(data); // Affiche la réponse du serveur dans la console
            clearFields(); // Vider les champs après la soumission
            alert("Question ajoutée avec succès !");
        })
        .catch((error) => {
            console.error("Erreur :", error);
        });
}

// Ajouter un écouteur d'événement pour le bouton "Ajouter la Question"
document.getElementById("add-question").addEventListener("click", submitQuestion);