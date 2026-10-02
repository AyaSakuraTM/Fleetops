<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_code' => ['required', 'string', 'max:50', 'unique:vehicles,vehicle_code'],
            'plate_number' => ['required', 'string', 'max:30', 'unique:vehicles,plate_number'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Active', 'Available', 'In Transit', 'Reserved', 'Maintenance', 'Inactive'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['vehicle_code'] = strtoupper($data['vehicle_code']);
        $data['plate_number'] = strtoupper($data['plate_number']);
        if ($data['status'] === 'Available') $data['status'] = 'Active';
        Vehicle::create($data);

        return redirect()->route('vehicles')->with('success', 'Vehicle saved to the fleet database.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'vehicle_code' => ['required', 'string', 'max:50', Rule::unique('vehicles', 'vehicle_code')->ignore($vehicle->id)],
            'plate_number' => ['required', 'string', 'max:30', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Active', 'Available', 'In Transit', 'Reserved', 'Maintenance', 'Inactive'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['vehicle_code'] = strtoupper($data['vehicle_code']);
        $data['plate_number'] = strtoupper($data['plate_number']);
        if ($data['status'] === 'Available') $data['status'] = 'Active';
        $vehicle->update($data);

        return redirect()->route('vehicles')->with('success', 'Vehicle changes saved to the fleet database.');
    }
}
