document.addEventListener("DOMContentLoaded", () => {
  const search = document.getElementById("taskSearch");
  const status = document.getElementById("statusFilter");
  const list = document.getElementById("taskList");
  const noMatches = document.getElementById("noMatches");

  function filterTasks() {
    if (!list) return;
    const query = (search?.value || "").trim().toLowerCase();
    const filter = status?.value || "all";
    let visible = 0;
    list.querySelectorAll(".task-item").forEach(item => {
      const matchesText = (item.dataset.search || "").includes(query);
      const matchesStatus = filter === "all" || item.dataset.status === filter;
      const show = matchesText && matchesStatus;
      item.classList.toggle("hidden", !show);
      if (show) visible++;
    });
    if (noMatches) noMatches.classList.toggle("hidden", visible !== 0);
  }
  search?.addEventListener("input", filterTasks);
  status?.addEventListener("change", filterTasks);

  document.querySelectorAll("[data-scroll]").forEach(button => {
    button.addEventListener("click", () => {
      document.querySelector(button.dataset.scroll)?.scrollIntoView({behavior:"smooth", block:"start"});
      document.querySelector(`${button.dataset.scroll} input:not([type=hidden])`)?.focus({preventScroll:true});
    });
  });

  const focusButton = document.getElementById("focusButton");
  const focusStatus = document.getElementById("focusStatus");
  let endAt = null;
  let interval = null;
  focusButton?.addEventListener("click", () => {
    if (interval) {
      clearInterval(interval); interval = null; endAt = null;
      focusButton.textContent = "Start 25-minute focus";
      focusStatus.textContent = "Focus session stopped. Take a breath and restart when ready.";
      return;
    }
    endAt = Date.now() + 25 * 60 * 1000;
    focusButton.textContent = "Stop focus session";
    const update = () => {
      const remaining = Math.max(0, endAt - Date.now());
      const minutes = Math.floor(remaining / 60000);
      const seconds = Math.floor((remaining % 60000) / 1000);
      focusStatus.textContent = remaining > 0
        ? `Focus time: ${String(minutes).padStart(2,"0")}:${String(seconds).padStart(2,"0")}`
        : "Session complete! Great work — take a short break.";
      if (remaining <= 0) {
        clearInterval(interval); interval = null; endAt = null;
        focusButton.textContent = "Start another focus session";
      }
    };
    update(); interval = setInterval(update, 1000);
  });
});
