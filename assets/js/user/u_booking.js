document.getElementById("phone").addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "")       // กันตัวอักษร
        .slice(0, 10);                // จำกัด 10 ตัว
});

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

// datepicker ช่องวันเข้า
$('#checkInDate').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    startDate: minCheckIn,
    language: 'th',      
    thaiyear: true       
}).on('changeDate', function (e) {
    const start = e.date; // วันที่ย้ายเข้า
    $('#checkOutDate').datepicker('setStartDate', start);

    // ถ้าตอนนี้มีค่าในช่องวันออก แล้ว < วันเข้า ให้ดันขึ้นมาเท่ากับวันเข้า
    const end = $('#checkOutDate').datepicker('getDate');
    if (end && end < start) {
        $('#checkOutDate').datepicker('setDate', start);
    }
});

// datepicker ช่องวันออก
$('#checkOutDate').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    startDate: minCheckIn,
    language: 'th',      
    thaiyear: true  
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