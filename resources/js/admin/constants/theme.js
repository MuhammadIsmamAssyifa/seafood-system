export const THEME_COLORS = {
    primary: {
        50: '#eff6ff',
        100: '#dbeafe',
        500: '#3b82f6',
        600: '#2563eb',
        700: '#1d4ed8',
    },
    slate: {
        800: '#1e293b',
        900: '#0f172a',
    },
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#3b82f6',
};

export const ORDER_STATUS_LABELS = {
    draft: { label: 'Draft', color: 'bg-slate-100 text-slate-700 border-slate-200' },
    submitted: { label: 'Submitted', color: 'bg-amber-50 text-amber-700 border-amber-200' },
    preparing: { label: 'Diproses', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    ready: { label: 'Siap Saji', color: 'bg-purple-50 text-purple-700 border-purple-200' },
    completed: { label: 'Selesai', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    cancelled: { label: 'Batal', color: 'bg-rose-50 text-rose-700 border-rose-200' },
};

