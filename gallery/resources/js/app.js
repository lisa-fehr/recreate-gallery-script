require('./bootstrap');
import { createApp } from 'vue';
import App from './views/app.vue'

let app=createApp(App)

app.mount("#app")
