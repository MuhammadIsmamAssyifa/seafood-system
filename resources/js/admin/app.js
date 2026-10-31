import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

// Global styles are loaded via blade @vite directive (app.css)

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#admin-app');

