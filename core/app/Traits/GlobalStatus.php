<?php

namespace App\Traits;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait GlobalStatus {
    public static function changeStatus($id, $column = 'status') {
        $modelName = get_class();
        $query     = $modelName::findOrFail($id);
        if ($query->$column == Status::ENABLE) {
            $query->$column = Status::DISABLE;
        } else {
            $query->$column = Status::ENABLE;
        }
        $message = keyToTitle($column) . ' changed successfully';

        $query->save();
        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function statusBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->status == Status::ENABLE) {
                $html = '<span class="badge badge--success">' . trans('Enabled') . '</span>';
            } else {
                $html = '<span><span class="badge badge--warning">' . trans('Disabled') . '</span></span>';
            }

            return $html;
        });
    }

    // Attribute
    public function betStatusBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->status == Status::BET_WIN) {
                $html = '<span class="badge badge--success">' . trans('Won') . '</span>';
            } elseif ($this->status == Status::BET_PENDING) {
                $html = '<span class="badge badge--warning">' . trans('Pending') . '</span>';
            } elseif ($this->status == Status::BET_LOSE) {
                $html = '<span class="badge badge--danger">' . trans('Lost') . '</span>';
            } elseif ($this->status == Status::BET_REFUNDED) {
                $html = '<span class="badge badge--primary">' . trans('Refunded') . '</span>';
            }

            return $html;
        });
    }

    // Scope
    public function scopeActive($query) {
        return $query->where('status', Status::ENABLE);
    }

    public function scopeInactive($query) {
        return $query->where('status', Status::DISABLE);
    }

    // Scope for Bet
    public function scopePending($query) {
        return $query->where('status', Status::BET_PENDING);
    }

    public function scopeWon($query) {
        return $query->where('status', Status::BET_WIN);
    }

    public function scopeLose($query) {
        return $query->where('status', Status::BET_LOSE);
    }

    public function scopeRefunded($query) {
        return $query->where('status', Status::BET_REFUNDED);
    }
}
