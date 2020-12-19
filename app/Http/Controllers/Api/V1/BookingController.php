<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Booking;
use App\Payment;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return response([
            'data' => Booking::all(),
        ]);
    }

    public function roomIsbooked($room_id, $arrival, $duration) {
        $day = $arrival . ' 12:00:00';
        $day = Carbon::parse($day);

        while ($duration >= 1) {
            $query = "
                SELECT
                    *
                FROM
                    bookings
                WHERE
                    room_id = ?
                    AND ? BETWEEN arrival AND IIF(checkout IS NOT NULL, checkout, DATEADD(DAY, duration, arrival))
            ";
            $parameters = [$room_id, $day];
            $has_conflict = DB::select($query, $parameters);
            if ($has_conflict) {
                return 1;
            }
            $day = $day->addDays(1);
            $duration--;
        }

        return 0;
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'room_id' => 'required|exists:rooms,id',
            'arrival' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'duration' => 'required|integer|min:1',
        ]);

        if ($this->roomIsBooked($request->room_id, $request->arrival, $request->duration)) {
            return response([
                'message' => 'The selected room is taken during this booking.',
                'data' => [],
            ], 403);
        }

        $booking = new Booking();

        $booking->room_id = $request->room_id;
        $booking->customer_id = $request->customer_id;
        $booking->arrival = $request->arrival;
        $booking->duration = $request->duration;
        $booking->book_time = Carbon::now();

        $booking->save();

        return response([
            'message' => 'Booking created successfully.',
            'data' => $booking,
        ]);
    }
    
    public function get(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        return response([
            'data' => $booking,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'room_id' => 'required|exists:rooms,id',
            'arrival' => 'required|date|date_format:Y-m-d H:i|after_or_equal:today',
            'duration' => 'required|integer|min:1',
        ]);

        $booking->room_id = $request->room_id;
        $booking->customer_id = $request->customer_id;
        $booking->arrival = $request->arrival;
        $booking->duration = $request->duration;

        $booking->save();

        return response([
            'message' => 'Booking updated successfully.',
            'data' => $booking,
        ]);
    }
    
    public function delete(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        $booking->delete();

        return response([
            'message' => 'Booking deleted successfully.',
        ]);
    }
    
    public function checkIn(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        $now = Carbon::now();
        $departure = Carbon::parse($booking->arrival)->addDays($booking->duration);
        if ($now >= $departure) {
            return response([
                'message' => 'Customer cannot be checked in after or at the time of departure!',
                'data' => [],
            ], 403);
        }
        if ($booking->checkout) {
            return response([
                'message' => 'Customer has already checked out!',
                'data' => [],
            ], 403);
        }
        if ($booking->checkin) {
            return response([
                'message' => 'Customer has already checked in!',
                'data' => [],
            ], 403);
        }


        $booking->checkin = $now;

        $booking->save();

        return response([
            'message' => 'Checkin successful.',
            'data' => $booking,
        ]);
    }

    public function checkOut(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        $now = Carbon::now();
        $departure = Carbon::parse($booking->arrival)->addDays($booking->duration);

        if ($booking->checkout) {
            return response([
                'message' => 'Customer has already checked out!',
                'data' => [],
            ], 403);
        }

        $booking->checkout = $now;

        $booking->save();

        return response([
            'message' => 'Checkout successful.',
            'data' => $booking,
        ]);
    }

    public function makePayment(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return response([
                'message' => 'Booking not found!',
                'data' => [],
            ], 404);
        }

        $total_bill = $booking->room->price * $booking->duration;
        $total_paid = $booking->payments->sum('amount');
        $max_payable = $total_bill - $total_paid;

        if (!$max_payable) {
            return response([
                'message' => 'The bill for this booking has been paid already!',
                'data' => [],
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:'.$max_payable,
        ]);

        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->amount = $request->amount;
        $payment->payment_date = Carbon::now();

        $payment->save();

        return response([
            'message' => 'Payment successful.',
            'data' => $payment,
        ]);
    }
}
