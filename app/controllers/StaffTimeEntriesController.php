<?php

namespace AppControllers;

use App\Database;

class StaffTimeEntriesController extends BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function index($projectId)
    {   
        echo "Staff Time Entries Index for project {$projectId}";
    }

    public function view($projectId, $id)
    {
        echo "View Staff Time Entry {$id} for project {$projectId}";
    }
}