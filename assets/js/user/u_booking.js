document.addEventListener("DOMContentLoaded", function () {

    const phone = document.getElementById("phone");
    if (phone) {
        phone.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, "").slice(0, 10);
        });
    }

    // เปลี่ยนข้อความปุ่มตามที่เลือกใน dropdown
    document.querySelectorAll('.dropdown').forEach(drop => {
        const btn = drop.querySelector('.dropdown-toggle');
        const hidden = drop.querySelector('input[type="hidden"]');
        drop.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function () {
                btn.textContent = this.textContent;
                if (hidden) hidden.value = this.textContent;
            });
        });
    });

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const minCheckIn = new Date(today);
    minCheckIn.setDate(minCheckIn.getDate() + 14);
    const maxCheckIn = new Date(today);
    maxCheckIn.setDate(maxCheckIn.getDate() + 60);

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
        endDate: maxCheckIn,   
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


    // ===== ส่งฟอร์มแบบ AJAX + SweetAlert2 =====
    $(function () {
        const form = $('#bookingForm');

        form.on('submit', function (e) {
            e.preventDefault(); // กันไม่ให้ submit ปกติ

            // 🔸 เปิด SweetAlert Loading แทน loadingModal
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
                // ปิด loading ก่อน
                Swal.close();

                if (res.status === 'success') {
                    // ล้างฟอร์ม
                    form.trigger('reset');
                    $('#checkInDate').datepicker('update', '');
                    $('#checkOutDate').datepicker('update', '');

                    // 🔸 แสดง SweetAlert Success แทน successModal
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
                <input type="tel" name="guest_phone[]" class="form-control" placeholder="เช่น 0812345678">
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



