function togglePositionFields() {
    const posEl = document.getElementById('position');
    if (!posEl) return;

    const pos = posEl.value;

    const studentGroup = document.querySelector('.position-student-group');
    const otherGroup = document.querySelector('.position-other-group');

    if (studentGroup) studentGroup.style.display = (pos === 'student') ? 'block' : 'none';
    if (otherGroup) otherGroup.style.display = (pos === 'other') ? 'block' : 'none';
}


function togglePurposeFields() {
    const purposeEl = document.getElementById('purpose');
    if (!purposeEl) return;

    const purpose = purposeEl.value || '';

    const studyGroups = document.querySelectorAll('.purpose-study-group');
    const electiveGroups = document.querySelectorAll('.purpose-elective-group');


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


function makeGuestRow() {
    const tr = document.createElement('tr');
    tr.className = 'guest-row';
    tr.innerHTML = `
    <td class="text-center text-muted fw-semibold guest-no"></td>
    <td>
      <input type="text" name="guest_name[]" class="form-control form-control-sm" placeholder="ชื่อผู้เข้าพัก">
    </td>
    <td>
      <select name="guest_gender[]" class="form-select form-select-sm">
        <option value="">-</option>
        <option value="F">หญิง</option>
        <option value="M">ชาย</option>
      </select>
    </td>
    <td>
      <input type="text" name="guest_phone[]" class="form-control form-control-sm" placeholder="เบอร์ (ถ้ามี)">
    </td>
    <td class="text-end">
      <button type="button" class="btn btn-sm btn-outline-danger btnRemoveGuest" title="ลบรายชื่อ">
        <i class="fas fa-trash-alt"></i>
      </button>
    </td>
  `;
    return tr;
}

function renumberGuestRows(tbody) {
    const row = tbody.querySelectorAll('tr.guest-row');
    let n = 1;
    row.forEach(r => {
        const noCell = r.querySelector('td.guest-no');
        if (noCell) noCell.textContent = n++;
    })
}

document.addEventListener('DOMContentLoaded', function () {
    togglePositionFields();
    togglePurposeFields();

    const positionEl = document.getElementById('position');
    const purposeEl = document.getElementById('purpose');
    if (positionEl) positionEl.addEventListener('change', togglePositionFields);
    if (purposeEl) purposeEl.addEventListener('change', togglePurposeFields);

    // ---- Guests ----
    const btnAddGuest = document.getElementById('btnAddGuest');
    const guestTable = document.getElementById('guestTable');
    if (!btnAddGuest || !guestTable) return;

    const tbody = guestTable.querySelector('tbody');

    // เลขเริ่มต้น (กรณีมีข้อมูลเดิม)
    renumberGuestRows(tbody);

    btnAddGuest.addEventListener('click', () => {
        tbody.appendChild(makeGuestRow());
        renumberGuestRows(tbody);
    });

    guestTable.addEventListener('click', (e) => {
        const btn = e.target.closest('.btnRemoveGuest');
        if (!btn) return;

        const row = btn.closest('tr');
        row.remove();

        if (tbody.querySelectorAll('tr.guest-row').length === 0) {
            tbody.appendChild(makeGuestRow());
        }

        renumberGuestRows(tbody);
    });
});

