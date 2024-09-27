import { BrowserQRCodeReader } from "@zxing/browser";

const codeReader = new BrowserQRCodeReader();
const scanerContainer = document.getElementById('scaner-container');
const qrControls = document.getElementById('qr-controls');
const openCameraButton = document.getElementById('open-camera-button');
const openCameraBox = document.getElementById('open-cam-box');
const stopButton = document.getElementById('stop-button');
let videoElement = document.getElementById('video-element');
const fullScreen = document.getElementById('full-screen');

window.onload = () => {
  if(scanerContainer){
    openCamera();
    openCameraButton.addEventListener('click', () => {
      openCamera();
      openCameraBox.classList.replace('block', 'hidden');
      qrControls.classList.replace('hidden', 'block');  
    });
  }
}

async function openCamera() {
  let videoInputDevices = await BrowserQRCodeReader.listVideoInputDevices();
  if (!videoInputDevices || videoInputDevices.length === 0) {
    console.error('No video input devices found');
    return;
  }

  let selectedDeviceId;
  let selectedDeviceLabel = 'Caméra arrière'; // Nom de la caméra à rechercher
  for (const device of videoInputDevices) {

    if (device.label.includes(selectedDeviceLabel) || device.label.includes('facing back') || device.label.includes('back') || device.label.includes('back camera')) {
      selectedDeviceId = device.deviceId;
      break;
    }
  }
  if (!selectedDeviceId) {
    selectedDeviceId = videoInputDevices[0].deviceId;
  }

  const oldVideoElement = document.getElementById('video-element');
  if (oldVideoElement) {
    oldVideoElement.remove();
  }
  fullScreen.classList.replace('hidden', 'flex');

  stopButton.addEventListener('click', () => {
    console.log('Stopped decode from camera');
    videoElement.pause();
    videoElement.srcObject = null;
    openCameraBox.classList.replace('hidden', 'block');
    videoElement.remove();
    let selectedDeviceId = null;
    let videoInputDevices = null;
    qrControls.classList.replace('flex', 'hidden');
    fullScreen.classList.replace('flex', 'hidden');
  });

  videoElement = document.createElement('video');
  videoElement.id = 'video-element';
  videoElement.autoplay = true;
  videoElement.setAttribute('class', 'block bg-black size-full border-2 border-purple-800 rounded-md');
  fullScreen.appendChild(videoElement);

  const controls = await codeReader.decodeOnceFromVideoDevice(selectedDeviceId, videoElement);
  const oldResult = document.getElementById('resultElement');
  if(oldResult){
    oldResult.remove();
  }
  if (controls) {
    const resultElement = document.createElement('p');
    resultElement.setAttribute('id', 'resultElement');
    resultElement.textContent = controls.getText();
    scanerContainer.appendChild(resultElement);
    fullScreen.classList.replace('flex', 'hidden');
    openCameraBox.classList.replace('hidden', 'block');
    // verifier si le resultat est effectivement un lien
    if (controls.getText().startsWith('http://localhost') || controls.getText().startsWith('https://localhost')) {
      window.location.href = controls.getText();
    }else{
      resultElement.textContent = 'Qr code invalide, assurez-vous que le qr code est bien celui de votre collecte.';
    }
  } else {
    console.log('No QR code found');
    const resultElement = document.createElement('p');
    resultElement.textContent = 'No QR code found';
    scanerContainer.appendChild(resultElement);
  }

}