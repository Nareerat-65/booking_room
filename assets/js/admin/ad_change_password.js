document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("changePassForm");
  const newPass = document.getElementById("new_password");
  const confirmPass = document.getElementById("confirm_password");

  form.addEventListener("submit", (e) => {
    if (newPass.value.length < 6) {
      e.preventDefault();
      alert("รหัสผ่านใหม่ต้องยาวอย่างน้อย 6 ตัวอักษร");
      newPass.focus();
      return;
    }

    if (newPass.value !== confirmPass.value) {
      e.preventDefault();
      alert("รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน");
      confirmPass.focus();
      return;
    }
  });
});

// ✅ toggle แสดง/ซ่อนรหัส "เฉพาะช่องที่กด"
document.querySelectorAll(".toggle-password").forEach(btn => {
  btn.addEventListener("click", () => {
    const input = btn.closest(".input-group").querySelector("input");
    const icon = btn.querySelector("i");

    const type = input.getAttribute("type") === "password" ? "text" : "password";
    input.setAttribute("type", type);

    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
  });
});