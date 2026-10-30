import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available on window for Echo
if (typeof window !== 'undefined') {
    window.Pusher = Pusher;
}

let echoInstance = null;
let connectionStateListeners = new Set();

export const initEcho = () => {
    if (echoInstance) {
        return echoInstance;
    }

    const config = (typeof window !== 'undefined' && window.__KDS_CONFIG__) || {};
    const key = config.pusherKey || import.meta.env.VITE_PUSHER_APP_KEY;
    const cluster = config.pusherCluster || import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1';
    const host = config.pusherHost || import.meta.env.VITE_PUSHER_HOST;
    const port = config.pusherPort || import.meta.env.VITE_PUSHER_PORT;
    const scheme = config.pusherScheme || import.meta.env.VITE_PUSHER_SCHEME || 'https';

    if (!key) {
        console.warn('[Pusher] Pusher Key is missing. Realtime updates might be disabled.');
        return null;
    }

    try {
        const echoOptions = {
            broadcaster: 'pusher',
            key: key,
            cluster: cluster,
            forceTLS: scheme === 'https',
            authEndpoint: '/api/broadcasting/auth',
            auth: {
                headers: {
                    get Authorization() {
                        const token = localStorage.getItem('kds_token');
                        return token ? `Bearer ${token}` : '';
                    },
                },
            },
        };

        if (host) {
            echoOptions.wsHost = host;
            echoOptions.wsPort = port || 80;
            echoOptions.wssPort = port || 443;
            echoOptions.enabledTransports = ['ws', 'wss'];
        }

        echoInstance = new Echo(echoOptions);

        // Bind connection events
        if (echoInstance.connector?.pusher?.connection) {
            const conn = echoInstance.connector.pusher.connection;
            conn.bind('state_change', (states) => {
                connectionStateListeners.forEach((listener) => listener(states.current, states.previous));
            });
        }

        return echoInstance;
    } catch (err) {
        console.error('[Pusher] Failed to initialize Laravel Echo:', err);
        return null;
    }
};

export const getEcho = () => {
    if (!echoInstance) {
        return initEcho();
    }
    return echoInstance;
};

export const onConnectionChange = (callback) => {
    connectionStateListeners.add(callback);
    return () => {
        connectionStateListeners.delete(callback);
    };
};

export const getKdsChannelName = () => {
    const config = (typeof window !== 'undefined' && window.__KDS_CONFIG__) || {};
    return config.kdsChannel || import.meta.env.VITE_PUSHER_KDS_CHANNEL || 'pos-seafood-kds-development';
};

export default {
    initEcho,
    getEcho,
    onConnectionChange,
    getKdsChannelName,
};

