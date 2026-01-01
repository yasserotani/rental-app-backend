<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use App\Models\Apartment;



class ReviewsController extends Controller
{
    public function review(Request $request, $apartment_id)
    {
        $validatedData = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        // Validate apartment exists
        $apartment = Apartment::findOrFail($apartment_id);

        $user = Auth::user();

        // تحقق أن المستخدم لديه حجز Approved للشقة
        $hasBooking = Booking::where('user_id', $user->id)
            ->where('apartment_id', $apartment_id)
            ->where('status', 'approved')
            ->exists();

        if (!$hasBooking) {
            return response()->json([
                'message' => 'You can only review apartments you have rented.'
            ], 403);
        }

        // Check if user already reviewed this apartment
        $existingReview = Review::where('user_id', $user->id)
            ->where('apartment_id', $apartment_id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this apartment.'
            ], 409);
        }


        // إضافة user_id و apartment_id
        $validatedData['user_id'] = $user->id;
        $validatedData['apartment_id'] = $apartment_id;

        // إنشاء التقييم
        $review = Review::create($validatedData);

        // update the apartment average rating
        $averageRating = $apartment->reviews()->avg('rating'); // avg function return the average of the ratings
        $reviewsCount = $apartment->reviews()->count(); // count function return the number of reviews

        $apartment->update([
            'average_rating' => $averageRating,
            'reviews_count' => $reviewsCount,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }
}
