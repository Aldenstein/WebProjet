document.getElementById('add-question').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('question-1').value = '';
    document.getElementById('option-1-1').value = '';
    document.getElementById('option-1-2').value = '';
    document.getElementById('option-1-3').value = '';
    document.getElementById('correct-option-1').value = '';
});