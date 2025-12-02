<?php
$pageTitle = 'แบบฟอร์มขอจองห้องพัก - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_booking.css">';
$activeMenu = 'booking';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/user/head_user.php'; ?>
</head>

<body>
    <!-- navbar -->
    <?php include '../partials/user/nav_user.php'; ?>

    <!-- ฟอร์มจองห้องพัก -->
    <div class="container py-5">
        <h2 class="h3 mb-4 text-center">แบบฟอร์มขอจองห้องพัก</h2>
        <div class="bookingForm">
            <form id="bookingForm" method="post" action="u_booking_process.php">
                <!-- ชื่อ-นามสกุล, เบอร์, ID LINE, Email -->
                <div class="mb-3">
                    <label for="fullName" class="form-label required">ชื่อ–นามสกุล ผู้จองห้องพัก</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" required />
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label required">เบอร์โทรศัพท์มือถือ</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        maxlength="10" inputmode="numeric" pattern="[0-9]{10}" required>
                </div>

                <div class="mb-3">
                    <label for="lineId" class="form-label required">ID LINE</label>
                    <input type="text" class="form-control" id="lineId" name="lineId" required />
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required />
                </div>

                <!-- ตำแหน่ง -->
                <fieldset class="mb-3">
                    <legend class="fs-6 required">ตำแหน่ง</legend>

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

                    <div class="form-check mb-2">
                        <input
                            class="form-check-input mt-0"
                            type="radio"
                            name="position"
                            id="positionIntern"
                            value="intern" />
                        <label class="form-check-label me-2" for="positionIntern">
                            แพทย์ใช้ทุน
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input
                            class="form-check-input mt-0"
                            type="radio"
                            name="position"
                            id="positionResident"
                            value="resident" />
                        <label class="form-check-label me-2" for="positionResident">
                            แพทย์ประจำบ้าน
                        </label>
                    </div>

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
                <!-- หน่วยงานต้นสังกัด -->
                <div class="mb-3">
                    <label for="department" class="form-label required">ชื่อหน่วยงานต้นสังกัด</label>
                    <input
                        class="form-control"
                        list="departmentList"
                        id="department"
                        name="department"
                        required
                        placeholder="พิมพ์เพื่อค้นหา หรือกรอกใหม่" />
                    <datalist id="departmentList">
                        <option value="คณะแพทยศาสตร์">
                        <option value="โรงพยาบาลมหาวิทยาลัย">
                        <option value="ภาควิชาอายุรศาสตร์">
                        <option value="ภาควิชาศัลยศาสตร์">
                        <option value="ภาควิชากุมารเวชศาสตร์">
                    </datalist>
                </div>
                <!-- วัตถุประสงค์ -->
                <fieldset class="mb-4">
                    <legend class="fs-6 required">วัตถุประสงค์การเข้าพัก</legend>

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
                <!-- ช่วงวันที่เข้าพัก -->
                <div class="row g-3 mb-3" id="dateRangePicker">
                    <div class="col-md-6">
                        <label for="checkInDate" class="form-label required">วันที่ย้ายเข้าพัก</label>
                        <input type="text" class="form-control date start" id="checkInDate" name="checkInDate" required placeholder="วว-ดด-ปปปป" />
                    </div>
                    <div class="col-md-6">
                        <label for="checkOutDate" class="form-label required">วันที่ย้ายออก</label>
                        <input type="text" class="form-control date end" id="checkOutDate" name="checkOutDate" required placeholder="วว-ดด-ปปปป" />
                    </div>
                </div>
                <!-- จำนวนผู้เข้าพัก -->
                <div class="mb-4">
                    <label class="form-label required">จำนวนผู้เข้าพัก</label>
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
                <div class="d-flex justify-content-between">
                    <button type="reset" class="btn btn-outline-secondary">ล้างฟอร์ม</button>
                    <button type="submit" class="btn btn-primary">ส่งคำขอ</button>
                </div>
            </form>
        </div>

    </div>

    <?php include '../partials/user/footer_user.php'; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datepair.js/0.2.2/jquery.datepair.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="/assets/js/user/u_booking.js"></script>
</body>

</html>