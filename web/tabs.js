// Onthoudt het gekozen tabblad (switchTab() staat in includes/header.html),
// zodat je na een formulier of "Vernieuwen" op hetzelfde tabblad blijft.
(function () {
  document.querySelectorAll('#nav button[data-tab]').forEach((b) =>
    b.addEventListener('click', () => {
      try { sessionStorage.setItem('gw_tab', b.dataset.tab); } catch (_) { /* geen opslag */ }
    })
  );

  let saved = null;
  try { saved = sessionStorage.getItem('gw_tab'); } catch (_) { /* geen opslag */ }
  if (saved && document.getElementById('tab-' + saved) && typeof switchTab === 'function') {
    switchTab(saved);
  }
})();
