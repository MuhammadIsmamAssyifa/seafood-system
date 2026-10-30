import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Mount when DOM is ready
const mountPoint = document.getElementById('kitchen-app');
if (mountPoint) {
    app.mount('#kitchen-app');
} else {
    document.addEventListener('DOMContentLoaded', () => {
        app.mount('#kitchen-app');
    });
}

