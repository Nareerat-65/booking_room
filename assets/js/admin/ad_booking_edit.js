function togglePositionFields() {
    const pos = document.getElementById('position').value;
    document.querySelector('.position-student-group').style.display =
        (pos === 'student') ? 'block' : 'none';
    document.querySelector('.position-other-group').style.display =
        (pos === 'other') ? 'block' : 'none';
}

function togglePurposeFields() {
    const purpose = document.getElementById('purpose').value;
    document.querySelector('.purpose-study-group').style.display =
        (purpose === 'study') ? 'block' : 'none';
    document.querySelector('.purpose-elective-group').style.display =
        (purpose === 'elective') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    togglePositionFields();
    togglePurposeFields();

    document.getElementById('position').addEventListener('change', togglePositionFields);
    document.getElementById('purpose').addEventListener('change', togglePurposeFields);
});