const btn = document.getElementById('localise');
if (btn) {

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        localise();
    });

    async function localise() {
        const latInput = document.getElementById('customer_coordLat');
        const longInput = document.getElementById('customer_coordLng');
        // Verifier si le service de localisation est disponible sur le navigateur
        if (navigator.geolocation) {
            try {

                latInput.value = 'En cours...';
                longInput.value = 'En cours...';
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                });

                const lat = position.coords.latitude;
                const long = position.coords.longitude;
                latInput.value = lat;
                longInput.value = long;

                console.log('Localisation disponible. Latitude:', lat, 'Longitude:', long);

            } catch (error) {
                const errorMessageElement = document.getElementById('localisation-message');
                if (errorMessageElement) {
                    errorMessageElement.classList.replace('text-purple-950', 'text-yellow-500');
                    let errorMessage = "Localisation non disponible." + error.message;
                    if (error && error.code === 1) {
                        errorMessage = "Vous n'avez pas autorisé la localisation.";
                    }
                    errorMessageElement.textContent = errorMessage + ' notre equipe vous contactera par téléfone, vous pouvez continuer votre collecte sans localisation.';
                } else {
                    console.error('Element with id "localisation-message" not found');
                }
                console.error('Error occurred while getting location:', error);
            }
        } else {
            console.log('Localisation non disponible.');
            const message = document.getElementById('localisation-message');
            message.classList.replace('txet-purple-950', 'text-yellow-500');
            message.innerHTML = "Localisation non disponible. notre equipe vous contactera dans par téléfone vous pouvez continuer votre collecte sans localisation.";
        }
    }
}