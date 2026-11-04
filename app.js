// GridWatch 2030 frontend
// Praat met de API op hetzelfde domein (/api/...). Pas API_BASE aan als de API elders draait.
const API_BASE = "";

const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => document.querySelectorAll(sel);

// ---------- helpers ----------
async function api(path, options = {}) {
    const res = await fetch(API_BASE + path, {
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        ...options,
    });
    let data = null;
    try { data = await res.json(); } catch (_) { /* geen JSON */ }
    if (!res.ok) {
        const err = new Error((data && (data.error || data.message)) || `HTTP ${res.status}`);
        err.status = res.status;
        throw err;
    }
    return data;
}

function toast(text) {
    const t = $("#toast");
    t.textContent = text;
    t.hidden = false;
    clearTimeout(toast._t);
    toast._t = setTimeout(() => (t.hidden = true), 3000);
}

function setMsg(el, text, ok) {
    el.textContent = text;
    el.className = "msg " + (ok ? "ok" : "err");
}

function esc(s) {
    return String(s ?? "").replace(/[&<>"']/g, (c) =>
        ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
}

function badge(status) {
    const s = String(status || "").toUpperCase();
    let cls = "ok";
    if (["WAARSCHUWING", "ONDERHOUD", "ONDERHOUD GEPLAND", "WARN"].includes(s)) cls = "warn";
    if (["STORING", "ACTIEVE STORING", "OFFLINE", "ERROR"].includes(s)) cls = "bad";
    return `<span class="badge ${cls}">${esc(status)}</span>`;
}

function fillTable(selector, rows, cols) {
    const tbody = $(selector + " tbody");
    if (!rows || !rows.length) {
        tbody.innerHTML = `<tr><td colspan="${cols.length}" class="muted">Geen gegevens</td></tr>`;
        return;
    }
    tbody.innerHTML = rows
        .map((r) => "<tr>" + cols.map((c) => `<td>${c(r)}</td>`).join("") + "</tr>")
        .join("");
}

// ---------- login / logout ----------
function showApp(user) {
    sessionStorage.setItem("gw_user", JSON.stringify(user));
    $("#login-view").hidden = true;
    $("#app-view").hidden = false;
    $("#user-label").textContent = `${user.username}${user.role ? " (" + user.role + ")" : ""}`;
    loadStations();
    loadHealth();
}

function showLogin() {
    sessionStorage.removeItem("gw_user");
    $("#app-view").hidden = true;
    $("#login-view").hidden = false;
    $("#password").value = "";
}

$("#login-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const msg = $("#login-msg");
    setMsg(msg, "Bezig met inloggen…", true);
    try {
        const user = await api("/api/login", {
            method: "POST",
            body: JSON.stringify({
                username: $("#username").value.trim(),
                password: $("#password").value,
            }),
        });
        setMsg(msg, "", true);
        showApp({ username: user.username || $("#username").value.trim(), role: user.role });
    } catch (err) {
        setMsg(msg, err.status === 401 ? "Onjuiste gebruikersnaam of wachtwoord." : "Fout: " + err.message, false);
        $("#password").value = "";
    }
});

$("#logout").addEventListener("click", async () => {
    try { await api("/api/logout", { method: "POST" }); } catch (_) { /* negeren */ }
    showLogin();
});

// ---------- tabs ----------
$("#nav").addEventListener("click", (e) => {
    const btn = e.target.closest("button[data-tab]");
    if (!btn) return;
    $$("#nav button").forEach((b) => b.classList.toggle("active", b === btn));
    $$(".tab").forEach((t) => (t.hidden = t.id !== "tab-" + btn.dataset.tab));
    const loaders = {
        overzicht: () => { loadStations(); loadHealth(); },
        storingen: loadAlerts,
        sensoren: loadSensors,
        onderhoud: fillStationSelect,
    };
    if (loaders[btn.dataset.tab]) loaders[btn.dataset.tab]();
});

// ---------- data laden ----------
let stationsCache = [];

async function loadStations() {
    try {
        stationsCache = await api("/api/stations");
        fillTable("#stations-table", stationsCache, [
            (r) => esc(r.id),
            (r) => esc(r.naam || r.station),
            (r) => esc(r.locatie),
            (r) => badge(r.status),
        ]);
    } catch (err) {
        toast("Stations laden mislukt: " + err.message);
    }
}

async function loadHealth() {
    const box = $("#health");
    try {
        const h = await api("/api/grid-health");
        const cls = h.status === "OK" ? "ok" : h.status === "WAARSCHUWING" ? "warn" : "bad";
        box.innerHTML = `
      <div class="tile"><div class="num">${esc(h.stations_online)}</div><div class="lbl">Stations online</div></div>
      <div class="tile"><div class="num">${esc(h.stations_offline)}</div><div class="lbl">Stations offline</div></div>
      <div class="tile"><div class="num">${esc(h.storingen)}</div><div class="lbl">Storingen</div></div>
      <div class="tile"><div class="num">${badge(h.status).replace("badge ok", "badge " + cls)}</div><div class="lbl">Netstatus</div></div>`;
    } catch (_) {
        box.innerHTML = ""; // bonus-endpoint is optioneel
    }
}

async function loadAlerts() {
    try {
        const rows = await api("/api/alerts");
        fillTable("#alerts-table", rows, [
            (r) => esc(r.tijdstip),
            (r) => badge(r.type),
            (r) => esc(r.beschrijving),
        ]);
    } catch (err) {
        toast("Meldingen laden mislukt: " + err.message);
    }
}

async function loadSensors() {
    try {
        const rows = await api("/api/sensor-data");
        fillTable("#sensor-table", rows, [
            (r) => esc(r.station),
            (r) => esc(r.vermogen_mw),
            (r) => esc(r.temperatuur),
            (r) => badge(r.status),
        ]);
    } catch (err) {
        toast("Sensordata laden mislukt: " + err.message);
    }
}

$("#refresh-stations").addEventListener("click", () => { loadStations(); loadHealth(); });
$("#refresh-alerts").addEventListener("click", loadAlerts);
$("#refresh-sensors").addEventListener("click", loadSensors);

// ---------- onderhoudsformulier ----------
async function fillStationSelect() {
    if (!stationsCache.length) await loadStations();
    $("#maint-station").innerHTML = stationsCache
        .map((s) => `<option value="${esc(s.id)}">${esc(s.naam || s.station)}</option>`)
        .join("");
}

$("#maint-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const msg = $("#maint-msg");
    try {
        await api("/api/maintenance", {
            method: "POST",
            body: JSON.stringify({
                station_id: Number($("#maint-station").value),
                prioriteit: $("#maint-prio").value,
                beschrijving: $("#maint-desc").value.trim(),
            }),
        });
        setMsg(msg, "Melding verstuurd.", true);
        $("#maint-desc").value = "";
    } catch (err) {
        setMsg(msg, "Versturen mislukt: " + err.message, false);
    }
});

// ---------- testtools ----------
$("#btn-error").addEventListener("click", async () => {
    const msg = $("#tools-msg");
    try {
        await api("/api/simulate/error", { method: "POST" });
        setMsg(msg, "Er is geen fout opgetreden (verwacht: foutmelding).", false);
    } catch (err) {
        // Een 500 is hier juist de bedoeling: de fout is gelogd.
        setMsg(msg, "Foutmelding gegenereerd en gelogd (" + err.message + ").", true);
    }
});

$("#btn-records").addEventListener("click", async (e) => {
    const btn = e.currentTarget;
    const msg = $("#tools-msg");
    const count = Number($("#records-count").value) || 1000;
    btn.disabled = true;
    setMsg(msg, `Bezig met ${count} sensorrecords aanmaken…`, true);
    try {
        const res = await api("/api/simulate/sensor-records", {
            method: "POST",
            body: JSON.stringify({ count }),
        });
        setMsg(msg, `Klaar: ${res && res.created != null ? res.created : count} records aangemaakt.`, true);
    } catch (err) {
        setMsg(msg, "Mislukt: " + err.message, false);
    } finally {
        btn.disabled = false;
    }
});

$("#btn-flood").addEventListener("click", async (e) => {
    const btn = e.currentTarget;
    const msg = $("#tools-msg");
    const bar = $("#flood-progress");
    const total = Math.min(Number($("#flood-count").value) || 40, 500);
    let ok = 0, failed = 0;
    btn.disabled = true;
    bar.max = total; bar.value = 0; bar.hidden = false;
    for (let i = 0; i < total; i++) {
        try { await api("/api/stations"); ok++; } catch (_) { failed++; }
        bar.value = i + 1;
    }
    bar.hidden = true;
    btn.disabled = false;
    setMsg(msg, `${total} verzoeken verstuurd (${ok} gelukt, ${failed} mislukt).`, failed === 0);
});

// ---------- opstarten ----------
(function init() {
    try {
        const saved = JSON.parse(sessionStorage.getItem("gw_user"));
        if (saved && saved.username) showApp(saved);
    } catch (_) { /* geen sessie */ }
})();