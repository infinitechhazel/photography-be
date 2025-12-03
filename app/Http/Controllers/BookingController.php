<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * ADMIN — Get all bookings
     */
    public function index()
    {
        $bookings = Booking::orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return response()->json($bookings);
    }

    /**
     * PUBLIC — Create a new booking
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstName'   => 'required|string|max:255',
            'lastName'    => 'nullable|string|max:255',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:50',
            'serviceType' => 'required|string',
            'date'        => 'required|date',
            'time'        => 'required',
            'guests'      => 'required|string',
            'message'     => 'nullable|string'
        ]);

        // Check if same date and time already exists
        $exists = Booking::where('date', $data['date'])
            ->where('time', $data['time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This timeslot is already booked.',
                'error'   => true
            ], 409); 
        }

        $booking = Booking::create($data);

        return response()->json([
            'message' => 'Booking created successfully!',
            'booking' => $booking
        ], 201);
    }

    /**
     * ADMIN — Show single booking
     */
    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    /**
     * ADMIN — Update a booking
     */
    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'firstName'   => 'sometimes|string|max:255',
            'lastName'    => 'sometimes|string|max:255',
            'email'       => 'sometimes|email',
            'phone'       => 'sometimes|string|max:50',
            'serviceType' => 'sometimes|string',
            'date'        => 'sometimes|date',
            'time'        => 'sometimes',
            'guests'      => 'sometimes|string',
            'message'     => 'sometimes|string'
        ]);

        // If date/time is being updated, check conflict
        if (isset($data['date']) && isset($data['time'])) {
            $exists = Booking::where('date', $data['date'])
                ->where('time', $data['time'])
                ->where('id', '!=', $booking->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'This timeslot is already booked.',
                    'error'   => true
                ], 409);
            }
        }

        $booking->update($data);

        return response()->json([
            'message' => 'Booking updated successfully!',
            'booking' => $booking
        ]);
    }

    /**
     * ADMIN — Delete booking
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully.'
        ], 200);
    }

    /**
     * PUBLIC — Get booked schedule (date → times)
     */
    public function bookedSchedule()
    {
        $grouped = Booking::orderBy('date', 'asc')
            ->get()
            ->groupBy('date');

        $data = $grouped->map(function ($items, $date) {
            return [
                'date'  => $date,
                'times' => $items->pluck('time')->values()->all(),
            ];
        })->values();

        return response()->json($data);
    }
}
