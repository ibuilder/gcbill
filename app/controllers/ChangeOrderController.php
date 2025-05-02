<?php

namespace App\Controllers; // Correct namespace

use App\Database;
use App\Models\ChangeOrder;
use App\Models\ChangeOrderItem;
use App\Models\Project; // To verify project exists
use App\Helpers\SecurityHelper;
use Exception;
// Auth is likely handled by BaseController

class ChangeOrderController extends BaseController
{
    protected string $controllerName = 'ChangeOrder'; // For permissions

    private ChangeOrder $changeOrderModel;
    private ChangeOrderItem $changeOrderItemModel;
    private Project $projectModel;


    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Auth check handled by BaseController or requirePermission()
        $this->changeOrderModel = new ChangeOrder($this->db);
        $this->changeOrderItemModel = new ChangeOrderItem($this->db);
        $this->projectModel = new Project($this->db);
    }

    /**
     * List change orders for a project.
     * Could be a dedicated page or part of the project view.
     */
    public function index(int $projectId): void
    {
        $this->actionName = 'index';
        $this->requirePermission();

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects'); // Redirect to projects list
            return;
        }

        $changeOrders = $this->changeOrderModel->findByProjectId($projectId);

        $this->render('change_orders/list', [ // Adjust template path, use .php
            'pageTitle' => 'Change Orders for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects', // Keep project context
            'project' => $project,
            'changeOrders' => $changeOrders
        ]);
    }

    /**
     * Show form to create a new change order.
     */
    public function create(int $projectId): void
    {
        $this->actionName = 'create';
        $this->requirePermission();

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $nextCONumber = $this->changeOrderModel->getNextCONumber($projectId);

        $this->render('change_orders/create', [ // Adjust template path, use .php
            'pageTitle' => 'Create Change Order for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project,
            'changeOrder' => $_SESSION['form_data'] ?? ['change_order_number' => $nextCONumber, 'status' => 'draft'], // Repopulate form
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/projects/' . $projectId . '/change-orders/store'
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }


    /**
     * Store a new change order header. Items are added/edited separately.
     */
    public function store(int $projectId): void
    {
        $this->actionName = 'store'; // Map to 'create' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/projects/' . $projectId . '/change-orders/create')) return;

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $data = $_POST;
        $data['project_id'] = $projectId;

        $changeOrder = new ChangeOrder($data);

        if ($changeOrder->validate()) {
            $changeOrder->save();
            $this->setFlashMessage('success', 'Change order created successfully.');
            $this->redirect('/projects/' . $projectId . '/change-orders');
        } else {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $changeOrder->getErrors();
            $this->redirect('/projects/' . $projectId . '/change-orders/create');
        }
    }

    /**
     * Edit a change order.
     */
    public function edit(int $projectId, int $changeOrderId): void
    {
        $this->actionName = 'edit';
        $this->requirePermission();

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $changeOrder = $this->changeOrderModel->findById($changeOrderId);
        if (!$changeOrder) {
            $this->setFlashMessage('error', 'Change order not found.');
            $this->redirect('/projects/' . $projectId . '/change-orders');
            return;
        }

        $this->render('change_orders/edit', [ // Adjust template path, use .php
            'pageTitle' => 'Edit Change Order for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project,
            'changeOrder' => $_SESSION['form_data'] ?? $changeOrder->toArray(), // Repopulate form
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/projects/' . $projectId . '/change-orders/update/' . $changeOrderId
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    /**
     * Update a change order.
     */
    public function update(int $projectId, int $changeOrderId): void
    {
        $this->actionName = 'update'; // Map to 'edit' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/projects/' . $projectId . '/change-orders/edit/' . $changeOrderId)) return;

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $changeOrder = $this->changeOrderModel->findById($changeOrderId);
        if (!$changeOrder) {
            $this->setFlashMessage('error', 'Change order not found.');
            $this->redirect('/projects/' . $projectId . '/change-orders');
            return;
        }

        $data = $_POST;
        $data['project_id'] = $projectId;

        $changeOrder->fromArray($data);

        if ($changeOrder->validate()) {
            $changeOrder->save();
            $this->setFlashMessage('success', 'Change order updated successfully.');
            $this->redirect('/projects/' . $projectId . '/change-orders');
        } else {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $changeOrder->getErrors();
            $this->redirect('/projects/' . $projectId . '/change-orders/edit/' . $changeOrderId);
        }
    }

    /**
     * Delete a change order.
     */
    public function delete(int $projectId, int $changeOrderId): void
    {
        $this->actionName = 'delete';
        $this->requirePermission();

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $changeOrder = $this->changeOrderModel->findById($changeOrderId);
        if (!$changeOrder) {
            $this->setFlashMessage('error', 'Change order not found.');
            $this->redirect('/projects/' . $projectId . '/change-orders');
            return;
        }

        $changeOrder->delete();
        $this->setFlashMessage('success', 'Change order deleted successfully.');
        $this->redirect('/projects/' . $projectId . '/change-orders');
    }
}