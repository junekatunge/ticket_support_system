<!-- PWA Meta Tags -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Helpdesk">
<meta name="theme-color" content="#8B4513">

<!-- PWA Manifest -->
<link rel="manifest" href="/helpdesk-core-php/manifest.json">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" href="/helpdesk-core-php/pwa-icon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/helpdesk-core-php/pwa-icon-192.png">

<!-- Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/helpdesk-core-php/service-worker.js')
      .then(function(registration) {
        console.log('ServiceWorker registration successful');
      })
      .catch(function(err) {
        console.log('ServiceWorker registration failed: ', err);
      });
  });
}

// PWA Install Prompt for iOS
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;

  // Show custom install button if you want
  console.log('PWA install prompt available');
});
</script>
