<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\SovController.php
<?php

namespace App\Controllers;

use App\Database;
use App\Models\Sov; // Correct model name casing
use App\Models\Project; // Need project model to verify project exists
use App\Helpers\SecurityHelper;
// Auth check might be handled in BaseController or here if specific logic needed

class SOVController extends BaseController
{
    protected string $controllerName = 'SOV'; // For permissions

    private Sov $sovModel;
    private Project $projectModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Auth check - ensure user is logged in for all AJAX SOV actions
        if (!$this->auth->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        $this->sovModel = new Sov($this->db);
        $this->projectModel = new Project($this->db);
    }

    /**
     * Get all SOV items for a project (typically called via AJAX).
     */
    public function getForProject(int $projectId): void
    {
        $this->actionName = 'getForProject'; // Or map to 'view' permission
        // $this->requirePermission(); // Add permission check if needed

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Project not found.'], 404);
            return;
        }

        // TODO: Check if user has permission for this specific project

        $sovItems = $this->sovModel->findByProjectId($projectId);
        $totalValue = $this->sovModel->getTotalScheduledValue($projectId);

        $this->jsonResponse([
            'success' => true,
            'items' => $sovItems,
            'totalScheduledValue' => $totalValue,
            'contractAmount' => (float)($project['contract_amount'] ?? 0.0) // Send contract amount for comparison
        ]);
    }

    /**
     * Store a new SOV item (typically called via AJAX).
     */
    public function store(): void
    {
        $this->actionName = 'store'; // Map to 'create' permission
        // $this->requirePermission();

        if (!$this->checkCsrfAjax()) return;

        $data = $_POST;
        // Validation
        if (empty($data['project_id']) || !isset($data['item_number']) || empty($data['description']) || !isset($data['scheduled_value'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields (Project ID, Item #, Description, Value).'], 400);
            return;
        }
        if (!is_numeric($data['scheduled_value'])) {
             $this->jsonResponse(['success' => false, 'message' => 'Scheduled Value must be a number.'], 400);
             return;
        }


        $projectId = (int)$data['project_id'];
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Project not found.'], 404);
            return;
        }
        // TODO: Check permission for this project

        if ($this->sovModel->itemNumberExists($projectId, $data['item_number'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Item number already exists for this project.'], 409); // 409 Conflict
            return;
        }

        // Prepare data (ensure types)
        $sovData = [
            'project_id' => $projectId,
            'item_number' => trim($data['item_number']),
            'description' => trim($data['description']),
            'scheduled_value' => (float)$data['scheduled_value']
        ];

        $newId = $this->sovModel->create($sovData);

        if ($newId) {
            $newItem = $this->sovModel->findById($newId); // Fetch the created item
            $totalValue = $this->sovModel->getTotalScheduledValue($projectId);
            $this->jsonResponse([
                'success' => true,
                'message' => 'SOV item added successfully.',
                'item' => $newItem, // Send back the newly created item data
                'totalScheduledValue' => $totalValue,
                'contractAmount' => (float)($project['contract_amount'] ?? 0.0)
            ], 201); // 201 Created
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to add SOV item.'], 500);
        }
    }

    /**
     * Update an existing SOV item (typically called via AJAX).
     */
    public function update(int $id): void
    {
        $this->actionName = 'update'; // Map to 'edit' permission
        // $this->requirePermission();

        if (!$this->checkCsrfAjax()) return;

        $sovItem = $this->sovModel->findById($id);
        if (!$sovItem) {
            $this->jsonResponse(['success' => false, 'message' => 'SOV item not found.'], 404);
            return;
        }

        $projectId = $sovItem['project_id'];
        // TODO: Check permission for this project

        $data = $_POST;

        // Validation
        $updateData = [];
        $errors = [];
        if (isset($data['item_number'])) {
            $updateData['item_number'] = trim($data['item_number']);
            if ($updateData['item_number'] === '') $errors[] = 'Item number cannot be empty.';
            // Check uniqueness if changed
            elseif ($updateData['item_number'] != $sovItem['item_number'] && $this->sovModel->itemNumberExists($projectId, $updateData['item_number'], $id)) {
                 $errors[] = 'Item number already exists for this project.';
            }
        }
        if (isset($data['description'])) {
             $updateData['description'] = trim($data['description']);
             if ($updateData['description'] === '') $errors[] = 'Description cannot be empty.';
        }
        if (isset($data['scheduled_value'])) {
            if ($data['scheduled_value'] === '' || !is_numeric($data['scheduled_value'])) {
                 $errors[] = 'Scheduled value must be a valid number.';
            } else {
                 $updateData['scheduled_value'] = (float)$data['scheduled_value'];
            }
        }

        if (!empty($errors)) {
             $this->jsonResponse(['success' => false, 'message' => implode(' ', $errors)], 400);
             return;
        }

        if (empty($updateData)) {
             $this->jsonResponse(['success' => false, 'message' => 'No data provided for update.'], 400);
             return;
        }


        $affectedRows = $this->sovModel->update($id, $updateData);

        if ($affectedRows >= 0) { // Allow 0 rows affected
            $updatedItem = $this->sovModel->findById($id); // Fetch updated item
            $totalValue = $this->sovModel->getTotalScheduledValue($projectId);
            $project = $this->projectModel->findById($projectId); // Re-fetch project for contract amount
            $this->jsonResponse([
                'success' => true,
                'message' => 'SOV item updated successfully.',
                'item' => $updatedItem,
                'totalScheduledValue' => $totalValue,
                'contractAmount' => (float)($project['contract_amount'] ?? 0.0)
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update SOV item.'], 500);
        }
    }

    /**
     * Delete an SOV item (typically called via AJAX).
     */
    public function delete(int $id): void
    {
        $this->actionName = 'delete';
        // $this->requirePermission();

        if (!$this->checkCsrfAjax()) return;

        $sovItem = $this->sovModel->findById($id);
        if (!$sovItem) {
            $this->jsonResponse(['success' => false, 'message' => 'SOV item not found.'], 404);
            return;
        }

        $projectId = $sovItem['project_id'];
        // TODO: Check permission for this project

        // Optional: Check if item is used in billings before deleting
        // if ($this->sovModel->isUsedInBilling($id)) {
        //     $this->jsonResponse(['success' => false, 'message' => 'Cannot delete this SOV item, it has been used in billings.'], 409); // 409 Conflict
        //     return;
        // }

        $affectedRows = $this->sovModel->delete($id);

        if ($affectedRows > 0) {
            $totalValue = $this->sovModel->getTotalScheduledValue($projectId);
            $project = $this->projectModel->findById($projectId);
            $this->jsonResponse([
                'success' => true,
                'message' => 'SOV item deleted successfully.',
                'totalScheduledValue' => $totalValue,
                'contractAmount' => (float)($project['contract_amount'] ?? 0.0)
            ]);
        } else {
            // Model might return specific error codes, or just generic failure
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete SOV item.'], 500);
        }
    }
}