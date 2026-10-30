import { defineStore } from 'pinia';
import kitchenService from '@/kitchen/services/kitchen.service';
import kitchenActionService from '@/kitchen/services/kitchen-action.service';
import { onConnectionChange } from '@/shared/services/pusher';

export const useKitchenStore = defineStore('kds-kitchen', {
    state: () => ({
        orders: [],
        stations: [],
        selectedOrder: null,
        activeTab: 'all', // 'all' | 'waiting' | 'preparing' | 'ready' | 'completed'
        loading: false,
        error: null,
        detailLoading: false,
        detailError: null,
        pendingActionKeys: [], // list of action keys currently executing
        connectionStatus: 'disconnected', // 'connected' | 'connecting' | 'disconnected' | 'unavailable'
        lastUpdated: null,
    }),

    getters: {
        /**
         * Normalized status mapping helper
         */
        normalizeStatus: () => (status) => {
            const s = (status || '').toLowerCase();
            if (['sent', 'waiting', 'submitted', 'draft'].includes(s)) return 'waiting';
            if (['cooking', 'preparing'].includes(s)) return 'preparing';
            if (['ready'].includes(s)) return 'ready';
            if (['completed', 'served'].includes(s)) return 'completed';
            return 'waiting';
        },

        waitingOrders(state) {
            return state.orders.filter((o) => this.normalizeStatus(o.status) === 'waiting');
        },

        preparingOrders(state) {
            return state.orders.filter((o) => this.normalizeStatus(o.status) === 'preparing');
        },

        readyOrders(state) {
            return state.orders.filter((o) => this.normalizeStatus(o.status) === 'ready');
        },

        completedOrders(state) {
            return state.orders.filter((o) => this.normalizeStatus(o.status) === 'completed');
        },

        /**
         * Status Summary for StatusCard counters
         */
        summary() {
            return {
                waiting: this.waitingOrders.length,
                preparing: this.preparingOrders.length,
                ready: this.readyOrders.length,
                total: this.orders.length,
            };
        },

        /**
         * Queue sorted by priority (FIFO: created_at ASC)
         * Waiting orders first, then preparing, then ready, then completed
         */
        sortedQueue(state) {
            return [...state.orders].sort((a, b) => {
                const dateA = new Date(a.created_at || a.sent_to_kitchen_at || 0).getTime();
                const dateB = new Date(b.created_at || b.sent_to_kitchen_at || 0).getTime();
                return dateA - dateB;
            });
        },

        /**
         * Filtered queue based on active status filter
         */
        filteredQueue() {
            if (this.activeTab === 'all') {
                return this.sortedQueue;
            }
            return this.sortedQueue.filter((o) => this.normalizeStatus(o.status) === this.activeTab);
        },

        isPusherConnected: (state) => state.connectionStatus === 'connected',

        isActionPending: (state) => (key) => state.pendingActionKeys.includes(String(key)),

        /**
         * Average waiting time for all active unserved orders (Sprint 07)
         * Formatted as MM:SS
         */
        averageWaitingTime(state) {
            const activeOrders = state.orders.filter((o) => {
                const s = this.normalizeStatus(o.status);
                return s === 'waiting' || s === 'preparing';
            });
            if (activeOrders.length === 0) return '00:00';

            const now = Date.now();
            let totalDiffSeconds = 0;
            for (const order of activeOrders) {
                const start = new Date(order.sent_to_kitchen_at || order.created_at || now).getTime();
                if (!isNaN(start)) {
                    totalDiffSeconds += Math.max(0, Math.floor((now - start) / 1000));
                }
            }
            const avgSeconds = Math.floor(totalDiffSeconds / activeOrders.length);
            const minutes = Math.floor(avgSeconds / 60);
            const seconds = avgSeconds % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        /**
         * Formatted last sync time (HH:mm:ss)
         */
        lastSyncFormatted(state) {
            if (!state.lastUpdated) return '-';
            const d = new Date(state.lastUpdated);
            return isNaN(d.getTime()) ? '-' : d.toLocaleTimeString('id-ID', { hour12: false });
        },
    },

    actions: {
        setActionPending(key, isPending) {
            const strKey = String(key);
            if (isPending) {
                if (!this.pendingActionKeys.includes(strKey)) {
                    this.pendingActionKeys.push(strKey);
                }
            } else {
                this.pendingActionKeys = this.pendingActionKeys.filter((k) => k !== strKey);
            }
        },

        setConnectionStatus(status) {
            this.connectionStatus = status;
        },

        /**
         * Watch connection state & automatically resync on reconnection (Sprint 07)
         */
        initConnectionWatcher() {
            onConnectionChange((currentState, previousState) => {
                this.connectionStatus = currentState;
                // If reconnecting from disconnected or connecting to connected, auto-sync queue
                if (currentState === 'connected' && previousState && previousState !== 'connected') {
                    console.log('[KDS Pusher] Reconnected! Auto-syncing kitchen queue...');
                    this.fetchOrders(true);
                }
            });
        },

        /**
         * Prevent duplicate chime for the same batch within 15 seconds
         */
        canPlayAlertFor(batchId) {
            if (!batchId) return true;
            const key = `batch_alert_${batchId}`;
            const lastAlert = this._recentAlerts ? this._recentAlerts[key] : null;
            const now = Date.now();
            if (lastAlert && now - lastAlert < 15000) {
                return false;
            }
            if (!this._recentAlerts) this._recentAlerts = {};
            this._recentAlerts[key] = now;
            return true;
        },

        setActiveTab(tab) {
            this.activeTab = tab;
        },

        selectOrder(order) {
            this.selectedOrder = order;
        },

        upsertOrder(batch) {
            if (!batch || !batch.id) return;
            const status = (batch.status || '').toLowerCase();
            if (['completed', 'cancelled'].includes(status)) {
                this.orders = this.orders.filter((o) => o.id !== batch.id);
                return;
            }
            const index = this.orders.findIndex((o) => o.id === batch.id);
            if (index >= 0) {
                this.orders[index] = { ...this.orders[index], ...batch };
            } else {
                this.orders.unshift(batch);
            }
        },

        clearSelectedOrder() {
            this.selectedOrder = null;
            this.detailError = null;
        },

        /**
         * Fetch full detail for a single order/batch
         * @param {string|number} id
         */
        async fetchOrderDetail(id) {
            this.detailLoading = true;
            this.detailError = null;
            try {
                const res = await kitchenService.getOrderDetail(id);
                const orderData = res.data || res;
                this.selectedOrder = orderData;
                return orderData;
            } catch (err) {
                const message =
                    err.response?.status === 404
                        ? 'Order tidak ditemukan.'
                        : err.customMessage || err.response?.data?.message || 'Gagal mengambil detail order.';
                this.detailError = message;
                return null;
            } finally {
                this.detailLoading = false;
            }
        },

        clearOrders() {
            this.orders = [];
            this.selectedOrder = null;
            this.error = null;
        },

        /**
         * Fetch orders from API
         */
        async fetchOrders(silent = false) {
            if (!silent) {
                this.loading = true;
            }
            this.error = null;
            try {
                const res = await kitchenService.getOrders();
                const data = res.data || res;
                const list = Array.isArray(data) ? data : [];
                // Exclude any draft or unsubmitted order/batch
                this.orders = list.filter((order) => {
                    const orderStatus = (order.order?.status || '').toLowerCase();
                    const batchStatus = (order.status || '').toLowerCase();
                    return orderStatus !== 'draft' && batchStatus !== 'waiting' && batchStatus !== 'draft';
                });
                this.lastUpdated = new Date();
            } catch (err) {
                this.error = err.customMessage || err.response?.data?.message || 'Gagal mengambil data kitchen.';
            } finally {
                if (!silent) {
                    this.loading = false;
                }
            }
        },

        /**
         * Refresh orders without full blocking loading indicator
         */
        async refreshOrders() {
            return this.fetchOrders(false);
        },

        /**
         * Fetch station catalog
         */
        async fetchStations() {
            try {
                const res = await kitchenService.getStations();
                const data = res.data || res || [];
                this.stations = Array.isArray(data) ? data : [];
            } catch (err) {
                console.warn('Failed to fetch stations:', err);
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Kitchen Workflow Actions (Sprint 05)
        |--------------------------------------------------------------------------
        */

        /**
         * Accept order batch into cooking
         */
        async acceptBatch(batchId) {
            const key = `batch-${batchId}`;
            if (this.isActionPending(key)) return;

            this.setActionPending(key, true);
            try {
                const res = await kitchenActionService.acceptBatch(batchId);
                const updatedBatch = res.data || res;

                // Sync local batch status
                const existing = this.orders.find((o) => o.id === batchId);
                if (existing) {
                    existing.status = 'cooking';
                }
                if (this.selectedOrder && this.selectedOrder.id === batchId) {
                    this.selectedOrder.status = 'cooking';
                }

                // Refresh silent queue and detail
                await this.fetchOrders(true);
                return { success: true, data: updatedBatch };
            } catch (err) {
                const message = err.customMessage || err.response?.data?.message || 'Gagal menerima order.';
                return { success: false, message };
            } finally {
                this.setActionPending(key, false);
            }
        },

        /**
         * Mark item as preparing / cooking
         */
        async prepareItem(itemId) {
            const key = `item-${itemId}`;
            if (this.isActionPending(key)) return;

            this.setActionPending(key, true);
            try {
                const res = await kitchenActionService.prepareItem(itemId);
                const updatedItem = res.data || res;

                this.syncItemState(itemId, 'preparing');
                await this.fetchOrders(true);
                return { success: true, data: updatedItem };
            } catch (err) {
                const message = err.customMessage || err.response?.data?.message || 'Gagal memulai memasak item.';
                return { success: false, message };
            } finally {
                this.setActionPending(key, false);
            }
        },

        /**
         * Mark item as ready
         */
        async readyItem(itemId) {
            const key = `item-${itemId}`;
            if (this.isActionPending(key)) return;

            this.setActionPending(key, true);
            try {
                const res = await kitchenActionService.readyItem(itemId);
                const updatedItem = res.data || res;

                this.syncItemState(itemId, 'ready');
                await this.fetchOrders(true);
                return { success: true, data: updatedItem };
            } catch (err) {
                const message = err.customMessage || err.response?.data?.message || 'Gagal menandai item siap saji.';
                return { success: false, message };
            } finally {
                this.setActionPending(key, false);
            }
        },

        /**
         * Mark item as served
         */
        async serveItem(itemId) {
            const key = `item-${itemId}`;
            if (this.isActionPending(key)) return;

            this.setActionPending(key, true);
            try {
                const res = await kitchenActionService.serveItem(itemId);
                const updatedItem = res.data || res;

                this.syncItemState(itemId, 'served');
                await this.fetchOrders(true);
                return { success: true, data: updatedItem };
            } catch (err) {
                const message = err.customMessage || err.response?.data?.message || 'Gagal menandai item disajikan.';
                return { success: false, message };
            } finally {
                this.setActionPending(key, false);
            }
        },

        /**
         * Helper to update item status locally in selectedOrder and queue
         */
        syncItemState(itemId, newStatus) {
            if (this.selectedOrder && Array.isArray(this.selectedOrder.items)) {
                const item = this.selectedOrder.items.find((i) => i.id === itemId);
                if (item) {
                    item.status = newStatus;
                }
            }

            for (const order of this.orders) {
                if (Array.isArray(order.items)) {
                    const item = order.items.find((i) => i.id === itemId);
                    if (item) {
                        item.status = newStatus;
                    }
                }
            }

            // Immediately remove completed orders where all items are served or cancelled
            this.orders = this.orders.filter((order) => {
                if (!Array.isArray(order.items) || order.items.length === 0) return true;
                return order.items.some((i) => {
                    const s = (i.status || '').toLowerCase();
                    return s !== 'served' && s !== 'cancelled';
                });
            });
        },
    },
});

export default useKitchenStore;
