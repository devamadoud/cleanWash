import { Dismiss } from "flowbite";
// Toast notification add to cart success
// Product page it this route
const currentUrl = window.location.href;
if(currentUrl.includes('boutique') || currentUrl.includes('product')) {
    toastCartAddSuccess();
}

function toastCartAddSuccess() {
    // target element that will be dismissed
    const toastCartSuccess = document.getElementById('toast-to-cart-success');

    // optional trigger element
    const triggerEl = document.getElementById('to-cart-dismiss');

    // options object
    const options = {
    transition: 'transition-opacity',
    duration: 10000,
    timing: 'ease-out',
    };

    // instance options object
    const instanceOptions = {
    id: 'toast-to-cart-success',
    override: true
    };
    const dismissTostcartSuccess = new Dismiss(toastCartSuccess, triggerEl, options, instanceOptions);
}

// Format the price above to USD using the locale, style, and currency.
function formatCurrency(value, locale = 'fr-SN', currency = 'XOF', thousandSeparator = '.', decimalSeparator = '.') {
    // Utiliser Intl.NumberFormat pour obtenir le format de base
    const formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
        notation: 'standard',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    // Formater le nombre
    const formattedValue = formatter.format(value);

    // Remplacer les séparateurs par les séparateurs personnalisés
    const formattedValueWithLocalizedSeparators = formattedValue
        .replace(/\u00A0/g, thousandSeparator) // Remplacer l'espace insécable par le séparateur de milliers personnalisé
        .replace(/\./g, decimalSeparator); // Remplacer le point par le séparateur décimal personnalisé

    return formattedValue;
}

const cart = document.getElementById('cart');
if(cart) {
    const cartCount = document.getElementById('cart-count');
    const cartTable = document.getElementById('cartTable');

    if(cartTable){
        if(cartCount === 0 || cartCount === null) {
            const emptyCart = document.getElementById('empty-cart');
            emptyCart.classList.replace('hidden', 'flex');
            cartTable.remove();
        }
    }
}

// Add to cart button using ajax
const cartBtnAdd = document.querySelectorAll('.add-to-cart');
cartBtnAdd.forEach((btn) => {
    btn.addEventListener('click', (e) => {
        if(e.target.parentNode.matches('.add-to-cart')){
            addProductToCart(e.target.parentNode.getAttribute('id'));
        }
    })
})

// Add to cart button using ajax
function addProductToCart(productId) {
    const toastCartSuccess = document.getElementById('toast-to-cart-success');
    const cartCount = document.getElementById('cart-count');
    const cartCountR = document.getElementById('cartCountR');
    const cartTot = document.getElementById('cartTot');
    const tostMessage = document.getElementById('toast-cart-success-message');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/cart/add/' + productId + '?ajax', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('id=' + productId);

    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status >= 200 && this.status < 400) {
            // Supposons que la réponse est le nouveau nombre total de produits dans le panier
            // get the json data response
            const data = JSON.parse(this.responseText);
            cartCount.classList.replace('hidden', 'flex');
            cartCount.textContent = data.articleCount;
            cartCountR.textContent = data.articleCount > 1 ? "Votre panier contient " + data.articleCount + " articles diférants" : "Votre panier contient " + data.articleCount + " article";
            cartTot.textContent = formatCurrency(data.cartTot);
            toastCartSuccess.classList.replace('hidden', 'flex');
            // Toast message
            tostMessage.textContent = 'L\'article a été ajoutée à votre panier';
            // Dismiss the toast notification after 5 seconds
            setTimeout(() => {
                toastCartSuccess.classList.replace('flex', 'hidden');
            }, 5000);
        }
    };
}

// Remove from cart button using ajax
const cartBtnRemove = document.querySelectorAll('.remove-from-cart');
cartBtnRemove.forEach((btn) => {
    btn.addEventListener('click', (e) => {
        let target = e.currentTarget;
        let productId = target.getAttribute('id');

        // Remove the product from the cart
        removeProductToCart(productId);
    })
})

function removeProductToCart(productId) {
    const cartBtnRemove = document.getElementById(productId);
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/cart/delete/' + productId + '?ajax', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('id=' + productId);

    xhr.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            // get the json data response
            const data = JSON.parse(this.responseText);

            // Supposons que la requête est le nouveau nombre total de produits dans le panier
            const cartCount = document.getElementById('cart-count');
            const cartTot = document.getElementById('cartTot');
            const cartSubTot = document.getElementById('cartSubTot');

            cartCount.textContent = data.articleCount;
            if(data.articleCount === 0) {
                const emptyCart = document.getElementById('empty-cart');
                const cartInfos = document.getElementById('cart-infos');
                const cartTable = document.getElementById('cartTable');

                cartInfos.remove();
                cartTable.remove();
                cartCount.remove();
                cartTot.classList.replace('flex', 'hidden');
                emptyCart.classList.replace('hidden', 'flex');
            }
            cartSubTot.textContent = formatCurrency(data.cartTot);
            cartTot.textContent = formatCurrency((data.cartTot + parseInt(cartTot.dataset['delivery'])));
            const container = document.getElementById('cart-container');
            const children = Array.from(container.children);
            const toRemove = cartBtnRemove.parentNode;
            toRemove.classList.add('fade-out');
            setTimeout(() => {
                children.forEach((child, index) => child.style.order = index + 1);
                toRemove.remove();
            }, 200);
        }
    };
}

// ValidateCommande
const validateBtn = document.getElementById('validateCommande');
const form = document.getElementById('checkout-form');

// Get the label elements for the two payment methods
const labelPaymenMode = document.querySelector('label[for="checkout_paymentMethodes_0"]');
const labelPaymenMode1 = document.querySelector('label[for="checkout_paymentMethodes_1"]');

// Get the div elements for the two payment methods
const paymentMetodDiv = document.querySelector('.paymentMethod_0');
const paymentMetodDiv1 = document.querySelector('.paymentMethod_1');

// Get the input elements for the two payment methods
const paymentModeInput = document.getElementById('checkout_paymentMethodes_0');
const paymentModeInput1 = document.getElementById('checkout_paymentMethodes_1');

const asMobileMoney = document.getElementById('asMobileMoney');
const atDelivery = document.getElementById('atDelivery');

if(validateBtn) {
    validateBtn.addEventListener('click', (e) => {
        form.classList.replace('hidden', 'flex');
        validateBtn.setAttribute('disabled', 'true');
        validateBtn.classList.replace('text-white', 'text-gray-600');
        validateBtn.classList.replace('bg-purple-700', 'bg-purple-400');
        validateBtn.classList.replace('hover:bg-purple-800', 'hover:bg-purple-400');

        if(paymentModeInput.checked == true){
            // Add styles to the second payment method div
            addStylesToPaymentMethodDivs0();
            atDelivery.classList.replace('hidden', 'flex');
            asMobileMoney.classList.replace('flex', 'hidden');
        }

        // Add event listener to the first payment method input
        paymentModeInput.addEventListener('click', (e) => {
            if (e.target.checked === true) {
                // Add styles to the first payment method div
                addStylesToPaymentMethodDivs0();
                atDelivery.classList.replace('hidden', 'flex');
                asMobileMoney.classList.replace('flex', 'hidden');
            }
        });

        if(paymentModeInput1.checked === true){
            // Add styles to the second payment method div
            addStylesToPaymentMethodDivs1();
            asMobileMoney.classList.replace('hidden', 'flex');
            atDelivery.classList.replace('flex', 'hidden');
        }
        // Add event listener to the second payment method input
        paymentModeInput1.addEventListener('click', (e) => {
            if (e.target.checked === true) {
                // Add styles to the second payment method div
                addStylesToPaymentMethodDivs1();
                asMobileMoney.classList.replace('hidden', 'flex');
                atDelivery.classList.replace('flex', 'hidden');
            }
        });
    })
}

function addStylesToPaymentMethodDivs0(){
    // Add styles to the first payment method div
    paymentMetodDiv.classList.add('bg-purple-100');
    labelPaymenMode.classList.add('text-purple-700');
    paymentMetodDiv.classList.add('border-purple-800');
    // Remove styles from the second payment method div
    paymentMetodDiv1.classList.remove('bg-purple-100');
    labelPaymenMode1.classList.remove('text-purple-700');
    paymentMetodDiv1.classList.remove('border-purple-800');
}

function addStylesToPaymentMethodDivs1(){
    // Add styles to the second payment method div
    paymentMetodDiv1.classList.add('bg-purple-100');
    paymentMetodDiv1.classList.add('border-purple-800');
    labelPaymenMode1.classList.add('text-purple-700');
    // Remove styles from the first payment method div
    paymentMetodDiv.classList.remove('bg-purple-100');
    paymentMetodDiv.classList.remove('border-purple-800');
    labelPaymenMode.classList.remove('text-purple-700');
}