import dayjs from 'dayjs';
import 'dayjs/locale/id';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.locale('id');
dayjs.extend(relativeTime);

/**
 * Format number to Indonesian Rupiah (Rp)
 * @param {number|string} amount
 * @param {boolean} showPrefix
 * @returns {string}
 */
export function formatCurrency(amount, showPrefix = true) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return showPrefix ? 'Rp 0' : '0';
    }

    const num = Number(amount);
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);

    return showPrefix ? `Rp ${formatted}` : formatted;
}

/**
 * Format decimal or integer
 * @param {number|string} val
 * @param {number} decimals
 * @returns {string}
 */
export function formatNumber(val, decimals = 0) {
    if (val === null || val === undefined || isNaN(val)) return '0';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(Number(val));
}

/**
 * Format standard date
 * @param {string|Date} date
 * @param {string} pattern
 * @returns {string}
 */
export function formatDate(date, pattern = 'DD MMM YYYY') {
    if (!date) return '-';
    return dayjs(date).format(pattern);
}

/**
 * Format time
 * @param {string|Date} date
 * @param {string} pattern
 * @returns {string}
 */
export function formatTime(date, pattern = 'HH:mm') {
    if (!date) return '-';
    return dayjs(date).format(pattern);
}

/**
 * Format datetime
 * @param {string|Date} date
 * @param {string} pattern
 * @returns {string}
 */
export function formatDateTime(date, pattern = 'DD MMM YYYY, HH:mm') {
    if (!date) return '-';
    return dayjs(date).format(pattern);
}

/**
 * Relative time from now (e.g. "5 menit yang lalu")
 * @param {string|Date} date
 * @returns {string}
 */
export function formatRelativeTime(date) {
    if (!date) return '-';
    return dayjs(date).fromNow();
}

