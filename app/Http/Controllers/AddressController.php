<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $addresses = Address::where('user_id', $userId)->get();
        return $this->Ok($addresses, "Addresses retrieved successfully!");
    }

    public function store(Request $request)
    {
        \Log::info("Request Data:", $request->all());
        $validator = validator()->make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'full_name'     => 'required|string|max:255',
            'phone_number'  => 'required|string|max:20',
            'region'        => 'required|string|max:255',
            'province'      => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'barangay'      => 'required|string|max:255',
            'postal_code'   => 'required|string|max:10',
            'street_address'=> 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            \Log::error("Validation failed:", $validator->errors()->all());
            return $this->BadRequest($validator);
        }

        $address = Address::create($validator->validated());

        return $this->Created($address, "Address created successfully!");
    }

    public function destroy(Request $request, $id)
    {
        $userId = $request->user()->id;
        $address = Address::where('id', $id)->where('user_id', $userId)->first();

        if (!$address) {
            return $this->NotFound("Address not found or does not belong to the user.");
        }

        $address->delete();

        return $this->Ok([], "Address deleted successfully!");
    }
}