// Register the service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            // Registration was successful
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, err => {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// Configuration du bouton d'installation de l'UI
const installBanner = document.getElementById('installBanner');
const installButton = document.getElementById('installButton');
const closeBannerButton = document.getElementById('closeBannerButton');

if (installBanner && installButton && closeBannerButton) {
    let deferredPrompt;

    const showInstallBanner = () => {
        installBanner.style.display = 'block';
    };

    const hideInstallBanner = () => {
        installBanner.style.display = 'none';
    };

    // Vérifier si le navigateur supporte l'installation des PWA
    const isPwaInstallable = () => {
        return 'serviceWorker' in navigator && 'BeforeInstallPromptEvent' in window;
    };

    // Vérifier si l'application est déjà installée
    const isAppInstalled = () => {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    };

    // Cacher la bannière si l'installation n'est pas supportée ou si l'app est déjà installée
    if (isPwaInstallable() && !isAppInstalled()) {
        showInstallBanner();
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallBanner();
        });

        installButton.addEventListener('click', async () => {
            hideInstallBanner();
            if (deferredPrompt) {
                try {
                    const { outcome } = await deferredPrompt.prompt();
                    console.log(`L'utilisateur a ${outcome === 'accepted' ? 'accepté' : 'refusé'} l'installation de l'A2HS`);
                } catch (error) {
                    console.error('Erreur lors de l\'affichage de l\'invite d\'installation:', error);
                } finally {
                    deferredPrompt = null;
                }
            }
        });

        window.addEventListener('appinstalled', (evt) => {
            console.log('L\'application a été installée', evt);
            hideInstallBanner();
        });

        closeBannerButton.addEventListener('click', hideInstallBanner);
    }
}

// Define the offline message and the online message elements.
const statusOffline = document.getElementById('offline-warning');
const statusOnline = document.getElementById('online-warning');

// if the status messages is defined, onload, check the network state.
if (statusOffline && statusOnline) {
    // Show message for de network state.
    // This is a bit hacky, but it works.
    window.addEventListener('load', () => {
    
    
        // Check if the device is online.
        let wasOffline = !navigator.onLine;
    
        // Update the online/offline status.
        function updateOnlineStatus() {
    
            // check if the navigator is online.
            // if it is, remove the offline message.
            // if it isn't, show the offline message and remove the online message after 5 seconds
            if (navigator.onLine) {
                statusOffline.style.display = 'none';
                if (wasOffline) {
                    statusOnline.style.display = 'block';
    
                    setTimeout(() => {
                        statusOnline.style.display = 'none';
                    }, 5000);
                }
                wasOffline = false;
        
            }else{
                statusOffline.style.display = 'block';
                statusOnline.style.display = 'none';
                wasOffline = true;
            }
        }
    
        window.addEventListener('online', () => {
            wasOffline = true;
            updateOnlineStatus();
        });
        
        window.addEventListener('offline', () => {
            wasOffline = false;
            updateOnlineStatus();
        });
    
        updateOnlineStatus();
    });
}