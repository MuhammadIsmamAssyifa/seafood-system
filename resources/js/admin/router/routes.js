export const routes = [
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/auth/LoginPage.vue'),
        meta: {
            layout: 'auth',
            requiresGuest: true,
            title: 'Login Admin & Owner',
        },
    },
    {
        path: '/403',
        name: 'unauthorized',
        component: () => import('../pages/auth/UnauthorizedPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            title: 'Akses Ditolak',
        },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../pages/dashboard/DashboardPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'dashboard.view',
            title: 'Dashboard Overview',
        },
    },
    {
        path: '/reporting',
        name: 'reporting',
        component: () => import('../pages/report/SalesReportPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'report.view',
            title: 'Laporan Penjualan',
        },
    },
    {
        path: '/orders',
        name: 'orders',
        component: () => import('../pages/orders/OrderPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'order.view',
            title: 'Manajemen Pesanan',
        },
    },
    {
        path: '/orders/:id',
        name: 'order-detail',
        component: () => import('../pages/orders/OrderDetailPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'order.view',
            title: 'Rincian Pesanan',
        },
    },
    {
        path: '/inventory',
        name: 'inventory',
        component: () => import('../pages/inventory/InventoryPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.view',
            title: 'Stok Bahan Baku (Inventory)',
        },
    },
    {
        path: '/inventory/cost',
        name: 'inventory-cost',
        component: () => import('../pages/inventory-cost/InventoryCostPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.view',
            title: 'Valuasi Biaya Bahan (Batch Cost)',
        },
    },
    {
        path: '/inventory/cost/:id',
        name: 'inventory-cost-detail',
        component: () => import('../pages/inventory-cost/InventoryCostDetailPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.view',
            title: 'Rincian Batch Cost',
        },
    },
    {
        path: '/inventory/:id',
        name: 'inventory-detail',
        component: () => import('../pages/inventory/InventoryDetailPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.view',
            title: 'Rincian Stok Bahan',
        },
    },
    {
        path: '/waste',
        name: 'waste',
        component: () => import('../pages/waste/WastePage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.manage',
            title: 'Waste Management (Bahan Terbuang)',
        },
    },
    {
        path: '/waste/:id',
        name: 'waste-detail',
        component: () => import('../pages/waste/WasteDetailPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'inventory.manage',
            title: 'Rincian Catatan Waste',
        },
    },
    {
        path: '/employees',
        name: 'employees',
        component: () => import('../pages/employees/EmployeePage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'employee.view|user.view',
            title: 'Employee Management (Karyawan)',
        },
    },
    {
        path: '/employees/:id',
        name: 'employee-detail',
        component: () => import('../pages/employees/EmployeeDetailPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'employee.view|user.view',
            title: 'Rincian Karyawan',
        },
    },
    {
        path: '/roles',
        name: 'roles',
        component: () => import('../pages/roles/RolePage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'role.view|user.manage',
            title: 'Role & Hak Akses (Permissions)',
        },
    },
    {
        path: '/audit-logs',
        name: 'audit-logs',
        component: () => import('../pages/audit/AuditPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'audit.view|report.view|user.manage',
            title: 'Audit Trail & Aktivitas Sistem',
        },
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('../pages/settings/SettingsPage.vue'),
        meta: {
            layout: 'default',
            requiresAuth: true,
            permission: 'settings.view|user.manage',
            title: 'Pengaturan Sistem (System Settings)',
        },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard',
    },
];

export default routes;

