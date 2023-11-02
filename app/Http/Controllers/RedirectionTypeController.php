<?php

namespace App\Http\Controllers;

use App\Models\MasterSetting;
use Illuminate\Http\Request;

class RedirectionTypeController extends Controller
{
    public function index()
    {
        $data = MasterSetting::first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $data = MasterSetting::first();
        $data->redirection_type = $request->redirection_type;
        $data->save();
        return response()->json([
            'message' => 'Successfully updated',
        ], 201);
    }

    public function options(Request $request)
    {

        return response()->json([
            [
                'value' => 1,
                'label' => 'Individual Redirection',
            ],
            [
                'value' => 2,
                'label' => 'Categorywise Redirection',
            ],
            // [
            //     'value' => 3,
            //     'label' => 'Countrywise Redirection',
            // ]
        ]);
    }
}
