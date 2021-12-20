require('./bootstrap');
import { createApp } from 'vue';
import App from './views/app.vue'

const mountEl = document.querySelector("#app app");

createApp(App, {...mountEl.dataset}).mount("#app")
