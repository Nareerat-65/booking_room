document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  const boardEl = document.getElementById("board");

  const btnBoard = document.getElementById("btnViewBoard");
  const btnCalendar = document.getElementById("btnViewCalendar");

  const toggleCleaning = document.getElementById("toggleCleaning");
  const searchText = document.getElementById("searchText");

  let rangeDays = 14;
  let showCleaning = true;
  let queryText = "";

  function fmtYMD(d) {
    const x = new Date(d);
    const y = x.getFullYear();
    const m = String(x.getMonth() + 1).padStart(2, "0");
    const day = String(x.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
  }

  function addDays(date, n) {
    const d = new Date(date);
    d.setDate(d.getDate() + n);
    return d;
  }

  function openEventModal(ev) {
    // ใช้ modal เดิมของคุณ (id eventDetailModal)
    document.getElementById("eventId").textContent =
      ev.extendedProps.booking_code || ev.extendedProps.booking_id || "-";
    document.getElementById("eventRoom").textContent =
      ev.extendedProps.room || "-";
    document.getElementById("eventBooker").textContent =
      ev.extendedProps.booker || "-";
    document.getElementById("eventDates").textContent =
      `${ev.extendedProps.start_real || ev.startStr} ถึง ${ev.extendedProps.end_real || "-"}`;
    document.getElementById("eventGuests").textContent =
      ev.extendedProps.guests || "-";

    const modal = new bootstrap.Modal(
      document.getElementById("eventDetailModal"),
    );
    modal.show();
  }

  async function fetchEvents(startYmd, endYmd) {
    const url = `ad_calendar_events.php?start=${encodeURIComponent(startYmd)}&end=${encodeURIComponent(endYmd)}`;
    const res = await fetch(url, { credentials: "same-origin" });
    const data = await res.json();

    const filtered = data.filter((e) => {
      const type = e.extendedProps?.type || "";
      if (!showCleaning && type === "cleaning") return false;

      if (queryText.trim() !== "") {
        const t = queryText.trim().toLowerCase();
        const hay =
          (e.title || "") +
          " " +
          (e.extendedProps?.room || "") +
          " " +
          (e.extendedProps?.booker || "") +
          " " +
          (e.extendedProps?.booking_code || "");
        if (!hay.toLowerCase().includes(t)) return false;
      }
      return true;
    });

    return filtered;
  }

  // ---------------- Calendar (รอง) ----------------
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    height: "auto",
    locale: "th",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,listWeek",
    },
    events: function (info, success, failure) {
      fetchEvents(fmtYMD(info.start), fmtYMD(info.end))
        .then(success)
        .catch(failure);
    },
    eventClick: function (info) {
      info.jsEvent.preventDefault();
      openEventModal(info.event);
    },
    eventContent: function (arg) {
      // ทำให้ “สั้นลง” ลดรกใน month view
      const type = arg.event.extendedProps.type;
      const title = arg.event.title;

      const wrap = document.createElement("div");
      wrap.style.fontSize = "12px";
      wrap.style.lineHeight = "1.2";
      wrap.style.whiteSpace = "nowrap";
      wrap.style.overflow = "hidden";
      wrap.style.textOverflow = "ellipsis";

      if (type === "cleaning") {
        wrap.innerHTML = `🧹 ${title}`;
      } else {
        wrap.textContent = title;
      }
      return { domNodes: [wrap] };
    },
  });

  // ---------------- Board (หลัก) ----------------
  async function renderBoard(anchorDate = new Date()) {
    const start = new Date(anchorDate);
    start.setHours(0, 0, 0, 0);
    const end = addDays(start, rangeDays);

    const startYmd = fmtYMD(start);
    const endYmd = fmtYMD(end);

    const events = await fetchEvents(startYmd, endYmd);

    // ห้อง (คุณจะเปลี่ยนเป็นดึงจาก DB ทีหลังได้)
    const rooms = [
      { id: 1, name: "ห้อง 001" },
      { id: 2, name: "ห้อง 002" },
      { id: 3, name: "ห้อง 003" },
      { id: 4, name: "ห้อง 004" },
      { id: 5, name: "ห้อง 005" },
      { id: 6, name: "ห้อง 006" },
    ];

    // สร้างหัวตารางวัน
    const days = [];
    for (let i = 0; i < rangeDays; i++) days.push(addDays(start, i));

    boardEl.innerHTML = `
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="boardTable" style="table-layout: fixed;">
            <thead class="table-light">
              <tr>
                <th style="width:180px">ห้อง</th>
                ${days
                  .map(
                    (d) => `
                  <th class="text-center day-col" style="min-width:90px;width:90px">
                    ${d.getDate()}/${d.getMonth() + 1}
                  </th>`,
                  )
                  .join("")}
              </tr>
            </thead>
            <tbody>
              ${rooms
                .map(
                  (r) => `
                <tr class="room-row" data-room="${r.id}">
                  <th class="bg-body-tertiary" style="width:180px">${r.name}</th>
                  ${days.map(() => `<td class="day-cell position-relative" style="height:54px"></td>`).join("")}
                </tr>`,
                )
                .join("")}
            </tbody>
          </table>
        </div>
        <div class="small text-muted mt-2">คลิกแถบเพื่อดูรายละเอียด</div>
      </div>
    </div>
  `;

    const table = document.getElementById("boardTable");
    const firstDayTh = table.querySelector("thead th.day-col");
    const cellWidth = firstDayTh
      ? firstDayTh.getBoundingClientRect().width
      : 90;

    function dateToIndex(ymd) {
      const d = new Date(ymd);
      d.setHours(0, 0, 0, 0);
      return Math.round((d - start) / (1000 * 60 * 60 * 24));
    }

    // 1) ใส่ overlay container ต่อแถว (กว้างเท่าวันทั้งหมด)
    document.querySelectorAll(".room-row").forEach((row) => {
      const cells = row.querySelectorAll("td.day-cell");
      if (!cells.length) return;

      // ให้ cell แรกเป็นจุดอ้างอิง แล้วสร้าง overlay ครอบช่องวันทั้งหมด
      const firstCell = cells[0];

      // ทำ overlay เป็น absolute บน cell แรก และยาวครอบทั้งแถววัน
      const overlay = document.createElement("div");
      overlay.className = "row-overlay";
      overlay.style.position = "absolute";
      overlay.style.left = "0";
      overlay.style.top = "0";
      overlay.style.height = "54px";
      overlay.style.width = `calc(${rangeDays} * ${cellWidth}px)`;
      overlay.style.pointerEvents = "none"; // ให้คลิกผ่านได้ ยกเว้นแถบเอง
      overlay.style.zIndex = "1";

      // ต้องทำให้ cell แรก position:relative อยู่แล้ว (เราใส่ไว้ใน HTML)
      firstCell.appendChild(overlay);

      // ซ่อนเส้นทับ overlay ด้วย padding สวย ๆ
      firstCell.style.overflow = "visible";
    });

    // 2) วางแถบยาวลง overlay ของแถวนั้น
    rooms.forEach((room) => {
      const row = boardEl.querySelector(`tr[data-room="${room.id}"]`);
      if (!row) return;

      const overlay = row.querySelector(".row-overlay");
      if (!overlay) return;

      const roomEvents = events.filter(
        (e) => (e.extendedProps?.room_id || 0) === room.id,
      );

      roomEvents.forEach((e) => {
        const sIdx = Math.max(0, dateToIndex(e.start));
        const endExclusiveIdx = dateToIndex(e.end); // end เป็น exclusive
        const eIdx = Math.min(rangeDays, endExclusiveIdx);
        const span = Math.max(1, eIdx - sIdx);

        if (sIdx >= rangeDays || eIdx <= 0) return;

        const type = e.extendedProps?.type || "";

        const bar = document.createElement("div");
        bar.className = "board-bar";
        bar.style.position = "absolute";
        bar.style.left = `${sIdx * cellWidth + 4}px`;
        bar.style.top = "8px";
        bar.style.height = "38px";
        bar.style.width = `${span * cellWidth - 8}px`;
        bar.style.borderRadius = "12px";
        bar.style.padding = "8px 10px";
        bar.style.whiteSpace = "nowrap";
        bar.style.overflow = "hidden";
        bar.style.textOverflow = "ellipsis";
        bar.style.cursor = "pointer";
        bar.style.pointerEvents = "auto"; // ให้คลิกที่แถบได้
        bar.style.zIndex = "2";

        if (type === "cleaning") {
          bar.style.background = "#9e9e9e";
          bar.style.color = "#fff";
          bar.textContent = `🧹 ${e.title}`;
        } else {
          bar.style.background = "#64b5f6";
          bar.style.color = "#0b1b2a";
          bar.textContent = e.title;
        }

        bar.addEventListener("click", () => {
          openEventModal({
            startStr: e.start,
            extendedProps: e.extendedProps,
          });
        });

        overlay.appendChild(bar);
      });
    });
  }

  //   async function renderBoard(anchorDate = new Date()) {
  //     const start = new Date(anchorDate);
  //     start.setHours(0, 0, 0, 0);
  //     const end = addDays(start, rangeDays);

  //     const startYmd = fmtYMD(start);
  //     const endYmd = fmtYMD(end);

  //     const events = await fetchEvents(startYmd, endYmd);

  //     // ห้อง (ถ้าคุณมี rooms table และอยากดึงจริง ๆ ค่อยต่อ API)
  //     const rooms = [
  //       { id: 1, name: "ห้อง 001" },
  //       { id: 2, name: "ห้อง 002" },
  //       { id: 3, name: "ห้อง 003" },
  //       { id: 4, name: "ห้อง 004" },
  //       { id: 5, name: "ห้อง 005" },
  //       { id: 6, name: "ห้อง 006" },
  //     ];

  //     // สร้างหัวตารางวัน
  //     const days = [];
  //     for (let i = 0; i < rangeDays; i++) {
  //       const d = addDays(start, i);
  //       days.push(d);
  //     }

  //     boardEl.innerHTML = `
  //       <div class="card">
  //         <div class="card-body">
  //           <div class="table-responsive">
  //             <table class="table table-bordered align-middle" style="table-layout: fixed; min-width:${rangeDays * 90 + 180}px">
  //               <thead class="table-light">
  //                 <tr>
  //                   <th style="width:180px">ห้อง</th>
  //                   ${days.map(d => `<th class="text-center" style="width:90px">${d.getDate()}/${d.getMonth()+1}</th>`).join("")}
  //                 </tr>
  //               </thead>
  //               <tbody>
  //                 ${rooms.map(r => `<tr data-room="${r.id}">
  //                   <th class="bg-body-tertiary">${r.name}</th>
  //                   ${days.map(() => `<td class="p-1 position-relative" style="height:44px"></td>`).join("")}
  //                 </tr>`).join("")}
  //               </tbody>
  //             </table>
  //           </div>
  //           <div class="small text-muted mt-2">คลิกแถบเพื่อดูรายละเอียด</div>
  //         </div>
  //       </div>
  //     `;

  //     // วาง event ลงช่อง
  //     function dateToIndex(ymd) {
  //       const d = new Date(ymd);
  //       d.setHours(0,0,0,0);
  //       const diff = Math.round((d - start) / (1000*60*60*24));
  //       return diff;
  //     }

  //     rooms.forEach(room => {
  //       const row = boardEl.querySelector(`tr[data-room="${room.id}"]`);
  //       if (!row) return;
  //       const tds = row.querySelectorAll("td");

  //       // events ของห้องนั้น
  //       const roomEvents = events.filter(e => (e.extendedProps?.room_id || 0) === room.id);

  //       roomEvents.forEach(e => {
  //         const sIdx = Math.max(0, dateToIndex(e.start));
  //         // end ของ FullCalendar เป็น exclusive → ตัด -1 เพื่อ span ช่อง
  //         const endExclusiveIdx = dateToIndex(e.end);
  //         const eIdx = Math.min(rangeDays, endExclusiveIdx);
  //         const span = Math.max(1, eIdx - sIdx);

  //         if (sIdx >= rangeDays || eIdx <= 0) return;

  //         const cell = tds[sIdx];
  //         if (!cell) return;

  //         const type = e.extendedProps?.type || "";
  //         const div = document.createElement("div");
  //         div.className = "badge w-100 text-start";
  //         div.style.cursor = "pointer";
  //         div.style.display = "block";
  //         div.style.padding = "10px 8px";
  //         div.style.borderRadius = "10px";
  //         div.style.whiteSpace = "nowrap";
  //         div.style.overflow = "hidden";
  //         div.style.textOverflow = "ellipsis";

  //         // สี
  //         if (type === "cleaning") {
  //           div.style.background = "#9e9e9e";
  //           div.style.color = "#fff";
  //           div.textContent = `🧹 ${e.title}`;
  //         } else {
  //           div.style.background = "#64b5f6";
  //           div.style.color = "#0b1b2a";
  //           div.textContent = e.title;
  //         }

  //         // กว้างข้ามหลายช่อง
  //         div.style.position = "absolute";
  //         div.style.left = "4px";
  //         div.style.top = "4px";
  //         div.style.height = "36px";
  //         div.style.width = `calc(${span} * 90px - 8px)`;

  //         div.addEventListener("click", () => {
  //           // สร้าง mock event object สำหรับ modal ให้ใช้ของเดิมได้
  //           openEventModal({
  //             startStr: e.start,
  //             extendedProps: e.extendedProps
  //           });
  //         });

  //         cell.appendChild(div);
  //       });
  //     });
  //   }

  // ---------------- UI Handlers ----------------
  function showBoard() {
    boardEl.classList.remove("d-none");
    calendarEl.classList.add("d-none");
    btnBoard.classList.add("btn-dark");
    btnBoard.classList.remove("btn-outline-dark");
    btnCalendar.classList.remove("btn-dark");
    btnCalendar.classList.add("btn-outline-dark");
    renderBoard(new Date());
  }

  function showCalendar() {
    calendarEl.classList.remove("d-none");
    boardEl.classList.add("d-none");
    btnCalendar.classList.add("btn-dark");
    btnCalendar.classList.remove("btn-outline-dark");
    btnBoard.classList.remove("btn-dark");
    btnBoard.classList.add("btn-outline-dark");
    calendar.refetchEvents();
  }

  btnBoard.addEventListener("click", showBoard);
  btnCalendar.addEventListener("click", showCalendar);

  document.querySelectorAll("[data-range]").forEach((btn) => {
    btn.addEventListener("click", () => {
      // Remove active class from all buttons
      document.querySelectorAll("[data-range]").forEach(b => b.classList.remove("active"));
      // Add active class to clicked button
      btn.classList.add("active");
      
      rangeDays = parseInt(btn.getAttribute("data-range"), 10) || 14;
      if (!boardEl.classList.contains("d-none")) renderBoard(new Date());
    });
  });

  toggleCleaning.addEventListener("change", () => {
    showCleaning = toggleCleaning.checked;
    if (!boardEl.classList.contains("d-none")) renderBoard(new Date());
    else calendar.refetchEvents();
  });

  searchText.addEventListener("input", () => {
    queryText = searchText.value;
    if (!boardEl.classList.contains("d-none")) renderBoard(new Date());
    else calendar.refetchEvents();
  });

  // Default: Board
  showBoard();
  
  let boardResizeTimer = null;
  window.addEventListener("resize", () => {
    if (boardEl.classList.contains("d-none")) return;
    clearTimeout(boardResizeTimer);
    boardResizeTimer = setTimeout(() => renderBoard(new Date()), 150);
  });
});
