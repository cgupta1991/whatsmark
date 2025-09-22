import './bootstrap';
import './config';
import './tippy';
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import './../../vendor/power-components/livewire-powergrid/dist/tailwind.css';
import Recorder from 'recorder-core';
import 'recorder-core/src/engine/mp3';
import 'recorder-core/src/engine/mp3-engine';
import { Picker } from 'emoji-mart';
import Tribute from 'tributejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import 'glightbox/dist/css/glightbox.css';
import GLightbox from 'glightbox';
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

// Define a global function to initialize GLightbox
window.initGLightbox = function () {
  if (window.lightbox) {
    window.lightbox.destroy(); // Destroy the old instance to prevent duplicates
  }

  window.lightbox = GLightbox({
    selector: '.glightbox',
    touchNavigation: true,
    loop: true,
    zoomable: true,
    autoplayVideos: true,
  });
};

// Run it once on page load
document.addEventListener('DOMContentLoaded', function () {
  window.initGLightbox();
});

window.Tribute = Tribute;
window.TomSelect = TomSelect;

// Make Recorder globally available
window.Recorder = Recorder;
// Function to initialize TomSelect safely

window.initTomSelect = function (selector, options = {}) {
  document.querySelectorAll(selector).forEach((element) => {
    if (!(element instanceof HTMLSelectElement)) return; // Ensure it's a <select>

    // Check if the <select> has valid options to prevent the "trim" error
    let hasValidOptions = Array.from(element.options).some(
      (opt) => opt.value?.trim() !== '' || opt.text?.trim() !== ''
    );
    if (!hasValidOptions) return;

    if (element.tomselect) {
      element.tomselect.destroy(); // Destroy existing instance if already initialized
    }
    new TomSelect(element, options);
  });
};

// Initialize TomSelect when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  window.initTomSelect('#basic-select', {
    allowEmptyOption: true,
    placeholder: 'Select an option...',
    allowEmptyOption: true,
  });

  window.initTomSelect('#multiple-select', {
    plugins: ['remove_button'],
    allowEmptyOption: true,
    maxItems: null,
    delimiter: ',',
    persist: false,
  });

  window.initTomSelect('#child-select', {
    plugins: ['remove_button'],
    allowEmptyOption: true,
    maxItems: null,
    persist: false,
  });

  window.initTomSelect('.tom-select', {
    maxOptions: 500,
    allowEmptyOption: true,
    persist: false,
  });
  window.initTomSelect('.tom-select-two', {
    persist: false,
  });
});
/**
 * Initializes the emoji picker and appends it to the designated container.
 */
function initializeEmojiPicker() {
  const pickerContainer = document.getElementById('emoji-picker-container');
  const emojiPickerElement = document.getElementById('emoji-picker');
  const textMessageInput = document.getElementById('textMessageInput');

  if (!pickerContainer || !emojiPickerElement || !textMessageInput) return;

  emojiPickerElement.innerHTML = '';

  const picker = new Picker({
    onEmojiSelect: (emoji) => {
      textMessageInput.value += emoji.native;
      textMessageInput.dispatchEvent(new Event('input'));
    },
  });

  emojiPickerElement.appendChild(picker);
}
window.initializeEmojiPicker = initializeEmojiPicker;

/**
 * Toggles voice recording functionality.
 */
