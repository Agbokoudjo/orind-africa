import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

 (function () {
      const adminPath = '/admin';
      const origin = window.location.origin.replace(/\/$/, '');
      const adminUrl = origin + adminPath;

      const a = document.getElementById('adminLink');
      const c = document.getElementById('copyBtn');
      const hostLabel = document.getElementById('hostLabel');
      const infoBox = document.getElementById('infoBox');

      a.href = adminUrl;
      hostLabel.textContent = origin;

      c.addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(adminUrl);
          c.textContent = 'URL copiée ✅';
          c.style.background = 'linear-gradient(135deg, #22c55e, #86efac)';
          setTimeout(() => {
            c.textContent = 'Copier l’URL d’accès';
            c.style.background = '';
          }, 1600);
        } catch (e) {
          infoBox.innerHTML = '<strong>Info :</strong> Impossible de copier automatiquement. URL : <code>' + adminUrl + '</code>';
        }
      });
    })();
