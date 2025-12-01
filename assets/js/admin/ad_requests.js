$(function () {
    $('#bookingsTable').DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
            info: "แสดง _START_–_END_ จากทั้งหมด _TOTAL_ รายการ",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        },
        pageLength: 10,
        order: []
    });

    // ===== logic เดิม =====

    let selectedId = null;

    // อนุมัติ
    $('#bookingsTable').on('click', '.btn-approve', function () {
        const $tr = $(this).closest('tr');
        const id = $tr.data('id');
        updateStatus(id, 'approved');
    });

    // ไม่อนุมัติ — เปิด modal เก็บเหตุผล
    $('#bookingsTable').on('click', '.btn-reject', function () {
        selectedId = $(this).closest('tr').data('id');
        $('#rejectModal').modal('show');
    });

    $('#rejectForm').on('submit', function (e) {
        e.preventDefault();
        const reason = $('#reason').val().trim();
        if (!reason) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกเหตุผล',
            });
            return;
        }

        $('#rejectModal').modal('hide');

        updateStatus(selectedId, 'rejected', reason);
        $('#reason').val('');
    });

    // รายละเอียด
    $('#bookingsTable').on('click', '.btn-detail', function () {
        const $tr = $(this).closest('tr');
        openDetailModalFromRow($tr);
    });

    // อัปโหลดเอกสาร (เฉพาะรายการที่อนุมัติแล้ว)
    $('#bookingsTable').on('click', '.btn-upload-doc', function () {
        const $tr = $(this).closest('tr');
        const id = $tr.data('id');
        const name = $tr.find('td').eq(1).text().trim(); // คอลัมน์ชื่อผู้จอง

        $('#uploadBookingId').val(id);
        $('#uploadBookingInfo').text(`คำขอ #${id} - ${name}`);

        $('#uploadDocModal').modal('show');
    });

    // ดูเอกสาร
    $('#bookingsTable').on('click', '.btn-view-doc', function () {
        const doc = $(this).data('doc');  // เช่น uploads/documents/booking_5_....
        if (!doc) return;

        // ปรับ path ถ้าหน้าปัจจุบันอยู่ใน admin/
        const url = '../' + doc;

        $('#docFrame').attr('src', url);
        $('#docDownload').attr('href', url);

        $('#viewDocModal').modal('show');
    });



    function updateStatus(id, status, reason = null) {
        // 🔸 SweetAlert2 Loading
        Swal.fire({
            title: 'กำลังบันทึก...',
            text: 'กรุณารอสักครู่',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('ad_updateStatus.php', {
            id,
            status,
            reason
        }, function (res) {
            if (res === 'success') {
                const $tr = $(`#bookingsTable tr[data-id="${id}"]`);
                const $statusCell = $tr.find('td').eq(12);

                if (status === 'approved') {
                    $statusCell.html('<span class="badge bg-success">อนุมัติแล้ว</span>');
                } else if (status === 'rejected') {
                    $statusCell.html('<span class="badge bg-danger">ไม่อนุมัติ</span>');
                } else {
                    $statusCell.html('<span class="badge bg-warning text-dark">รออนุมัติ</span>');
                }

                $tr.attr('data-status', status);
                if (reason !== null) $tr.attr('data-reason', reason);

                const $actionCell = $tr.find('td').last();

                if (status === 'approved') {
                    $actionCell.html(`
                    <button class="btn btn-success btn-sm btn-upload-doc" data-id="${id}">
                        <i class="fas fa-upload"></i> อัปโหลด
                    </button>
                `);
                } else if (status === 'rejected') {
                    $actionCell.html(`
                    <button class="btn btn-outline-secondary btn-sm btn-detail" data-id="${id}">
                        <i class="fas fa-info-circle"></i> รายละเอียด
                    </button>
                `);
                } else {
                    $actionCell.html(`
                    <button class="btn btn-outline-secondary btn-sm btn-detail" data-id="${id}">
                        <i class="fas fa-info-circle"></i> รายละเอียด
                    </button>
                `);
                }

                // ถ้าอยากเปิด modal รายละเอียดต่อก็ปล่อยไว้
                openDetailModalFromRow($tr);

                // ✅ แจ้งเตือนสำเร็จ
                Swal.fire({
                    icon: 'success',
                    title: (status === 'approved') ? 'อนุมัติสำเร็จ' : 'บันทึกเหตุผลสำเร็จ',
                    timer: 1500,
                    showConfirmButton: false
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถอัปเดตสถานะได้'
                });
            }
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ',
                text: 'กรุณาลองใหม่อีกครั้ง'
            });
        }).always(function () {
            // ปิดโหลด (ถ้ายังไม่ถูกปิด)
            // ถ้า Swl.fire success มี timer ก็ไม่ต้อง close เพิ่ม
        });
    }

    function openDetailModalFromRow($tr) {
        const status = ($tr.data('status') || '').toString();
        const reason = ($tr.data('reason') || '').toString();

        const name = $tr.find('td').eq(1).text().trim();
        const inDate = $tr.find('td').eq(9).text().trim();
        const outDate = $tr.find('td').eq(10).text().trim();
        const ppl = $tr.find('td').eq(11).text().trim();

        const $header = $('#detailHeader');
        $header.removeClass('bg-success bg-danger bg-secondary');

        let title = 'รายละเอียดคำขอ';
        if (status === 'approved') {
            $header.addClass('bg-success');
            title = 'รายละเอียดคำขอ (อนุมัติแล้ว)';
        } else if (status === 'rejected') {
            $header.addClass('bg-danger');
            title = 'รายละเอียดคำขอ (ไม่อนุมัติ)';
        } else {
            $header.addClass('bg-secondary');
        }
        $('#detailTitle').text(title);

        let html = `
                <div class="mb-2"><b>ชื่อผู้จอง:</b> ${name}</div>
                <div class="mb-2"><b>วันที่เข้าพัก:</b> ${inDate}</div>
                <div class="mb-2"><b>วันที่ออก:</b> ${outDate}</div>
                <div class="mb-2"><b>จำนวนคน:</b> ${ppl}</div>
            `;
        if (status === 'rejected') {
            html += `<div class="alert alert-danger mt-3"><b>เหตุผลที่ไม่อนุมัติ:</b> ${reason || '—'}</div>`;
        }

        $('#detailBody').html(html);
        $('#detailsModal').modal('show');
    }
});