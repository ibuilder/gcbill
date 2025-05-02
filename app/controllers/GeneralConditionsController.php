<?php

namespace AppControllers;

use App\Database;
use App\Controllers\BaseController;

class GeneralConditionsController extends \App\Controllers\BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}