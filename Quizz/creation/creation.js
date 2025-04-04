// attendre que la page charge
document.addEventListener('DOMContentLoaded', () => {
    // recupere les elements html
    const questionTypeSelect = document.getElementById('question_type');
    const qcmOptions = document.getElementById('qcm-options');
    const trueFalseOptions = document.getElementById('true-false-options');
    const openAnswer = document.getElementById('open-answer');

    // change l'affichage selon le type de question
    questionTypeSelect.addEventListener('change', () => {
        const selectedType = questionTypeSelect.value;
        qcmOptions.style.display = selectedType === 'QCM' ? 'block' : 'none';
        trueFalseOptions.style.display = selectedType === 'Vrai/Faux' ? 'block' : 'none';
        openAnswer.style.display = selectedType === 'Ouverte' ? 'block' : 'none';
    });
});