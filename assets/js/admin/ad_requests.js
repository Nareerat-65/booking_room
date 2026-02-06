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
    let currentDetailId = null;

    function openDetailModalFromRow($tr) {
        const status = ($tr.data('status') || '').toString();
        const reason = ($tr.data('reason') || '').toString();

        const id = $tr.data('id');
        currentDetailId = id;

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

        const $btnDelete = $('#btnDeleteBooking');
        const $btnEdit = $('#btnEditBooking');

        if (status === 'approved') {
            $btnDelete.removeClass('d-none');
            $btnEdit.removeClass('d-none');
        } else {
            $btnDelete.addClass('d-none');
            $btnEdit.addClass('d-none');
        }

        $('#detailsModal').modal('show');
    }

    function updateStatus(id, status, reason = null) {
        // 🔸 Show loading indicator
        SA.loading('กำลังบันทึก...', 'กรุณารอสักครู่');

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

                const detailBtn = `
                    <button class="btn btn-outline-secondary btn-sm btn-detail" data-id="${id}">
                        <i class="fas fa-info-circle"></i> รายละเอียด
                    </button>
                `;
                $tr.find('td').last().html(detailBtn);

                // ถ้าอยากเปิด modal รายละเอียดต่อก็ปล่อยไว้
                openDetailModalFromRow($tr);

                // ✅ แจ้งเตือนสำเร็จ
                SA.success((status === 'approved') ? 'อนุมัติสำเร็จ' : 'บันทึกเหตุผลสำเร็จ', undefined, undefined, {
                    timer: 1500,
                    showConfirmButton: false
                });

            } else if (res === 'no_rooms') {
                SA.warning('ไม่สามารถอนุมัติได้', undefined, undefined, {
                    html: 'จำนวนห้องว่างไม่เพียงพอในช่วงวันที่ผู้ใช้เลือก<br>กรุณาจัดสรรช่วงวันใหม่หรือแจ้งผู้ใช้ปรับวันเข้าพัก'
                });

            } else {
                SA.error('เกิดข้อผิดพลาด', 'ไม่สามารถอัปเดตสถานะได้');
            }
        }).fail(function () {
            SA.error('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ', 'กรุณาลองใหม่อีกครั้ง');
        }).always(function () {
            // ปิดโหลด (ถ้ายังไม่ถูกปิด)
            // ถ้า SA.success มี timer ก็ไม่ต้อง close เพิ่ม
        });
    }

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
            SA.warning('กรุณากรอกเหตุผล');
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

    // ⭐ ปุ่มลบรายการจองใน modal
    $('#btnDeleteBooking').on('click', function () {
        if (!currentDetailId) return;

        SA.confirm(
            'ยืนยันการลบรายการจอง?',
            'เมื่อลบแล้วจะไม่สามารถกู้คืนได้',
            'ลบรายการ',
            'ยกเลิก',
            (isConfirmed) => {
                if (!isConfirmed) return;

                SA.loading('กำลังลบ...');

                $.post('ad_delete_booking.php', { id: currentDetailId }, function (res) {
                    res = (res || '').toString().trim();

                    if (res === 'success') {
                        // ลบแถวออกจากตาราง
                        $(`#bookingsTable tr[data-id="${currentDetailId}"]`).remove();

                        $('#detailsModal').modal('hide');

                        SA.success('ลบรายการจองแล้ว', undefined, undefined, {
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        SA.error('ลบไม่สำเร็จ', 'กรุณาลองใหม่อีกครั้ง');
                    }
                }).fail(function () {
                    SA.error('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ', 'กรุณาลองใหม่อีกครั้ง');
                });
            },
            {
                confirmButtonColor: '#d33'
            }
        );
    });

    // ⭐ ปุ่มแก้ไขข้อมูลใน modal
    $('#btnEditBooking').on('click', function () {
        if (!currentDetailId) return;

        // วิธีง่ายสุด: เด้งไปหน้าฟอร์มแก้ไข
        window.location.href = 'ad_edit_booking.php?id=' + currentDetailId;
    });

});