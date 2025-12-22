function togglePositionFields() {
    const pos = document.getElementById('position').value;
    document.querySelector('.position-student-group').style.display =
        (pos === 'student') ? 'block' : 'none';
    document.querySelector('.position-other-group').style.display =
        (pos === 'other') ? 'block' : 'none';
}

function togglePurposeFields() {
    const purpose = document.getElementById('purpose')?.value || '';

    const studyGroups    = document.querySelectorAll('.purpose-study-group');
    const electiveGroups = document.querySelectorAll('.purpose-elective-group');

    // ซ่อนทั้งหมดก่อน
    studyGroups.forEach(el => el.style.display = 'none');
    electiveGroups.forEach(el => el.style.display = 'none');

    // แสดงตามวัตถุประสงค์
    if (purpose === 'study') {
        studyGroups.forEach(el => el.style.display = 'block');
    }

    if (purpose === 'elective') {
        electiveGroups.forEach(el => el.style.display = 'block');
    }
}


document.addEventListener('DOMContentLoaded', function () {
    togglePositionFields();
    togglePurposeFields();

    document.getElementById('position').addEventListener('change', togglePositionFields);
    document.getElementById('purpose').addEventListener('change', togglePurposeFields);
});