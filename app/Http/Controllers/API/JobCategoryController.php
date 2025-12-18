<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = JobCategory::all();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List of job categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $categories = JobCategory::find($id);
        if (!$categories) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Job category not found',
            ]);
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Job category retrieved successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
