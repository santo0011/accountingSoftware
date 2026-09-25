<?php

/*
|--------------------------------------------------------------------------
| Roles & permissions
|--------------------------------------------------------------------------
| Permissions are named "<module>.<action>". The seeder creates them and the
| default roles below; roles can be edited afterwards in Admin → Roles.
*/

return [

    'super_admin_role' => 'super-admin',

    'modules' => [
        'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
        'customers' => ['label' => 'Customers', 'actions' => ['view', 'create', 'edit', 'delete']],
        'leads' => ['label' => 'Leads', 'actions' => ['view', 'view_all', 'create', 'edit', 'delete']],
        'categories' => ['label' => 'Service Categories', 'actions' => ['manage']],
        'services' => ['label' => 'Services', 'actions' => ['view', 'manage']],
        'applications' => ['label' => 'Applications', 'actions' => ['view', 'view_all', 'create', 'update', 'assign', 'delete']],
        'documents' => ['label' => 'Documents', 'actions' => ['view', 'verify', 'upload_final']],
        'payments' => ['label' => 'Payments', 'actions' => ['view', 'manage', 'refund']],
        'invoices' => ['label' => 'Invoices', 'actions' => ['view', 'manage']],
        'staff' => ['label' => 'Staff', 'actions' => ['view', 'manage']],
        'professionals' => ['label' => 'Professionals', 'actions' => ['view', 'manage']],
        'roles' => ['label' => 'Roles & Permissions', 'actions' => ['manage']],
        'tasks' => ['label' => 'Tasks', 'actions' => ['view', 'view_all', 'manage']],
        'compliance' => ['label' => 'Compliance', 'actions' => ['view', 'view_all', 'manage']],
        'support' => ['label' => 'Support Tickets', 'actions' => ['view', 'view_all', 'reply', 'manage']],
        'reports' => ['label' => 'Reports', 'actions' => ['view']],
        'cms' => ['label' => 'Website Content', 'actions' => ['manage']],
        'settings' => ['label' => 'Settings', 'actions' => ['manage']],
        'audit' => ['label' => 'Audit Log', 'actions' => ['view']],
    ],

    // Default permissions per role. '*' = every permission.
    'roles' => [
        'super-admin' => ['label' => 'Super Admin', 'permissions' => ['*']],
        'admin' => ['label' => 'Admin', 'permissions' => ['*', '!roles.manage']],
        'staff' => ['label' => 'Staff', 'permissions' => [
            'dashboard.view', 'customers.view', 'customers.create', 'customers.edit',
            'leads.view', 'leads.create', 'leads.edit', 'services.view',
            'applications.view', 'applications.create', 'applications.update',
            'documents.view', 'documents.verify', 'documents.upload_final',
            'payments.view', 'invoices.view', 'tasks.view', 'tasks.manage',
            'compliance.view', 'compliance.manage', 'support.view', 'support.reply',
        ]],
        'accountant' => ['label' => 'Accountant', 'permissions' => [
            'dashboard.view', 'customers.view', 'services.view', 'applications.view', 'applications.view_all',
            'payments.view', 'payments.manage', 'payments.refund', 'invoices.view', 'invoices.manage',
            'tasks.view', 'tasks.manage', 'reports.view', 'support.view', 'support.reply',
        ]],
        'hr' => ['label' => 'HR', 'permissions' => [
            'dashboard.view', 'customers.view', 'services.view', 'applications.view',
            'applications.update', 'documents.view', 'documents.verify', 'documents.upload_final',
            'staff.view', 'tasks.view', 'tasks.manage', 'compliance.view', 'compliance.manage',
        ]],
        'legal-professional' => ['label' => 'Legal Professional', 'permissions' => [
            'dashboard.view', 'applications.view', 'applications.update', 'documents.view',
            'documents.verify', 'documents.upload_final', 'tasks.view', 'tasks.manage',
        ]],
        'tax-professional' => ['label' => 'Tax Professional', 'permissions' => [
            'dashboard.view', 'applications.view', 'applications.update', 'documents.view',
            'documents.verify', 'documents.upload_final', 'tasks.view', 'tasks.manage', 'compliance.view', 'compliance.manage',
        ]],
        'customer' => ['label' => 'Customer', 'permissions' => []],
    ],
];
