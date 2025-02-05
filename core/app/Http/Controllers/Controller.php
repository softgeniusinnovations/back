<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Laramin\Utility\Onumoti;
use Illuminate\Foundation\{
    Auth\Access\AuthorizesRequests,
    Bus\DispatchesJobs,
    Validation\ValidatesRequests,
};

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $activeTemplate;

    public function __construct() {
        $this->activeTemplate = activeTemplate();

        $className = get_called_class();
        Onumoti::mySite($this, $className);
    }
}
