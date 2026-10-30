import { initEcho, getEcho, onConnectionChange, getKdsChannelName } from '@/shared/services/pusher';

export const subscribeToKitchenChannel = (handlers = {}) => {
    const echo = getEcho();
    if (!echo) {
        console.warn('[KDS Pusher] Echo instance not available');
        return null;
    }

    const channelName = getKdsChannelName();
    console.log(`[KDS Pusher] Subscribing to channel: ${channelName}`);

    const channel = echo.channel(channelName);

    const bindMulti = (eventNames, callback) => {
        if (!callback) return;
        eventNames.forEach((name) => {
            const cleanName = name.startsWith('.') ? name.substring(1) : name;
            // Listen with leading dot (Laravel Echo syntax for broadcastAs names)
            channel.listen(`.${cleanName}`, (data) => {
                console.log(`[KDS Pusher] Event received (.${cleanName}):`, data);
                callback(data);
            });
            // Listen without leading dot
            channel.listen(cleanName, (data) => {
                console.log(`[KDS Pusher] Event received (${cleanName}):`, data);
                callback(data);
            });
        });
    };

    // 1. Batch submitted by cashier to kitchen
    if (handlers.onBatchSubmitted) {
        bindMulti([
            'kds.batch.submitted',
            'OrderBatchSubmitted',
            'KdsBatchSubmitted',
        ], handlers.onBatchSubmitted);
    }

    // 2. Batch updated in kitchen (status changed, etc.)
    if (handlers.onBatchUpdated || handlers.onOrderUpdated) {
        const batchCallback = handlers.onBatchUpdated || handlers.onOrderUpdated;
        bindMulti([
            'kds.batch.updated',
            'OrderBatchUpdated',
            'KdsBatchUpdated',
            'kds.order.updated',
            'OrderUpdated',
        ], batchCallback);
    }

    // 3. Item status updated (cooking, ready, served)
    if (handlers.onItemStatusChanged) {
        bindMulti([
            'kds.item.updated',
            'kds.item.changed',
            'OrderItemStatusChanged',
            'KdsItemUpdated',
            'KdsItemChanged',
        ], handlers.onItemStatusChanged);
    }

    // 4. Order created
    if (handlers.onOrderCreated) {
        bindMulti([
            'kds.order.created',
            'OrderCreated',
        ], handlers.onOrderCreated);
    }

    return channel;
};

export const unsubscribeFromKitchenChannel = () => {
    const echo = getEcho();
    if (!echo) return;

    const channelName = getKdsChannelName();
    echo.leaveChannel(channelName);
};

export {
    initEcho,
    getEcho,
    onConnectionChange,
    getKdsChannelName,
};

export default {
    subscribeToKitchenChannel,
    unsubscribeFromKitchenChannel,
    initEcho,
    getEcho,
    onConnectionChange,
};
