<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public function logActivity($actionType, $modelType, $description = null, $companyName = null, $fileCreatedAt = null)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'model_type' => $modelType,
            'company_name' => $companyName,
            'description' => $description,
            'file_created_at' => $fileCreatedAt,
        ]);
    }
}
