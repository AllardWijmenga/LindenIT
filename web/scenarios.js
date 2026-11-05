// Knoppen op het tabblad "Scenario's".
(function () {
  const msg = document.getElementById('tools-msg');
  if (!msg) return;

  function say(text, ok) {
    msg.textContent = text;
    msg.className = 'msg ' + (ok ? 'ok' : 'err');
  }

  async function post(body) {
    const res = await fetch('/scenarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    let data = null;
    try { data = await res.json(); } catch (_) { /* geen JSON */ }
    return { status: res.status, data };
  }

  document.getElementById('btn-error').addEventListener('click', async () => {
    const r = await post({ action: 'error' });
    say(r.status === 500
      ? 'Foutmelding gegenereerd en gelogd (ERROR Test exception generated).'
      : 'Onverwacht antwoord: ' + r.status, r.status === 500);
  });

  document.getElementById('btn-records').addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    const count = Number(document.getElementById('records-count').value) || 1000;
    btn.disabled = true;
    say('Bezig met ' + count + ' sensorrecords aanmaken…', true);
    try {
      const r = await post({ action: 'records', count });
      say(r.status === 200 ? 'Klaar: ' + r.data.created + ' records aangemaakt.'
                           : 'Mislukt: ' + ((r.data && r.data.error) || r.status), r.status === 200);
    } finally {
      btn.disabled = false;
    }
  });

  document.getElementById('btn-flood').addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    const bar = document.getElementById('flood-progress');
    const total = Math.min(Number(document.getElementById('flood-count').value) || 40, 500);
    let ok = 0, failed = 0;
    btn.disabled = true;
    bar.max = total; bar.value = 0; bar.hidden = false;
    for (let i = 0; i < total; i++) {
      try { (await fetch('/api/stations')).ok ? ok++ : failed++; } catch (_) { failed++; }
      bar.value = i + 1;
    }
    bar.hidden = true;
    btn.disabled = false;
    say(total + ' verzoeken verstuurd (' + ok + ' gelukt, ' + failed + ' mislukt).', failed === 0);
  });
})();
