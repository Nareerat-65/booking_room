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

// datepicker ช่องวันเข้า
$('#checkInDate').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    startDate: today
}).on('changeDate', function (e) {
    const start = e.date; // วันที่ย้ายเข้า

    // อัปเดตให้วันออกเลือกได้ไม่น้อยกว่าวันเข้า
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
    startDate: today
});

//ส่งฟอร์มแบบ AJAX
const bookingForm = document.getElementById('bookingForm');

bookingForm.addEventListener('submit', function (e) {
    e.preventDefault();

    $('#loadingModal').modal('show'); // แสดง modal โหลด
    const formData = new FormData(bookingForm); // สร้าง FormData จากฟอร์ม

    fetch('u_booking_process.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.text())
        .then(text => {
            $('#loadingModal').modal('hide'); // ซ่อน modal โหลด

            // ถ้า u_booking_process.php ส่ง echo "OK" ตอนทำสำเร็จ
            if (text.trim() === 'OK') {
                // เคลียร์ฟอร์ม
                bookingForm.reset();
                $('#checkInDate').datepicker('update', '');
                $('#checkOutDate').datepicker('update', '');

                // แสดง modal success
                const modalEl = document.getElementById('successModal');
                const successModal = new bootstrap.Modal(modalEl);
                successModal.show();
            } else {
                // ถ้าฝั่ง PHP ส่ง error text กลับมา
                alert('เกิดข้อผิดพลาด: ' + text);
            }
        })
        .catch(err => {
            $('#loadingModal').modal('hide'); // ปิดตอน error ด้วย
            console.error(err);
            alert('ไม่สามารถส่งคำขอได้ กรุณาลองใหม่อีกครั้ง');
        });
});