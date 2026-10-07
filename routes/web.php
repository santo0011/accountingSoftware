<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\Site;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::name('site.')->group(function () {
    Route::get('/', Site\HomeController::class)->name('home');
    Route::get('/services', [Site\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/search', [Site\ServiceController::class, 'search'])->name('services.search')->middleware('throttle:60,1');
    Route::get('/services/category/{category:slug}', [Site\ServiceController::class, 'category'])->name('categories.show');
    Route::get('/services/{service:slug}', [Site\ServiceController::class, 'show'])->name('services.show');

    Route::get('/about-us', [Site\PageController::class, 'about'])->name('about');
    Route::get('/pricing', [Site\PageController::class, 'pricing'])->name('pricing');
    Route::get('/faq', [Site\PageController::class, 'faq'])->name('faq');
    Route::get('/contact', [Site\PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [Site\ContactController::class, 'store'])->name('contact.store')->middleware('throttle:forms');
    Route::post('/callback-request', [Site\ContactController::class, 'callback'])->name('callback.store')->middleware('throttle:forms');
    Route::get('/privacy-policy', [Site\PageController::class, 'show'])->defaults('slug', 'privacy-policy')->name('privacy');
    Route::get('/terms-and-conditions', [Site\PageController::class, 'show'])->defaults('slug', 'terms-and-conditions')->name('terms');
    Route::get('/refund-policy', [Site\PageController::class, 'show'])->defaults('slug', 'refund-policy')->name('refund');

    Route::get('/sitemap.xml', [Site\SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [Site\SeoController::class, 'robots'])->name('robots');
});

Route::match(['get', 'post'], '/webhooks/payments/{gateway}/{payment}', WebhookController::class)
    ->name('webhooks.payments')->middleware('throttle:60,1');

Route::get('/dashboard', DashboardRedirectController::class)->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Customer portal
|--------------------------------------------------------------------------
*/
Route::prefix('account')->name('portal.')->middleware(['auth', 'verified', 'user.type:customer'])->group(function () {
    Route::get('/', Portal\DashboardController::class)->name('dashboard');
    Route::get('/services', [Portal\MyServicesController::class, 'index'])->name('services.index');

    Route::get('/apply/{service:slug}', [Portal\ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/apply/{service:slug}', [Portal\ApplicationController::class, 'store'])->name('applications.store')->middleware('throttle:uploads');
    Route::get('/applications', [Portal\ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [Portal\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/cancel', [Portal\ApplicationController::class, 'cancel'])->name('applications.cancel');
    Route::post('/applications/{application}/documents', [Portal\DocumentController::class, 'store'])->name('applications.documents.store')->middleware('throttle:uploads');

    Route::get('/documents', [Portal\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/download', [Portal\DocumentController::class, 'download'])->name('documents.download');

    Route::get('/payments', [Portal\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/applications/{application}/pay', [Portal\PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/applications/{application}/pay', [Portal\PaymentController::class, 'pay'])->name('payments.pay')->middleware('throttle:10,1');

    Route::get('/invoices', [Portal\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/pdf', [Portal\InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/compliance', [Portal\ComplianceController::class, 'index'])->name('compliance.index');

    Route::get('/notifications', [Portal\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [Portal\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [Portal\NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/support', [Portal\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/new', [Portal\SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [Portal\SupportController::class, 'store'])->name('support.store')->middleware('throttle:forms');
    Route::get('/support/{ticket}', [Portal\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [Portal\SupportController::class, 'reply'])->name('support.reply')->middleware('throttle:forms');
    Route::get('/support/attachments/{message}', [Portal\SupportController::class, 'attachment'])->name('support.attachment');

    Route::get('/profile', [Portal\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Portal\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Portal\ProfileController::class, 'password'])->name('profile.password');
    Route::post('/businesses', [Portal\BusinessController::class, 'store'])->name('businesses.store');
    Route::put('/businesses/{business}', [Portal\BusinessController::class, 'update'])->name('businesses.update');
    Route::delete('/businesses/{business}', [Portal\BusinessController::class, 'destroy'])->name('businesses.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin panel (staff & professionals). Permissions are enforced per controller.
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'user.type:backoffice', \App\Http\Middleware\RememberListUrl::class])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::get('/search', Admin\SearchController::class)->name('search');

    Route::resource('customers', Admin\CustomerController::class);
    Route::put('/subscriptions/{customerService}', [Admin\CustomerController::class, 'updateSubscription'])->name('subscriptions.update');

    Route::resource('leads', Admin\LeadController::class);
    Route::post('/leads/{lead}/followups', [Admin\LeadController::class, 'followup'])->name('leads.followups.store');
    Route::post('/leads/{lead}/convert', [Admin\LeadController::class, 'convert'])->name('leads.convert');

    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('services', Admin\ServiceController::class)->except('show');

    Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [Admin\ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [Admin\ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/status', [Admin\ApplicationController::class, 'status'])->name('applications.status');
    Route::post('/applications/{application}/assign', [Admin\ApplicationController::class, 'assign'])->name('applications.assign');
    Route::post('/applications/{application}/notes', [Admin\ApplicationController::class, 'note'])->name('applications.notes.store');
    Route::post('/applications/{application}/document-requests', [Admin\ApplicationController::class, 'requestDocument'])->name('applications.document-requests.store');
    Route::post('/applications/{application}/deliverables', [Admin\ApplicationController::class, 'deliverable'])->name('applications.deliverables.store');
    Route::post('/applications/{application}/payments', [Admin\PaymentController::class, 'record'])->name('applications.payments.store');
    Route::delete('/applications/{application}', [Admin\ApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('/documents', [Admin\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/view', [Admin\DocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/{document}/download', [Admin\DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/verify', [Admin\DocumentController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/{document}/reject', [Admin\DocumentController::class, 'reject'])->name('documents.reject');

    Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/confirm', [Admin\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/fail', [Admin\PaymentController::class, 'fail'])->name('payments.fail');
    Route::post('/payments/{payment}/refund', [Admin\PaymentController::class, 'refund'])->name('payments.refund');

    Route::get('/invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::resource('staff', Admin\StaffController::class)->except('show')->parameters(['staff' => 'user']);
    Route::resource('professionals', Admin\ProfessionalController::class)->except('show');
    Route::resource('roles', Admin\RoleController::class)->except('show');

    Route::resource('tasks', Admin\TaskController::class)->except('show');
    Route::post('/tasks/{task}/complete', [Admin\TaskController::class, 'complete'])->name('tasks.complete');

    Route::get('/compliance/types', [Admin\ComplianceTypeController::class, 'index'])->name('compliance-types.index');
    Route::post('/compliance/types', [Admin\ComplianceTypeController::class, 'store'])->name('compliance-types.store');
    Route::put('/compliance/types/{complianceType}', [Admin\ComplianceTypeController::class, 'update'])->name('compliance-types.update');
    Route::resource('compliance', Admin\ComplianceController::class)->except('show')->parameters(['compliance' => 'record']);
    Route::post('/compliance/{record}/complete', [Admin\ComplianceController::class, 'complete'])->name('compliance.complete');

    Route::get('/support', [Admin\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [Admin\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [Admin\SupportController::class, 'reply'])->name('support.reply');
    Route::put('/support/{ticket}', [Admin\SupportController::class, 'update'])->name('support.update');
    Route::get('/support/attachments/{message}', [Admin\SupportController::class, 'attachment'])->name('support.attachment');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{type}', [Admin\ReportController::class, 'export'])->name('reports.export');

    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [Admin\NotificationController::class, 'open'])->name('notifications.open');

    Route::resource('pages', Admin\PageController::class)->except('show');
    Route::resource('faqs', Admin\FaqController::class)->except('show');
    Route::resource('testimonials', Admin\TestimonialController::class)->except('show');

    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-mail', [Admin\SettingController::class, 'testMail'])->name('settings.test-mail')->middleware('throttle:6,1');
    Route::get('/audit-log', [Admin\AuditLogController::class, 'index'])->name('audit.index');

    Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Admin\ProfileController::class, 'password'])->name('profile.password');
});
