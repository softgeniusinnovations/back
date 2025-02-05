<?php

namespace App\Observers;

use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;

class GlobalObserver
{
    public function created(Model $model)
    {
        if ($this->shouldLog('created', $model)) {
            $this->logActivity($model, 'created');
        }
    }

    public function updated(Model $model)
    {
        if ($this->shouldLog('updated', $model)) {
            $this->logActivity($model, 'updated', $model->getChanges());
        }
    }

    public function deleted(Model $model)
    {
        if ($this->shouldLog('deleted', $model)) {
            $this->logActivity($model, 'deleted');
        }
    }

    private function shouldLog(string $operation, Model $model): bool
    {
        // Skip models explicitly marked as not trackable
        if (property_exists($model, 'trackable') && !$model->trackable) {
            return false;
        }

        // Exclude certain routes
        $excludedRoutes = Config::get('activitylog.excluded_routes', []);
        $currentRouteName = Request::route()?->getName();

        return !in_array($currentRouteName, $excludedRoutes);
    }

    private function logActivity(Model $model, $operation, $changes = null)
    {
        $user = auth()->user();
        $role = Admin::where('id', $user?->id)->pluck('type')->first();

        Log::info("user");
        Log::info($user);
        Log::info("role");
        Log::info($role);



        ActivityLog::create([
            'model' => get_class($model),
            'model_id' => $model->getKey(),
            'user_id' => $user?->id,
            'role_id' => $role ?? null,
            'operation' => $operation,
            'changes' => $changes ? json_encode($changes) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'url' => Request::fullUrl(),
            'performed_at' => now(),
        ]);

        // Debugging logs (optional)
        Log::info("Activity logged: {$operation} on model " . get_class($model));
    }
}
