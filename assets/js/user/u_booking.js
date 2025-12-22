document.addEventListener("DOMContentLoaded", function () {
    const positionRadios = document.querySelectorAll('input[name="position"]');
    const studentYear = document.getElementById('studentYear');
    const otherDetail = document.querySelector('input[name="positionOtherDetail"]');

    positionRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            // student
            if (radio.value === 'student') {
                studentYear.disabled = false;
                studentYear.required = true;
            } else {
                studentYear.disabled = true;
                studentYear.required = false;
                studentYear.value = '';
            }

            // other
            if (radio.value === 'other') {
                otherDetail.disabled = false;
                otherDetail.required = true;
            } else {
                otherDetail.disabled = true;
                otherDetail.required = false;
                otherDetail.value = '';
            }
        });
    });

    // ===== Datepicker =====
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const minCheckIn = new Date(today);
    minCheckIn.setDate(minCheckIn.getDate() + 14);
    const maxCheckIn = new Date(today);
    maxCheckIn.setDate(maxCheckIn.getDate() + 74);

    $('#checkInDate').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startDate: minCheckIn,
        endDate: maxCheckIn,
        language: 'th',
        thaiyear: true
    }).on('changeDate', function (e) {
        const start = e.date;
        $('#checkOutDate').datepicker('setStartDate', start);

        const end = $('#checkOutDate').datepicker('getDate');
        if (end && end < start) {
            $('#checkOutDate').datepicker('setDate', start);
        }
    });


    $('#checkOutDate').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startDate: minCheckIn,
        // endDate: maxCheckIn,
        language: 'th',
        thaiyear: true
    });

    $.getJSON('api_get_full_dates.php', function (fullDates) {

        const disabled = fullDates.map(function (d) {
            const parts = d.split('-');
            if (parts.length !== 3) return d;
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        });

        $('#checkInDate').datepicker('setDatesDisabled', disabled);
        $('#checkOutDate').datepicker('setDatesDisabled', disabled);
    });

    // ดัก mousedown บนทุก cell วันที่ แล้วเช็คเองว่าถูก disable มั้ย
    $(document).on('mousedown', '.datepicker-days td.day', function (e) {
        const $td = $(this);

        // ถ้า td นี้ถูกปิดใช้งาน (เช่น disabled จาก setDatesDisabled / ช่วงวันที่นอก range)
        if ($td.hasClass('disabled') || $td.hasClass('disabled-date')) {
            e.preventDefault(); // กันไม่ให้ datepicker ทำงานต่อ

            Swal.fire({
                icon: 'info',
                title: 'ไม่สามารถเลือกวันที่นี้ได้',
                html: `
                ช่วงวันดังกล่าวไม่เปิดให้จองผ่านระบบออนไลน์<br>
                หากต้องการจองในกรณีเร่งด่วนหรือฉุกเฉิน<br>
                กรุณาติดต่อเจ้าหน้าที่เพื่อดำเนินการแทน<br><br>
                <b>เบอร์โทรศัพท์: 0-5596-7847</b>
            `,
                confirmButtonText: 'ตกลง'
            });
        }
    });


    // ===== ส่งฟอร์มแบบ AJAX + SweetAlert2 =====
    $(function () {
        const form = $('#bookingForm');

        form.on('submit', function (e) {
            e.preventDefault(); // ❗ กัน submit ปกติไว้ก่อน

            Swal.fire({
                icon: 'question',
                title: 'ยืนยันการส่งคำขอ',
                html: `
            กรุณาตรวจสอบข้อมูลให้ถูกต้องครบถ้วนก่อนส่ง<br>
            เมื่อส่งแล้วจะไม่สามารถแก้ไขข้อมูลได้ทันที
        `,
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน ส่งคำขอ',
                cancelButtonText: 'กลับไปตรวจสอบ',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: 'กำลังส่งคำขอ...',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'u_booking_process.php',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json'
                }).done(function (res) {
                    Swal.close();

                    if (res.status === 'success') {
                        form.trigger('reset');
                        $('#checkInDate').datepicker('update', '');
                        $('#checkOutDate').datepicker('update', '');

                        Swal.fire({
                            icon: 'success',
                            title: 'ส่งคำขอสำเร็จ',
                            html: `
                        ระบบได้รับคำขอจองห้องพักของคุณเรียบร้อยแล้ว<br>
                        <span style="font-size:0.9rem;color:#666;">
                            กรุณารอการติดต่อกลับจากเจ้าหน้าที่เพื่อยืนยันการจอง
                        </span>
                    `,
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: res.message || 'ไม่สามารถส่งคำขอได้ กรุณาลองใหม่อีกครั้ง'
                        });
                    }
                }).fail(function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง'
                    });
                });
            });
        });
    });

    const womanInput = document.getElementById('womanCount');
    const manInput = document.getElementById('manCount');
    const btnGen = document.getElementById('btnGenerateGuests');
    const btnAdd = document.getElementById('btnAddGuest');
    const container = document.getElementById('guestListContainer');

    if (!container) return;

    function createGuestRow(index, defaultGender) {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 guest-row';

        row.innerHTML = `
            <div class="col-md-5 col-12">
                <label class="form-label small">ชื่อ–นามสกุล ผู้เข้าพัก #${index}</label>
                <input type="text" name="guest_name[]" class="form-control" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small">เพศ</label>
                <select name="guest_gender[]" class="form-select">
                    <option value="">ไม่ระบุ</option>
                    <option value="F">หญิง</option>
                    <option value="M">ชาย</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small">เบอร์โทรศัพท์</label>
                <input type="tel" name="guest_phone[]" class="form-control" placeholder="เช่น 0812345678" maxlength="10">
            </div>
            <div class="col-md-1 col-12 d-flex justify-content-md-center justify-content-start mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-guest">
                    ลบ
                </button>
            </div>
        `;

        // ตั้งค่าค่าเพศเริ่มต้นถ้ามี
        const genderSelect = row.querySelector('select[name="guest_gender[]"]');
        if (defaultGender === 'F' || defaultGender === 'M') {
            genderSelect.value = defaultGender;
        }

        // ปุ่มลบแถว
        const btnRemove = row.querySelector('.btn-remove-guest');
        btnRemove.addEventListener('click', function () {
            row.remove();
            // อัปเดตเลขลำดับใหม่เล็กน้อย (ไม่จำเป็นมาก แต่เพื่อความสวย)
            renumberGuests();
        });

        container.appendChild(row);
    }

    function renumberGuests() {
        const rows = container.querySelectorAll('.guest-row');
        rows.forEach((row, idx) => {
            const label = row.querySelector('label.form-label.small');
            if (label) {
                label.textContent = `ชื่อ–นามสกุล ผู้เข้าพัก #${idx + 1}`;
            }
        });
    }

    // ปุ่ม "สร้างช่องกรอกจากจำนวนด้านบน"
    if (btnGen) {
        btnGen.addEventListener('click', function () {
            const w = parseInt(womanInput.value || '0', 10);
            const m = parseInt(manInput.value || '0', 10);
            const total = (isNaN(w) ? 0 : w) + (isNaN(m) ? 0 : m);

            container.innerHTML = '';

            if (total <= 0) {
                alert('กรุณากรอกจำนวนผู้เข้าพักอย่างน้อย 1 คนก่อน');
                return;
            }

            let index = 1;

            // เติมผู้หญิงก่อนตามจำนวนที่กรอก
            for (let i = 0; i < w; i++) {
                createGuestRow(index++, 'F');
            }
            // ตามด้วยผู้ชาย
            for (let i = 0; i < m; i++) {
                createGuestRow(index++, 'M');
            }

            renumberGuests();
        });
    }

    // ปุ่ม "+ เพิ่มรายชื่อทีละคน"
    if (btnAdd) {
        btnAdd.addEventListener('click', function () {
            const current = container.querySelectorAll('.guest-row').length;
            createGuestRow(current + 1, '');
            renumberGuests();
        });
    }
});



