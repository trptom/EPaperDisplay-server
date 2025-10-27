<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Display;
use App\Models\Module;
use App\Models\User;
use App\Models\AllowedIp;

class DashboardController extends Controller
{
    /**
     * Public dashboard with usage statistics.
     */
    public function public(Request $request)
    {
        $usersCount = User::count();
        $displaysCount = Display::count();
        $modulesCount = -1; // TODO count of available internal modules

        // Total 'displayed' sum across displays (column exists in Display model)
        $totalDisplayed = Display::sum('displayed');

        return response()->json([
            'usersCount' => $usersCount,
            'displaysCount' => $displaysCount,
            'modulesCount' => $modulesCount,
            'totalDisplayed' => $totalDisplayed
        ]);
    }

    /**
     * Private dashboard with my statistics.
     */
    public function private(Request $request)
    {
        return response()->json([ /* TODO private dashboard not implemented yet */ ]);
    }
}
