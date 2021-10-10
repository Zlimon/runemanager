<?php

namespace App\Http\Controllers\Admin\Api;

use App\Calendar;
use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Rules\ValidateIconId;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $calendar = Calendar::get();

        foreach ($calendar as &$event) {
            $event->backgroundColor = $event->event_color;

            // All day events
            if (!$event->end_date) {
                $event->allDay = true;
                $event->backgroundColor = '#a66fb5';
            }

            // Rename start and end date key names
            $event->start = $event->start_date;
            $event->end = $event->end_date;

            unset($event->event_color);
            unset($event->start_date);
            unset($event->end_date);
        }

        return response($calendar, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:200'],
            'start_date' => ['required', 'date', 'after:yesterday'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'icon_id' => ['nullable', 'integer', new ValidateIconId()],
            'event_color' => ['nullable', 'string', 'max:7'],
        ]);

        $calendar = new Calendar();

        $calendar->user_id = 1;
        $calendar->title = $request->title;
        $calendar->description = $request->description;
        $calendar->start_date = $request->start_date;
        $calendar->end_date = $request->end_date;
        $calendar->icon_id = $request->icon_id;
        $calendar->event_color = $request->event_color;

        $calendar->save();

        return response($calendar, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Calendar $calendar
     * @return \Illuminate\Http\Response
     */
    public function show(Calendar $calendar)
    {
        return response($calendar, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Calendar $calendar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Calendar $calendar)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:200'],
            'start_date' => ['required', 'date', 'after:yesterday'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'icon_id' => ['nullable', 'integer', new ValidateIconId()],
            'event_color' => ['nullable', 'string', 'max:7'],
        ]);

        $calendar->user_id = 1;
        $calendar->title = $request->title;
        $calendar->description = $request->description;
        $calendar->start_date = $request->start_date;
        $calendar->end_date = $request->end_date;
        $calendar->icon_id = $request->icon_id;
        $calendar->event_color = $request->event_color;

        $calendar->save();

        return response($calendar, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Calendar $calendar
     * @return \Illuminate\Http\Response
     */
    public function updateSchedule(Request $request, Calendar $calendar)
    {
        $this->validate($request, [
            'start_date' => ['required', 'date', 'after:yesterday'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $calendar->start_date = $request->start_date;
        $calendar->end_date = $request->end_date;

        $calendar->save();

        return response($calendar, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Calendar $calendar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Calendar $calendar)
    {
        $calendar->delete();

        return response('', 204);
    }
}
