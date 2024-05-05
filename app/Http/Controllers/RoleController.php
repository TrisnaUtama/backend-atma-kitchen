<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function getAllRole()
    {
        try {
            $products = Role::all();
            if (count($products) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'role is empty',
                    'data' => $products,
                ], 401);

            return response()->json([
                'status' => true,
                'message' => 'success retreive all data products',
                'data' => $products
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
