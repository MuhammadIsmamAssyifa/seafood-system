/**
 * Determine timer status based on age in minutes (Sprint 07: 5m warning, 10m danger)
 * @param {string|Date} createdAt
 * @param {number} warningThresholdMinutes
 * @param {number} dangerThresholdMinutes
 * @returns {'normal' | 'warning' | 'danger'}
 */
export const getOrderAgeStatus = (createdAt, warningThresholdMinutes = 5, dangerThresholdMinutes = 10) => {
    if (!createdAt) return 'normal';
    const start = new Date(createdAt).getTime();
    if (isNaN(start)) return 'normal';

    const diffMinutes = (Date.now() - start) / 60000;

    if (diffMinutes >= dangerThresholdMinutes) {
        return 'danger';
    }
    if (diffMinutes >= warningThresholdMinutes) {
        return 'warning';
    }
    return 'normal';
};

/**
 * Debounce helper
 */
export const debounce = (func, wait = 300) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

/**
 * Truncate string with ellipsis
 */
export const truncate = (text, maxLength = 30) => {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

/**
 * Play kitchen audio beep for new orders or notifications
 */
export const playNotificationTone = (type = 'new-order') => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        if (type === 'new-order') {
            // High double beep
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            osc.start();
            osc.frequency.setValueAtTime(1174.66, audioCtx.currentTime + 0.15); // D6
            osc.stop(audioCtx.currentTime + 0.35);
        } else if (type === 'alert') {
            // Warning tone
            osc.frequency.setValueAtTime(440, audioCtx.currentTime); // A4
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.4);
        } else {
            // Pleasant click
            osc.frequency.setValueAtTime(600, audioCtx.currentTime);
            gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.1);
        }
    } catch {
        // AudioContext might be blocked until user interacts
    }
};

