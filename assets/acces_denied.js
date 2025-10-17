(function() {
  const originalFetch = window.fetch;
  window.fetch = async (...args) => {
    const response = await originalFetch(...args);
    if (response.status === 403) {
      const data = await response.clone().json().catch(() => null);
      alert(data?.message || 'Accès refusé');
    }
    return response;
  };
})();

document.addEventListener('DOMContentLoaded', (e) => {
    e.preventDefault();
    console.log(e)
});
