<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Instance\AdminOverview;
use App\Services\Instance\SystemHealth;
use App\Support\Instance;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin backend. Gated by `can:manage instance` (routes/web.php).
 */
class DashboardController extends Controller
{
    public function index(AdminOverview $overview, SystemHealth $health): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $overview->stats(),
            'health' => $health->report(),
            'suspiciousUsers' => $overview->suspiciousUsers(),
            'mode' => Instance::mode(),
            'instanceName' => Instance::name(),
        ]);
    }
}
