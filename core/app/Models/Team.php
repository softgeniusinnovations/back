<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Team extends Model {
    use Searchable;
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function games() {
        return $this->hasMany(Game::class);
    }

    public function teamImage() {
        return getImage(getFilePath('team') . '/' . $this->image, getFileSize('team'));
    }
}
