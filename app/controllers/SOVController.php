<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\SovController.php
<?php

namespace App\Controllers;

use App\Controller;
use App\Models\Sov;
use App\Models\Project; // Need project model to verify project exists
use App\Helpers\SecurityHelper;

class SovController extends Controller {

    private Sov $sovModel;
    private Project $projectModel;

    public function __construct() {
        parent::__construct();
        if (!$this->auth->isLoggedIn()) {
            // For AJAX, returning an error might be better than redirecting
            $this->jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
            exit; // Stop execution
        }
        $this->sovModel = new Sov($this->db);
        $this->projectModel = new Project($this->db);
    }

    /**
     * Get all SOV items for a project (typically called via AJAX).
     */
    public function getForProject(int $projectId): void {
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Project not found.'], 404);
            return;
        }

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
    public function store(): void {
        // Basic CSRF check (token should be sent with AJAX request)
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $this->jsonResponse(['success' => false, 'message' => 'Invalid request token.'], 403);
             return;
        }

        $data = $_POST;
        // Validation
        if (empty($data['project_id']) || !isset($data['item_number']) || empty($data['description']) || !isset($data['scheduled_value'])) {
             $this->jsonResponse(['success' => false, 'message' => 'Missing required fields (Project ID, Item #, Description, Value).'], 400);
             return;
        }

        $projectId = (int)$data['project_id'];
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->jsonResponse(['success' => false, 'message' => 'Project not found.'], 404);
            return;
        }

        if ($this->sovModel->itemNumberExists($projectId, $data['item_number'])) {
             $this->jsonResponse(['success' => false, 'message' => 'Item number already exists for this project.'], 409); // 409 Conflict
             return;
        }

        $newId = $this->sovModel->create($data);

        if ($newId) {
            $newItem = $this->sovModel->findById((int)$newId); // Fetch the created item
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
     * Expects data via PUT or POST with _method=PUT.
     * For simplicity, we'll use POST here.
     */
    public function update(int $id): void {
         // Basic CSRF check
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $this->jsonResponse(['success' => false, 'message' => 'Invalid request token.'], 403);
             return;
        }

        $sovItem = $this->sovModel->findById($id);
        if (!$sovItem) {
            $this->jsonResponse(['success' => false, 'message' => 'SOV item not found.'], 404);
            return;
        }

        $data = $_POST;
        $projectId = $sovItem['project_id']; // Use original project ID

        // Validation (ensure required fields aren't emptied if they are required)
        if (isset($data['item_number']) && $data['item_number'] === '') {
             $this->jsonResponse(['success' => false, 'message' => 'Item number cannot be empty.'], 400); return;
        }
         if (isset($data['description']) && empty($data['description'])) {
             $this->jsonResponse(['success' => false, 'message' => 'Description cannot be empty.'], 400); return;
        }
         if (isset($data['scheduled_value']) && $data['scheduled_value'] === '') {
             $this->jsonResponse(['success' => false, 'message' => 'Scheduled value cannot be empty.'], 400); return;
        }

        // Check uniqueness if item number changed
        if (isset($data['item_number']) && $data['item_number'] != $sovItem['item_number']) {
             if ($this->sovModel->itemNumberExists($projectId, $data['item_number'], $id)) {
                 $this->jsonResponse(['success' => false, 'message' => 'Item number already exists for this project.'], 409);
                 return;
             }
        }

        $affectedRows = $this->sovModel->update($id, $data);

        if ($affectedRows >= 0) {
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
     * Expects data via DELETE or POST with _method=DELETE.
     * For simplicity, we'll use POST here.
     */
    public function delete(int $id): void {
         // Basic CSRF check
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $this->jsonResponse(['success' => false, 'message' => 'Invalid request token.'], 403);
             return;
        }

        $sovItem = $this->sovModel->findById($id);
        if (!$sovItem) {
            $this->jsonResponse(['success' => false, 'message' => 'SOV item not found.'], 404);
            return;
        }

        $projectId = $sovItem['project_id'];
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
        } elseif ($affectedRows === -1) {
             // Specific error from model (e.g., cannot delete if billed)
             $this->jsonResponse(['success' => false, 'message' => 'Cannot delete this SOV item, it may have been used in billings.'], 409); // 409 Conflict
        }
        else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete SOV item.'], 500);
        }
    }

    /**
     * Helper to send JSON responses.
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}