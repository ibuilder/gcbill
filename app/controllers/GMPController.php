<?php

namespace App\Controllers;

use App\Database;
// Add necessary models: GmpSetting, GmpSov, Project etc.

class GMPController extends BaseController
{
    protected string $controllerName = 'GMP'; // For permissions

    // Add model properties if needed
    // private $gmpSettingModel;
    // private $gmpSovModel;
    // private $projectModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Instantiate models
        // $this->projectModel = new Project($this->db);
        // $this->gmpSettingModel = new GmpSetting($this->db);
        // $this->gmpSovModel = new GmpSov($this->db);
    }

    /**
     * Show GMP Setup page for a project.
     */
    public function setup(int $projectId): void
    {
        $this->actionName = 'setup';
        $this->requirePermission();

        // TODO: Fetch project
        // TODO: Fetch GMP settings for the project

        $this->render('gmp/setup', [ // Use .php
            'pageTitle' => 'GMP Setup', // Add project name?
            'activeNav' => 'gmp-setup', // Or link from project view?
            'projectId' => $projectId,
            // 'project' => $project,
            // 'gmpData' => $gmpData,
            'errors' => $_SESSION['errors'] ?? [],
            'formData' => $_SESSION['form_data'] ?? [] // For repopulation
        ]);
         unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    /**
     * Update GMP Setup for a project.
     */
    public function updateSetup(int $projectId): void
    {
        $this->actionName = 'updateSetup'; // Map to 'setup' permission?
        $this->requirePermission();

        if (!$this->checkCsrf('/gmp/setup/' . $projectId)) return;

        // TODO: Fetch project
        // TODO: Validate input data from $_POST['gmp']
        // TODO: Update or create GMP settings in the database

        $data = $_POST['gmp'] ?? [];
        $errors = [];
        // --- Validation ---
        // Example: if (!is_numeric($data['original_gmp_amount'] ?? null)) $errors['original_gmp_amount'] = 'Must be numeric.';
        // --- End Validation ---

        if (!empty($errors)) {
             $_SESSION['form_data'] = $data;
             $_SESSION['errors'] = $errors;
             $this->setFlashMessage('error', 'Please correct the errors.');
             $this->redirect('/gmp/setup/' . $projectId);
             return;
        }

        // Prepare data (types, nulls)
        // $gmpData = [...]

        // if ($this->gmpSettingModel->saveForProject($projectId, $gmpData)) {
        //     $this->setFlashMessage('success', 'GMP settings updated.');
        //     $this->redirect('/gmp/setup/' . $projectId);
        // } else {
        //     $_SESSION['form_data'] = $data;
        //     $this->setFlashMessage('error', 'Failed to update GMP settings.');
        //     $this->redirect('/gmp/setup/' . $projectId);
        // }
         $this->setFlashMessage('warning', 'GMP Update logic not fully implemented.'); // Placeholder
         $this->redirect('/gmp/setup/' . $projectId); // Placeholder
    }

    /**
     * Show GMP SOV page for a project.
     */
    public function sov(int $projectId): void
    {
        $this->actionName = 'sov';
        $this->requirePermission();

        // TODO: Fetch project
        // TODO: Fetch SOV items for the project (consider using SOVController::getForProject logic if applicable)

        $this->render('gmp/sov', [ // Use .php
            'pageTitle' => 'GMP Schedule of Values',
            'activeNav' => 'gmp-sov',
            'projectId' => $projectId,
            // 'project' => $project,
            // 'sovItems' => $sovItems // Data might be loaded via AJAX instead
        ]);
    }

     /**
     * Show GMP Tracking page for a project.
     */
    public function tracking(int $projectId): void
    {
        $this->actionName = 'tracking';
        $this->requirePermission();

        // TODO: Fetch project
        // TODO: Fetch GMP settings
        // TODO: Fetch SOV summary
        // TODO: Fetch Change Orders summary
        // TODO: Fetch Actual Costs summary
        // TODO: Calculate GMP tracking summary data

        $this->render('gmp/tracking', [ // Use .php
            'pageTitle' => 'GMP Tracking',
            'activeNav' => 'gmp-tracking',
            'projectId' => $projectId,
            // 'project' => $project,
            // 'gmpSummary' => $summaryData,
            // 'costDetails' => $detailedCosts // Optional detailed breakdown
        ]);
    }

    // Add methods for managing GMP SOV items if not handled by SOVController
    // e.g., createSovItem, storeSovItem, editSovItem, updateSovItem, deleteSovItem
}