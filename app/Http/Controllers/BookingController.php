<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function createBook(Request $request)
    {
        // Validate the request
        $request->validate([
            'apartment_id' => 'required|integer|exists:apartments,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $apartmentId = $request->apartment_id;

        // change the string date to carbon date
        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);
        $user = Auth::user();

        // Check if user status is approved
        if ($user->status !== 'approved') {
            return response()->json([
                'message' => 'Your account must be approved before you can make bookings. Current status: ' . $user->status
            ], 403);
        }

        $apartment = Apartment::find($apartmentId);

        // get all future approved bookings for this apartment
        $allBookings = $apartment->bookings()
            ->where('end_date', '>=', now())
            ->where('status', 'approved')
            ->get();


        foreach ($allBookings as $booking) {
            // check if dates overlap
            $hasConflict = (
                $startDate < $booking->end_date &&
                $booking->start_date < $endDate
            );

            if ($hasConflict) {
                return response()->json([
                    'message' => 'Booking conflict: The apartment is already booked for the selected dates!',
                    'conflicting_booking' => [
                        'id' => $booking->id,
                        'start_date' => $booking->start_date,
                        'end_date' => $booking->end_date,
                        'status' => $booking->status,
                    ]
                ], 409);
            }
        }
        // the owner cannot book his own apartment
        if ($apartment->user_id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot book your own apartment'
            ], 403);
        }

        //count the total price
        $days = $startDate->diffInDays($endDate) + 1;
        $pricePerDay = $apartment->price;
        $totalPrice = $days * $pricePerDay;

        // create the booking with pending status (wait for owner approval)
        $booking = Booking::create([
            'user_id' => $user->id,
            'apartment_id' => $apartmentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Booking request created successfully. Waiting for apartment owner approval.',
            'booking' => [
                'id' => $booking->id,
                'apartment_id' => $booking->apartment_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'created_at' => $booking->created_at,
            ]
        ], 201);
    }
    public function approve($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);

        if ($booking->apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'you do not own this apartment!',
            ], 403);
        }

        // check if the dates conflict with an existing approved booking 
        $startDate = Carbon::parse($booking->start_date);
        $endDate = Carbon::parse($booking->end_date);

        //get all future approved bookings except this one
        $allBookings = $booking->apartment->bookings()
            ->where('status', 'approved')
            ->where('id', '!=', $booking->id)
            ->where('end_date', '>=', now())
            ->get();

        foreach ($allBookings as $approvedBooking) {
            $hasConflict = (
                $startDate < $approvedBooking->end_date &&
                $approvedBooking->start_date < $endDate
            );
            if ($hasConflict) {
                return response()->json([
                    'message' => 'Cannot approve this booking ,dates conflict with an existing approved booking.',
                    'conflicting_booking' => [
                        'id' => $approvedBooking->id,
                        'start_date' => $approvedBooking->start_date,
                        'end_date' => $approvedBooking->end_date,
                    ]
                ], 400);
            }
        }

        // approve only pending bookings not other statuses
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be approved'
            ], 400);
        }
        // approve the booking
        $booking->update([
            'status' => 'approved',
        ]);


        return response()->json([
            'message' => 'Booking approved successfully',
            'booking' => [
                'id' => $booking->id,
                'apartment_id' => $booking->apartment_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'total_price' => $booking->total_price,
            ]
        ], 200);
    }
    public function reject($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);

        // check if the user owns the apartment
        if ($booking->apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'you do not own this apartment!',
            ], 403);
        }

        // reject only pending bookings not other statuses
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be rejected'
            ], 400);
        }
        // reject the booking
        $booking->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Booking rejected successfully',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
            ]
        ], 200);
    }
    public function cancel($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);

        // check if the user own this booking 
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'you did not make this booking !'
            ], 400);
        }

        // the user could cancel only the pending and approved booking  
        if ($booking->status !== 'pending' && $booking->status !== 'approved') {
            return response()->json([
                'message' => 'you cannot cancel this booking'
            ], 400);
        }

        // cancel the booking
        $booking->update([
            'status' => 'cancelled'
        ]);
        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
            ]
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date'
        ]);
        $booking = Booking::with('apartment')->findOrFail($id);

        // check if the user own the booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'the user did not make this booking!'
            ], 400);
        }
        // only pending or approved booking can be updated
        if ($booking->status !== 'pending' && $booking->status !== 'approved') {
            return response()->json([
                'message' => 'only pending or approved booking can be updated!'
            ], 400);
        }

        // get the new dates
        $newStartDate = Carbon::parse($request->start_date);
        $newEndDate = Carbon::parse($request->end_date);

        // check for conflict with existing future approved bookings
        $allBookings = $booking->apartment->bookings()
            ->where('status', 'approved')
            ->where('end_date', '>=', now())
            ->where('id', '!=', $booking->id)
            ->get();

        foreach ($allBookings as $approvedBooking) {
            $hasConflict = (
                $newStartDate < $approvedBooking->end_date &&
                $approvedBooking->start_date < $newEndDate
            );
            if ($hasConflict) {
                return response()->json([
                    'message' => 'Cannot update this booking ,dates conflict with an existing approved booking.',
                    'conflicting_booking' => [
                        'id' => $approvedBooking->id,
                        'start_date' => $approvedBooking->start_date,
                        'end_date' => $approvedBooking->end_date,
                    ]
                ], 400);
            }
        }


        // calculate the new total price
        $days = $newStartDate->diffInDays($newEndDate) + 1;
        $pricePerDay = $booking->apartment->price;
        $totalPrice = $days * $pricePerDay;
        // update the booking
        $booking->update([
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => [
                'id' => $booking->id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => $booking->status,
                'total_price' => $booking->total_price,
            ]
        ], 200);
    }

    // delete booking
    public function delete($id)
    {
        $booking = Booking::with('apartment')->findOrFail($id);
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'you did not make this booking!'
            ], 400);
        }

        $booking->delete();
        return response()->json([
            'message' => 'Booking deleted successfully',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
            ]
        ], 200);
    }

    public function getAllUserBookings()
    {

        $userBookings = Auth::user()
            ->bookings()
            ->with('apartment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'getting user bookings success',
            'data' => $userBookings
        ], 200);
    }
    public function getAllOwnerBookings()
    {
        // get all bookings for all owned apartments ,and order them by apartment id 
        $ownerBookings = Booking::join('apartments', 'bookings.apartment_id', '=', 'apartments.id')
            ->where('apartments.user_id', Auth::id())
            ->with('apartment')
            ->orderBy('apartments.id', 'asc')
            ->select('bookings.*')
            ->paginate(10);

        return response()->json([
            'message' => 'getting owner bookings success',
            'data' => $ownerBookings
        ], 200);
    }
}
