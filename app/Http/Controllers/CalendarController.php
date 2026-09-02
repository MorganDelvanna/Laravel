<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalendarController extends Controller
{
    public function events(): JsonResponse
    {
        try {
            $events = CalendarEvent::query()
            ->where('start', '>=', date_create('2025-09-30'))
            ->orderBy('start')
            ->get();

            return response()->json($events);
        } catch (Throwable $e) {
            Log::error('Error fetching calendar events: ' . $e->getMessage());
            // Return an empty JSON array with an HTTP 200 (or 500) status
            return response()->json([]);
        }       
    }

    public function eventList()
    {
        $events = CalendarEvent::query()
            ->orderBy('start')
            ->get();

        return view('admin.calendar', compact('events'));
    }

    public function saveEvent(Request $request)
    {
        $validated = $request->validate([
            'eventID' => ['nullable', 'integer', 'exists:calendar_events,id'],
            'start' => ['required', 'date_format:Y-m-d H:i'],
            'end' => ['required', 'date_format:Y-m-d H:i'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'allDay' => ['sometimes', 'boolean'],
        ]);

        $event = !empty($validated['eventID'])
            ? CalendarEvent::findOrFail($validated['eventID'])
            : new CalendarEvent();

        $event->fill([
            'start' => $validated['start'],
            'end' => $validated['end'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'allDay' => $validated['allDay'] ?? false,
        ]);
        $event->save();

        return redirect()
            ->route('calendar.admin')
            ->with('status', $validated['eventID'] ? 'Event updated.' : 'Event created.');
    }

    public function deleteEvent($id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->delete();

        return redirect()
            ->route('calendar.admin')
            ->with('status', 'Event deleted.');
    }
}