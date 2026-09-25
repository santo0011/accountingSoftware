<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        if ($user->isCustomer()) {
            return $this->owns($user, $application);
        }

        return $user->can('applications.view') && $this->isVisibleToStaff($user, $application);
    }

    public function update(User $user, Application $application): bool
    {
        return $user->isBackoffice() && $user->can('applications.update') && $this->isVisibleToStaff($user, $application);
    }

    public function assign(User $user, Application $application): bool
    {
        return $user->isBackoffice() && $user->can('applications.assign');
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->isBackoffice() && $user->can('applications.delete');
    }

    /** Customer may pay while the application is open and unpaid. */
    public function pay(User $user, Application $application): bool
    {
        return $this->owns($user, $application) && ! $application->isPaid()
            && (float) $application->total > 0 && ! $application->status->isClosed();
    }

    /** Customer may upload while the application is still open. */
    public function upload(User $user, Application $application): bool
    {
        return $this->owns($user, $application) && ! $application->status->isClosed();
    }

    /** Customer may cancel only before work has started and before paying. */
    public function cancel(User $user, Application $application): bool
    {
        return $this->owns($user, $application) && ! $application->isPaid()
            && in_array($application->status, [ApplicationStatus::New, ApplicationStatus::DocumentsPending, ApplicationStatus::PaymentPending], true);
    }

    private function owns(User $user, Application $application): bool
    {
        return $user->isCustomer() && $user->customer?->id === $application->customer_id;
    }

    private function isVisibleToStaff(User $user, Application $application): bool
    {
        return $user->can('applications.view_all')
            || $application->assigned_staff_id === $user->id
            || ($user->professional && $application->assigned_professional_id === $user->professional->id);
    }
}
