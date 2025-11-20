// ตรวจสอบรหัสผ่านใหม่ว่าตรงกันหรือไม่
$("#changePassForm").on("submit", function (e) {
    let newPass = $("#new_password").val();
    let confirmPass = $("#confirm_password").val();

    if (newPass !== confirmPass) {
        e.preventDefault();
        alert("รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน กรุณาลองอีกครั้ง");
    }
});