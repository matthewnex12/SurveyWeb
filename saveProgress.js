// saveProgress.js

// Function to set a cookie
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Function to convert JavaScript object to JSON and set it as a cookie
function setObjectAsCookie(name, obj, days) {
    setCookie(name, JSON.stringify(obj), days);
}

// Function to save survey progress
function saveSurveyProgress(surveyId) {
    var answers = {};
    document.querySelectorAll('input[type="radio"]:checked').forEach(function(input) {
        answers[input.name] = input.value;
    });
    var progress = {
        surveyId: surveyId,
        answers: answers,
        savedAt: new Date().toISOString() // Add a timestamp to the saved progress
    };
    setObjectAsCookie('surveyProgress_' + surveyId, progress, 7);
    console.log("Progress saved at " + progress.savedAt, progress); // Debugging message
    alert("Progress saved!");
}

// Example usage: Save survey progress when a user clicks a save button
document.getElementById('saveProgressButton').addEventListener('click', function() {
    var surveyId = document.getElementById('surveyId').value;
    saveSurveyProgress(surveyId);
});
