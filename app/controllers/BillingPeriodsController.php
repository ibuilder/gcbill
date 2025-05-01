php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\BillingPeriod;

class BillingPeriodsController extends Controller
{
    public function before()
    {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        if (!$auth->checkPermission($_SESSION['user'], get_class($this), $this->action)) {
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
}