<?php
require_once dirname(__DIR__, 1) . '/config.php';
$pageTitle = 'แบบฟอร์มขอจองห้องพัก - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_booking.css">';
$activeMenu = 'booking';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/user/head_user.php'; ?>
</head>

<body>
    <?php include_once PARTIALS_PATH . '/user/nav_user.php'; ?>
    <div class="container py-5">
        <h2 class="h3 mb-4 text-center">แบบฟอร์มขอจองห้องพัก</h2>
        <div class="bookingForm">
            <form id="bookingForm" method="post" action="u_booking_process.php">
                <div class="mb-3">
                    <label for="fullName" class="form-label required">ชื่อ–นามสกุล ผู้จองห้องพัก</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" required />
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label required">เบอร์โทรศัพท์มือถือ</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        maxlength="10" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required>
                </div>

                <div class="mb-3">
                    <label for="lineId" class="form-label required">ID LINE</label>
                    <input type="text" class="form-control" id="lineId" name="lineId" required />
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required />
                </div>

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
                        <select name="studentYear" id="studentYear" class="form-select form-select-sm" style="max-width: 100px;">
                            <option value="">เลือกชั้นปี</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
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
                            style="max-width: 200px;"
                            disabled />
                    </div>
                </fieldset>
                
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
                        <option value="ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลขอนแก่น">
                        <option value="ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลหาดใหญ่">
                        <option value="ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลสระบุรี">
                        <option value="ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลแพร่">
                    </datalist>
                </div>
                
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
                                <select name="study_dept" id="study_dept" class="form-select">
                                    <option value="">เลือกภาควิชา</option>
                                    <option value="กุมารเวชศาสตร์">กุมารเวชศาสตร์</option>
                                    <option value="จักษุวิทยา">จักษุวิทยา</option>
                                    <option value="จิตเวชศาสตร์">จิตเวชศาสตร์</option>
                                    <option value="นิติเวชศาสตร์">นิติเวชศาสตร์</option>
                                    <option value="พยาธิวิทยา">พยาธิวิทยา</option>
                                    <option value="รังสีวิทยา">รังสีวิทยา</option>
                                    <option value="วิสัญญีวิทยา">วิสัญญีวิทยา</option>
                                    <option value="ศัลยศาสตร์">ศัลยศาสตร์</option>
                                    <option value="สูติศาสตร์-นรีเวชวิทยา">สูติศาสตร์-นรีเวชวิทยา</option>
                                    <option value="ออร์โธปิดิกส์">ออร์โธปิดิกส์</option>
                                    <option value="อายุรศาสตร์">อายุรศาสตร์</option>
                                    <option value="เวชศาสตร์ครอบครัว">เวชศาสตร์ครอบครัว</option>
                                    <option value="เวชศาสตร์ชุมชน">เวชศาสตร์ชุมชน</option>
                                    <option value="เวชศาสตร์ฟื้นฟู">เวชศาสตร์ฟื้นฟู</option>
                                    <option value="โสต ศอ นาสิกวิทยา">โสต ศอ นาสิกวิทยา</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="purpose" id="purposeElective" value="elective">
                        <label class="form-check-label fw-semibold" for="purposeElective">ทำ Elective ภาควิชา</label>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">ภาควิชา</label>
                                <select name="elective_dept" id="elective_dept" class="form-select">
                                    <option value="">เลือกภาควิชา</option>
                                    <option value="กุมารเวชศาสตร์">กุมารเวชศาสตร์</option>
                                    <option value="จักษุวิทยา">จักษุวิทยา</option>
                                    <option value="จิตเวชศาสตร์">จิตเวชศาสตร์</option>
                                    <option value="นิติเวชศาสตร์">นิติเวชศาสตร์</option>
                                    <option value="พยาธิวิทยา">พยาธิวิทยา</option>
                                    <option value="รังสีวิทยา">รังสีวิทยา</option>
                                    <option value="วิสัญญีวิทยา">วิสัญญีวิทยา</option>
                                    <option value="ศัลยศาสตร์">ศัลยศาสตร์</option>
                                    <option value="สูติศาสตร์-นรีเวชวิทยา">สูติศาสตร์-นรีเวชวิทยา</option>
                                    <option value="ออร์โธปิดิกส์">ออร์โธปิดิกส์</option>
                                    <option value="อายุรศาสตร์">อายุรศาสตร์</option>
                                    <option value="เวชศาสตร์ครอบครัว">เวชศาสตร์ครอบครัว</option>
                                    <option value="เวชศาสตร์ชุมชน">เวชศาสตร์ชุมชน</option>
                                    <option value="เวชศาสตร์ฟื้นฟู">เวชศาสตร์ฟื้นฟู</option>
                                    <option value="โสต ศอ นาสิกวิทยา">โสต ศอ นาสิกวิทยา</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
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

                <div class="mb-4">
                    <label class="form-label required">รายชื่อผู้เข้าพัก</label>
                    <p class="text-muted small mb-2">
                        กรุณากรอกรายชื่อผู้ที่จะเข้าพักทุกคนให้ครบถ้วน
                        คุณสามารถกดปุ่ม “สร้างช่องกรอกจากจำนวน” เพื่อให้ระบบสร้างช่องตามจำนวนผู้เข้าพักด้านบนอัตโนมัติ
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnGenerateGuests">
                            สร้างช่องกรอกจากจำนวนด้านบน
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAddGuest">
                            + เพิ่มรายชื่อทีละคน
                        </button>
                    </div>

                    <div id="guestListContainer"></div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="reset" class="btn btn-outline-secondary">ล้างฟอร์ม</button>
                    <button type="submit" class="btn btn-primary">ส่งคำขอ</button>
                </div>
            </form>
        </div>

    </div>

    <?php include_once PARTIALS_PATH . '/user/footer_user.php'; ?>

    <?php include_once PARTIALS_PATH . '/user/script_user.php'; ?>

    <script src="/assets/js/user/u_booking.js"></script>
</body>

</html>