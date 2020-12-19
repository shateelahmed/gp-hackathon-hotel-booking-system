<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Customer;
use App\Booking;
use App\Payment;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        return response([
            'data' => Customer::all(),
        ]);
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|min:11|max:11|unique:customers,phone',
        ]);

        $customer = new Customer();

        $customer->first_name = strtoupper($request->first_name);
        $customer->last_name = strtoupper($request->last_name);
        $customer->email = strtolower($request->email);
        $customer->phone = $request->phone;
        $customer->registered_at = Carbon::now();

        $customer->save();

        return response([
            'message' => 'Customer created successfully.',
            'data' => $customer,
        ]);
    }
    
    public function get(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found!',
                'data' => [],
            ], 404);
        }

        return response([
            'data' => $customer,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found!',
                'data' => [],
            ], 404);
        }

        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('customers')->ignore($id),
            ],
            'phone' => [
                'required',
                'min:11',
                'max:11',
                Rule::unique('customers')->ignore($id),
            ],
        ]);

        $customer->first_name = strtoupper($request->first_name);
        $customer->last_name = strtoupper($request->last_name);
        $customer->email = strtolower($request->email);
        $customer->phone = $request->phone;

        $customer->save();

        return response([
            'message' => 'Customer updated successfully.',
            'data' => $customer,
        ]);
    }
    
    public function delete(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found!',
                'data' => [],
            ], 404);
        }
        
        $customer->delete();

        return response([
            'message' => 'Customer deleted successfully.',
        ]);
    }
    
    public function bookings(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found!',
                'data' => [],
            ], 404);
        }
        
        $bookings = Booking::where('customer_id', $id)->get();

        return response([
            'data' => $bookings,
        ]);
    }
    
    public function payments(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found!',
                'data' => [],
            ], 404);
        }
        
        $booking_ids = Booking::where('customer_id', $id)->pluck('id')->toArray();
        $payments = Payment::whereIn('booking_id', $booking_ids)->get();

        return response([
            'data' => $payments,
        ]);
    }
}
