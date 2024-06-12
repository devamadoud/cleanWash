import flowbite from 'flowbite';
import 'flowbite-datepicker';
import './bootstrap.js';
import './js/localisation.js';
import './js/qrcodeReader.js';
import './js/universal.js';
import './js/cart.js';
import './js/datepicker.js';
import './js/weeklyTransactionChart.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';


if('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw/sw.js').then(function(registration) {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function(err) {
        console.log('ServiceWorker registration failed: ', err);
    });
}