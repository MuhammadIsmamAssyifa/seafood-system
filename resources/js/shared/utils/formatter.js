/**
 * Format currency to IDR
 */
export const formatCurrency = (value) => {
    const num = Number(value) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

/**
 * Format date to standard readable time HH:mm:ss or HH:mm
 */
export const formatTime = (date, withSeconds = false) => {
    if (!date) return '-';
    const d = new Date(date);
    if (isNaN(d.getTime())) return '-';

    return d.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        ...(withSeconds ? { second: '2-digit' } : {}),
        hour12: false,
    });
};

/**
 * Format full date and time
 */
export const formatDateTime = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    if (isNaN(d.getTime())) return '-';

    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

/**
 * Calculate elapsed time string since given timestamp
 * Output format: "4m 12s" or "1h 10m"
 */
export const formatElapsedTime = (timestamp) => {
    if (!timestamp) return '0m';
    const start = new Date(timestamp).getTime();
    if (isNaN(start)) return '0m';

    const now = Date.now();
    const diffSeconds = Math.max(0, Math.floor((now - start) / 1000));

    const hours = Math.floor(diffSeconds / 3600);
    const minutes = Math.floor((diffSeconds % 3600) / 60);
    const seconds = diffSeconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m ${seconds}s`;
};

/**
 * Format timestamp into digital timer "MM:SS" or "HH:MM:SS"
 */
export const formatTimerDigital = (timestamp) => {
    if (!timestamp) return '00:00';
    const start = new Date(timestamp).getTime();
    if (isNaN(start)) return '00:00';

    const now = Date.now();
    const diffSeconds = Math.max(0, Math.floor((now - start) / 1000));

    const hours = Math.floor(diffSeconds / 3600);
    const minutes = Math.floor((diffSeconds % 3600) / 60);
    const seconds = diffSeconds % 60;

    const pad = (n) => String(n).padStart(2, '0');
    if (hours > 0) {
        return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }
    return `${pad(minutes)}:${pad(seconds)}`;
};

/**
 * Format quantity with optional unit
 */
export const formatQuantity = (qty, unit = '') => {
    const num = Number(qty) || 0;
    const formatted = Number.isInteger(num) ? num.toString() : num.toFixed(2);
    return unit ? `${formatted} ${unit}` : formatted;
};

