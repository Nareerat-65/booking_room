<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มขอจองห้องพัก</title>
    <link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        body {
            background: #fbf6f4ff;
            font-family: 'Kanit', sans-serif;
        }

        .navbar {
            font-size: 0.95rem;
            backdrop-filter: blur(12px);
            background-color: #F57B39;
        }

        .nav-link {
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding-inline: 1rem;
        }

        .navbar-brand {
            font-size: 1.9rem;
        }

        .booking-wrapper {
            min-height: 100vh;
        }

        .booking-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .booking-card-header {
            background: linear-gradient(135deg, #0d6efd, #4e9bff);
            color: #fff;
        }

        .booking-card-header h1 {
            font-size: 1.4rem;
            margin-bottom: .25rem;
        }

        .booking-card-header p {
            margin: 0;
            opacity: .9;
            font-size: .9rem;
        }

        .form-section {
            padding: 1.25rem 1.5rem;
            border-radius: 1rem;
            background: #ffffff;
            margin-bottom: 1rem;
            border: 1px solid #eef0f7;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #1f2933;
        }

        .section-title::before {
            content: "";
            width: 4px;
            height: 18px;
            border-radius: 999px;
            background: #0d6efd;
        }

        fieldset {
            border: 0;
            padding: 0;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            font-size: .9rem;
        }

        .text-muted-small {
            font-size: .8rem;
            color: #6c757d;
        }

        .radio-inline-group .form-check {
            padding-left: 0;
        }

        .radio-inline-group .form-check-input {
            margin-right: .35rem;
        }

        .pill-badge {
            font-size: .75rem;
            padding: .15rem .5rem;
            border-radius: 999px;
            background: #e9f2ff;
            color: #1d4ed8;
        }

        .btn-light {
            background-color: #fff;
            border-color: #cfcfcfff;
        }

        .btn-primary {
            border-radius: 999px;
            padding-inline: 1.75rem;
            border: none;
            background-color: #F57B39;
        }

        .btn-primary:hover {
            background-color: #F57B39;
            opacity: 0.9;
        }

        .btn-outline-secondary {
            border-radius: 999px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2" href="#">
                🏨 ระบบจองห้องพัก
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link " href="index.php">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="u_booking.php">จองห้องพัก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">ติดต่อเรา</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <h1 class="h3 mb-4 text-center">แบบฟอร์มขอจองห้องพัก</h1>

        <form id="bookingForm" method="post" action="u_booking_process.php">
            <!-- ชื่อ / เบอร์ / LINE / Email -->
            <div class="mb-3">
                <label for="fullName" class="form-label">ชื่อ–นามสกุล ผู้จองห้องพัก</label>
                <input type="text" class="form-control" id="fullName" name="fullName" required />
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">เบอร์โทรศัพท์มือถือ</label>
                <input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]{9,10}" required>
            </div>

            <div class="mb-3">
                <label for="lineId" class="form-label">ID LINE</label>
                <input type="text" class="form-control" id="lineId" name="lineId" required />
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required />
            </div>

            <!-- ตำแหน่ง -->
            <fieldset class="mb-3">
                <legend class="fs-6">ตำแหน่ง</legend>

                <!-- นักศึกษาแพทย์ชั้นปีที่ ... -->
                <div class="form-check mb-2 d-flex align-items-center gap-2">
                    <input
                        class="form-check-input mt-0"
                        type="radio"
                        name="position"
                        id="positionStudent"
                        required
                        value="student" />
                    <label class="form-check-label me-2" for="positionStudent">
                        นักศึกษา / นิสิตแพทย์ชั้นปีที่
                    </label>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        name="studentYear"
                        placeholder="เช่น 4"
                        style="max-width: 120px;" />
                </div>

                <!-- แพทย์ -->
                <div class="form-check mb-2">
                    <input
                        class="form-check-input mt-0"
                        type="radio"
                        name="position"
                        id="positionDoctor"
                        value="doctor" />
                    <label class="form-check-label me-2" for="positionDoctor">
                        แพทย์ใช้ทุน / แพทย์เพิ่มพูนทักษะ / แพทย์ประจำบ้าน / แพทย์ประจำบ้านต่อยอด
                    </label>
                </div>

                <!-- เจ้าหน้าที่ -->
                <div class="form-check mb-2">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="position"
                        id="positionStaff"
                        value="staff" />
                    <label class="form-check-label" for="positionStaff">
                        เจ้าหน้าที่
                    </label>
                </div>

                <!-- อื่น ๆ -->
                <div class="form-check d-flex align-items-center gap-2">
                    <input
                        class="form-check-input mt-0"
                        type="radio"
                        name="position"
                        id="positionOther"
                        value="other" />
                    <label class="form-check-label me-2" for="positionOther">
                        อื่น ๆ
                    </label>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        name="positionOtherDetail"
                        placeholder="ระบุ"
                        style="max-width: 200px;" />
                </div>
            </fieldset>

            <!-- ชื่อหน่วยงาน / สังกัด -->
            <div class="mb-3">
                <label for="department" class="form-label">ชื่อหน่วยงานต้นสังกัด</label>
                <input
                    class="form-control"
                    list="departmentList"
                    id="department"
                    name="department"
                    required
                    placeholder="พิมพ์เพื่อค้นหา หรือกรอกใหม่" />
                <datalist id="departmentList">
                    <!-- ตัวอย่าง option -->
                    <option value="คณะแพทยศาสตร์">
                    <option value="โรงพยาบาลมหาวิทยาลัย">
                    <option value="ภาควิชาอายุรศาสตร์">
                    <option value="ภาควิชาศัลยศาสตร์">
                    <option value="ภาควิชากุมารเวชศาสตร์">
                </datalist>
            </div>

            <!-- วัตถุประสงค์ -->
            <fieldset class="mb-4">
                <legend class="fs-6">วัตถุประสงค์การเข้าพัก</legend>

                <!-- มาศึกษารายวิชา -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="purpose" id="purposeStudy" value="study" required>
                    <label class="form-check-label fw-semibold" for="purposeStudy">มาศึกษารายวิชา</label>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">ชื่อรายวิชา</label>
                            <input type="text" class="form-control form-control-sm" name="studyCourse" placeholder="กรอกชื่อรายวิชา">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">ภาควิชา</label>
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle w-100" type="button" id="studyDeptDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    เลือกภาควิชา
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="studyDeptDropdown">
                                    <li><a class="dropdown-item" href="#">กุมารเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">จักษุวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">จิตเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">นิติเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">พยาธิวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">รังสีวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">วิสัญญีวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">ศัลยศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">สูติศาสตร์-นรีเวชวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">ออร์โธปิดิกส์</a></li>
                                    <li><a class="dropdown-item" href="#">อายุรศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ครอบครัว</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ชุมชน</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ฟื้นฟู</a></li>
                                    <li><a class="dropdown-item" href="#">โสต ศอ นาสิกวิทยา</a></li>
                                </ul>
                                <input type="hidden" name="studyDept" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ทำ Elective -->
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="purpose" id="purposeElective" value="elective">
                    <label class="form-check-label fw-semibold" for="purposeElective">ทำ Elective ภาควิชา</label>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">ภาควิชา</label>
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle w-100" type="button" id="electiveDeptDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    เลือกภาควิชา
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="electiveDeptDropdown">
                                    <li><a class="dropdown-item" href="#">กุมารเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">จักษุวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">จิตเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">นิติเวชศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">พยาธิวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">รังสีวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">วิสัญญีวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">ศัลยศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">สูติศาสตร์-นรีเวชวิทยา</a></li>
                                    <li><a class="dropdown-item" href="#">ออร์โธปิดิกส์</a></li>
                                    <li><a class="dropdown-item" href="#">อายุรศาสตร์</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ครอบครัว</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ชุมชน</a></li>
                                    <li><a class="dropdown-item" href="#">เวชศาสตร์ฟื้นฟู</a></li>
                                    <li><a class="dropdown-item" href="#">โสต ศอ นาสิกวิทยา</a></li>
                                </ul>
                                <input type="hidden" name="electiveDept" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- วันเข้า / วันออก -->
            <div class="row g-3 mb-3" id="dateRangePicker">
                <div class="col-md-6">
                    <label for="checkInDate" class="form-label">วันที่ย้ายเข้าพัก</label>
                    <input type="text" class="form-control date start" id="checkInDate" name="checkInDate" required placeholder="DD-MM-YYYY" />
                </div>
                <div class="col-md-6">
                    <label for="checkOutDate" class="form-label">วันที่ย้ายออก</label>
                    <input type="text" class="form-control date end" id="checkOutDate" name="checkOutDate" required placeholder="DD-MM-YYYY" />
                </div>
            </div>

            <!-- จำนวนผู้เข้าพัก -->
            <div class="mb-4">
                <label class="form-label">จำนวนผู้เข้าพัก</label>
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4">
                        <label for="womanCount" class="form-label small">ผู้หญิง</label>
                        <input type="number" min="0" class="form-control" id="womanCount" name="womanCount">
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <label for="manCount" class="form-label small">ผู้ชาย</label>
                        <input type="number" min="0" class="form-control" id="manCount" name="manCount">
                    </div>
                </div>
            </div>

            <!-- ปุ่ม -->
            <div class="d-flex justify-content-between">
                <button type="reset" class="btn btn-outline-secondary">ล้างฟอร์ม</button>
                <button type="submit" class="btn btn-primary">ส่งคำขอ</button>
            </div>
        </form>
    </div>

    <!-- Modal: ส่งคำขอสำเร็จ -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">ส่งคำขอสำเร็จ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>ระบบได้รับคำขอจองห้องพักของคุณเรียบร้อยแล้ว</p>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        กรุณารอการติดต่อกลับจากเจ้าหน้าที่เพื่อยืนยันการจอง
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal โหลดสำหรับผู้จอง -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content d-flex flex-column justify-content-center align-items-center p-4">
                <div class="spinner-border text-primary mb-3 mx-auto" role="status"></div>
                <div class="text-center">กำลังส่งข้อมูล...<br>กรุณารอสักครู่</div>
            </div>
        </div>
    </div>



    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datepair.js/0.2.2/jquery.datepair.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // เปลี่ยนข้อความปุ่มตามที่เลือกใน dropdown
        document.querySelectorAll('.dropdown').forEach(drop => {
            const btn = drop.querySelector('.dropdown-toggle');
            const hidden = drop.querySelector('input[type="hidden"]');
            drop.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
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
        }).on('changeDate', function(e) {
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

        bookingForm.addEventListener('submit', function(e) {
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
    </script>
</body>

</html>