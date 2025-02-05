<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

use Spatie\Activitylog\Models\Activity;

class ActivitylogController extends Controller
{
    public function index()
    {
        $pageTitle="Activity";
//        $logs = ActivityLog::paginate(20);

        $logs = Activity::paginate(20);


        // Group the paginated logs by `role_id` after fetching them
        $groupedLogs = $logs->groupBy('role_id');
//        return $groupedLogs;
        return view('admin.activity', compact('groupedLogs', 'logs','pageTitle'));
    }
}
