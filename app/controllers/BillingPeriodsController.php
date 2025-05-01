php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\BillingPeriod;

class BillingPeriodsController extends Controller
{
    public function before()
    {
        // Check if the user is logged in
        if (!Auth::isLoggedIn()) {
            // Redirect to the login page if not logged in
            header('Location: /login');
            exit;
        }

        // Get the current user from the session
        $user = $_SESSION['user'];

        // Check if the user has permission to access the current controller and action
        if (!Auth::checkPermission($user, $this->controller, $this->action)) {
            // Redirect to a 403 error page if no permission
            header('Location: /403');
            exit;
        }
    }

    public function index($projectId)
    {
        $this->before();
        $billingPeriods = BillingPeriod::where('project_id', $projectId)->get();
        return json_encode(['project_id' => $projectId, 'billing_periods' => $billingPeriods]);
    }

    public function view($id)
    {
        $this->before();
        $billingPeriod = BillingPeriod::find($id);
        return json_encode(['billing_period' => $billingPeriod]);
    }

    public function create($data)
    {
        $this->before();
        $billingPeriod = new BillingPeriod($data);
        $billingPeriod->save();
        return json_encode(['id' => $billingPeriod->id]);
    }

    public function edit($id, $data)
    {
        $this->before();
        $billingPeriod = BillingPeriod::find($id);
        $billingPeriod->fill($data);
        $billingPeriod->save();
        return json_encode(['id' => $billingPeriod->id]);
    }

    public function delete($id)
    {
        $this->before();
        $billingPeriod = BillingPeriod::find($id);
        $billingPeriod->delete();
        return json_encode(['id' => $id]);
    }
    protected $controller = 'BillingPeriodsController';
}