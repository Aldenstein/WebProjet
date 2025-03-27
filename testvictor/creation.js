document.getElementById('add-question').addEventListener('click', function() {
    const questionsContainer = document.getElementById('questions-container');
    const questionCount = questionsContainer.getElementsByClassName('question').length + 1;

    const newQuestion = `
        <div class="question">
            <label for="question-${questionCount}">Question ${questionCount} :</label>
            <input type="text" id="question-${questionCount}" name="questions[]" required>
            <div class="options">
                <label for="option-${questionCount}-1">Option 1 :</label>
                <input type="text" id="option-${questionCount}-1" name="options-${questionCount}[]" required>
                <label for="option-${questionCount}-2">Option 2 :</label>
                <input type="text" id="option-${questionCount}-2" name="options-${questionCount}[]" required>
                <label for="correct-option-${questionCount}">Réponse correcte :</label>
                <select id="correct-option-${questionCount}" name="correct-options[]" required>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                </select>
            </div>
        </div>
    `;

    questionsContainer.insertAdjacentHTML('beforeend', newQuestion);
});
