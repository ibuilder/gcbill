<?php

namespace AppControllers;

use AppDatabase;

class GeneralConditionsController extends BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}