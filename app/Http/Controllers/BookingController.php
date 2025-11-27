<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'serviceType' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'guests' => 'required|string',
            'message' => 'nullable|string'
        ]);

        // Check if same date and time already exists
        $exists = Booking::where('date', $data['date'])
            ->where('time', $data['time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This timeslot is already booked.',
                'error' => true
            ], 409); 
        }

        $booking = Booking::create($data);

        return response()->json([
            'message' => 'Booking created successfully!',
            'booking' => $booking
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }


    /**
     * Get all bookings, group them by date
     */
    public function bookedSchedule()
    {
        $grouped = Booking::orderBy('date', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return $item->date;
            });

        $data = $grouped->map(function ($items, $date) {
            return [
                'date'  => $date,
                'times' => $items->pluck('time')->values()->all(),
            ];
        })->values();

        return response()->json($data);
    }
}
