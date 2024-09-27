// New collecte form
const canDoIt = document.getElementById('form-canDoIt');
if(canDoIt) {
    const canDoItChoice1 = document.getElementById('form_waiteForAgentChoice_0');
    const canDoItChoice2 = document.getElementById('form_waiteForAgentChoice_1');
    const collectChoice1 = document.getElementById('form_collecteChoice_0');
    const collectChoice2 = document.getElementById('form_collecteChoice_1');

    canDoItChoice2.addEventListener('change', (e) => {

        if(e.target.checked === true){
            canDoIt.classList.replace('hidden', 'flex');
            collectChoice1.required = false;
            collectChoice2.required = false;
        }
    })

    canDoItChoice1.addEventListener('change', (e) => {

        if(e.target.checked === true){
            canDoIt.classList.replace('flex', 'hidden');
            collectChoice1.required = false;
            collectChoice2.required = false;
        }
    })
}

// Get the label elements for the two payment methods
const labelPaymenMode = document.querySelector('label[for="checkout_paymentMethodes_0"]');
const labelPaymenMode1 = document.querySelector('label[for="checkout_paymentMethodes_1"]');

// Get the div elements for the two payment methods
const paymentMetodDiv = document.querySelector('.paymentMethod_0');
const paymentMetodDiv1 = document.querySelector('.paymentMethod_1');

// Get the input elements for the two payment methods
const paymentModeInput = document.getElementById('checkout_paymentMethodes_0');
const paymentModeInput1 = document.getElementById('checkout_paymentMethodes_1');

// Add icons and text to the label elements
if (labelPaymenMode) {
    const iconAndText = document.createElement('span');
    iconAndText.setAttribute('class', 'flex flex-col items-center px-2.5 py-0.5 justify-center');
    iconAndText.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
        </svg>
        <span>A la livraison</span>
    `;
    labelPaymenMode.innerHTML = iconAndText.outerHTML;
}

if (labelPaymenMode1) {
    const iconAndText = document.createElement('span');
    iconAndText.setAttribute('class', 'flex flex-col items-center px-2.5 py-0.5 justify-center');
    iconAndText.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
        </svg>
        <span>Mobile Money</span>
    `;
    labelPaymenMode1.innerHTML = iconAndText.outerHTML;
}

const searchToggler = document.getElementById('mobile-search-toggler');

if(searchToggler){

    const search = document.getElementById('search-toggle');

    searchToggler.addEventListener('click', (e) => {
        searchToggler.classList.replace('flex', 'hidden')
        search.classList.replace('hidden', 'flex')
    })
}