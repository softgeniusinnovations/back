<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class News extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = "newses";

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'Bonus';


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Bonus')
//            ->withProperties([
//                'role_name' => $this->getRoleName((integer) auth()->user()->type)
//            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $roleName = $this->getRoleName((integer) auth()->user()->type);
                $name=auth()->user()->name;
                return "Bonus has been {$eventName} by {$name} with role: {$roleName}";
            });
    }

    protected function getRoleName(int $roleId): string
    {
        $roleName = DB::table('roles')
            ->where('id', $roleId)
            ->value('name');

        return $roleName ?? 'Unknown Role';
    }

}
