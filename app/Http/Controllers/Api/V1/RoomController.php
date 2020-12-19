<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Room;
use App\Booking;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        return response([
            'data' => Room::all(),
        ]);
    }
    
    public function create(Request $request)
    {
        if (isset($request->room_type)) {
            $request_data = $request->all();
            $request_data['room_type'] = strtoupper($request_data['room_type']);
            $request->replace($request_data);
        }

        $request->validate([
            'room_number' => 'required|max:255|unique:rooms',
            'price' => 'required|numeric|min:1',
            'max_persons' => 'required|integer|min:1',
            'room_type' => [
                'required',
                Rule::in(['SINGLE', 'DOUBLE', 'FAMILY']),
            ],
        ]);

        $room = new Room();

        $room->room_number = strtoupper($request->room_number);
        $room->price = $request->price;
        $room->max_persons = $request->max_persons;
        $room->room_type = $request->room_type;

        $room->save();

        return response([
            'message' => 'Room created successfully.',
            'data' => $room,
        ]);
    }
    
    public function get(Request $request, $id)
    {
        $room = Room::where('id', $id)->first();

        if (!$room) {
            return response([
                'message' => 'Room not found!',
                'data' => [],
            ], 404);
        }

        return response([
            'data' => $room,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $room = Room::where('id', $id)->first();

        if (!$room) {
            return response([
                'message' => 'Room not found!',
                'data' => [],
            ], 404);
        }

        if (isset($request->room_type)) {
            $request_data = $request->all();
            $request_data['room_type'] = strtoupper($request_data['room_type']);
            $request->replace($request_data);
        }

        $request->validate([
            'room_number' => [
                'required',
                'max:255',
                Rule::unique('rooms')->ignore($id),
            ],
            'price' => 'required|numeric|min:1',
            'max_persons' => 'required|integer|min:1',
            'room_type' => [
                'required',
                Rule::in(['SINGLE', 'DOUBLE', 'FAMILY']),
            ],
        ]);

        $room->room_number = strtoupper($request->room_number);
        $room->price = $request->price;
        $room->max_persons = $request->max_persons;
        $room->room_type = $request->room_type;

        $room->save();

        return response([
            'message' => 'Room updated successfully.',
            'data' => $room,
        ]);
    }
    
    public function delete(Request $request, $id)
    {
        $room = Room::where('id', $id)->first();

        if (!$room) {
            return response([
                'message' => 'Room not found!',
                'data' => [],
            ], 404);
        }

        $room->delete();

        return response([
            'message' => 'Room deleted successfully.',
        ]);
    }
    
    public function bookings(Request $request, $id)
    {
        $room = Room::where('id', $id)->first();

        if (!$room) {
            return response([
                'message' => 'Room not found!',
                'data' => [],
            ], 404);
        }
        
        $bookings = Booking::where('room_id', $id)->get();

        return response([
            'data' => $bookings,
        ]);
    }
}
