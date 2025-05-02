<?php

namespace App\Controllers; // Correct namespace

use App\Database;
// Add necessary models: GeneralCondition, Project

class GeneralConditionsController extends BaseController
{
    protected string $controllerName = 'GeneralConditions'; // For permissions

    // Add model properties if needed
    // private $gcModel;
    // private $projectModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Instantiate models
        // $this->projectModel = new Project($this->db);
        // $this->gcModel = new GeneralCondition($this->db);
    }

    /**
     * List General Conditions items for a project.
     */
    public function index(int $projectId): void
    {
        $this->actionName = 'index';
        $this->requirePermission();

        // TODO: Fetch project
        // TODO: Fetch General Conditions items for the project

        $this->render('general_conditions/index', [ // Adjust template path, use .php
            'pageTitle' => 'General Conditions', // Add project name?
            'activeNav' => 'general-conditions', // Or link from project view?
            'projectId' => $projectId,
            // 'project' => $project,
            // 'gcItems' => $gcItems
        ]);
    }

    // Add create, store, edit, update, delete methods as needed
    // These might involve AJAX similar to SOVController depending on UI design
}