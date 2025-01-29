<?php

namespace App\Http\Controllers\Admin;

use App\Calendar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar');
    }

    public function truncate()
    {
        Calendar::query()->truncate();

        return redirect(route('admin-calendar'))->with('message', 'All events deleted!');
    }
}
