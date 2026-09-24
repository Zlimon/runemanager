<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\User;
use App\Support\Instance;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin backend. Gated by `can:manage instance` (routes/web.php).
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'users' => User::count(),
                'accounts' => Account::count(),
                'announcements' => Announcement::count(),
            ],
            'mode' => Instance::mode(),
            'instanceName' => Instance::name(),
        ]);
    }
}
