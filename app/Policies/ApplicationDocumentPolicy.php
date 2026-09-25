<?php

namespace App\Policies;

use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationDocumentPolicy
{
    public function download(User $user, ApplicationDocument $document): bool
    {
        if ($user->isCustomer()) {
            return $user->can('view', $document->application);
        }

        return $user->can('documents.view') && $user->can('view', $document->application);
    }

    public function review(User $user, ApplicationDocument $document): bool
    {
        return $user->isBackoffice() && $user->can('documents.verify')
            && ! $document->isDeliverable() && $user->can('view', $document->application);
    }
}
