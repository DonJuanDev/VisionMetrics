<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Company;
use App\Models\TrackingLink;
use App\Models\Webhook;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin gates
        Gate::define('manage-companies', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('view-admin-panel', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('impersonate-users', function (User $user) {
            return $user->isSuperAdmin();
        });

        // Company Admin gates
        Gate::define('manage-company-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-company-settings', function (User $user) {
            return $user->canManageCompanySettings();
        });

        // Resource-based gates
        Gate::define('view-tracking-link', function (User $user, TrackingLink $trackingLink) {
            return $user->belongsToCompany($trackingLink->company_id) || $user->isSuperAdmin();
        });

        Gate::define('update-tracking-link', function (User $user, TrackingLink $trackingLink) {
            return $user->belongsToCompany($trackingLink->company_id) || $user->isSuperAdmin();
        });

        Gate::define('delete-tracking-link', function (User $user, TrackingLink $trackingLink) {
            return $user->belongsToCompany($trackingLink->company_id) || $user->isSuperAdmin();
        });

        Gate::define('view-webhook', function (User $user, Webhook $webhook) {
            return $user->belongsToCompany($webhook->company_id) || $user->isSuperAdmin();
        });

        Gate::define('update-webhook', function (User $user, Webhook $webhook) {
            return $user->belongsToCompany($webhook->company_id) || $user->isSuperAdmin();
        });

        Gate::define('delete-webhook', function (User $user, Webhook $webhook) {
            return $user->belongsToCompany($webhook->company_id) || $user->isSuperAdmin();
        });

        // Company-scoped permissions
        Gate::define('view-company-data', function (User $user, int $companyId) {
            return $user->belongsToCompany($companyId) || $user->isSuperAdmin();
        });

        Gate::define('manage-conversions', function (User $user) {
            return $user->canManageConversations();
        });

        Gate::define('view-reports', function (User $user) {
            return $user->canViewReports();
        });

        Gate::define('export-data', function (User $user) {
            return $user->canViewReports();
        });
    }
}
