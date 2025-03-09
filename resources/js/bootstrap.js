import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'e55844bfac4f51b59ea3',  // Your Pusher app key from .env
    cluster: 'ap2',  // Your Pusher app cluster from .env
    forceTLS: true,
});
