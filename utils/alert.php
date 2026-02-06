<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ---- Helper กลาง (เรียกใช้ได้ทุกหน้า) ----
window.SA = {
  // ✅ แจ้งเตือนสำเร็จ
  success: function (title, text, thenFn, options = {}) {
    Swal.fire({
      icon: 'success',
      title: title || 'สำเร็จ',
      text: text || undefined,
      html: options.html || undefined,
      confirmButtonText: options.confirmButtonText || 'ตกลง',
      confirmButtonColor: '#34c5ff',
      timer: options.timer || undefined,
      showConfirmButton: options.showConfirmButton !== false,
      ...options
    }).then(() => { if (typeof thenFn === 'function') thenFn(); });
  },

  // ❌ แจ้งเตือนข้อผิดพลาด
  error: function (title, text, thenFn, options = {}) {
    Swal.fire({
      icon: 'error',
      title: title || 'เกิดข้อผิดพลาด',
      text: text || undefined,
      html: options.html || undefined,
      confirmButtonText: options.confirmButtonText || 'ตกลง',
      confirmButtonColor: '#34c5ff',
      ...options
    }).then(() => { if (typeof thenFn === 'function') thenFn(); });
  },

  // ⚠️ แจ้งเตือนเตือน/คำเตือน
  warning: function (title, text, thenFn, options = {}) {
    Swal.fire({
      icon: 'warning',
      title: title || 'คำเตือน',
      text: text || undefined,
      html: options.html || undefined,
      confirmButtonText: options.confirmButtonText || 'ตกลง',
      confirmButtonColor: '#ffd500',
      ...options
    }).then(() => { if (typeof thenFn === 'function') thenFn(); });
  },

  // ℹ️ แจ้งเตือนข้อมูล/หมายเหตุ
  info: function (title, text, thenFn, options = {}) {
    Swal.fire({
      icon: 'info',
      title: title || 'ข้อมูล',
      text: text || undefined,
      html: options.html || undefined,
      confirmButtonText: options.confirmButtonText || 'ตกลง',
      confirmButtonColor: '#34c5ff',
      ...options
    }).then(() => { if (typeof thenFn === 'function') thenFn(); });
  },

  // ❓ ยืนยันการทำรายการ (มี Cancel button)
  confirm: function (title, text, okText, cancelText, thenFn, options = {}) {
    Swal.fire({
      icon: 'question',
      title: title || 'ยืนยันการทำรายการ',
      text: text || undefined,
      html: options.html || undefined,
      showCancelButton: true,
      confirmButtonText: okText || 'ยืนยัน',
      cancelButtonText: cancelText || 'ยกเลิก',
      confirmButtonColor: '#34c5ff',
      ...options
    }).then((result) => { if (typeof thenFn === 'function') thenFn(result.isConfirmed); });
  },

  // 🔄 แสดง loading spinner
  loading: function (title, text, options = {}) {
    Swal.fire({
      title: title || 'กำลังประมวลผล...',
      text: text || 'กรุณารอสักครู่',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading(),
      ...options
    });
  },

  // 🔒 ปิด loading
  close: function () {
    Swal.close();
  }
};
</script>
