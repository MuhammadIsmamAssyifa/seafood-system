/**
 * Get human readable status label and badge CSS
 * @param {string} status
 */
export function getStatusBadge(status) {
    switch (status?.toLowerCase()) {
        case 'completed':
            return { label: 'Selesai', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' };
        case 'preparing':
            return { label: 'Diproses', class: 'bg-blue-50 text-blue-700 border border-blue-200' };
        case 'submitted':
            return { label: 'Terkirim', class: 'bg-amber-50 text-amber-700 border border-amber-200' };
        case 'ready':
            return { label: 'Siap', class: 'bg-purple-50 text-purple-700 border border-purple-200' };
        case 'cancelled':
            return { label: 'Batal', class: 'bg-rose-50 text-rose-700 border border-rose-200' };
        case 'draft':
            return { label: 'Draft', class: 'bg-slate-100 text-slate-700 border border-slate-200' };
        default:
            return { label: status || 'Unknown', class: 'bg-slate-100 text-slate-600 border border-slate-200' };
    }
}

/**
 * Truncate long strings
 */
export function truncate(text, length = 30) {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
}

