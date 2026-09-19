<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequirementRequest;
use App\Models\Requirement;

class RequirementController extends Controller
{
    public function index() 
    {
        return Requirement::all();
    }

    public function store(StoreRequirementRequest $request)
    {
        $requirement = Requirement::create($request->validated());

        return $requirement;
    }

    public function show($id)
    {
        $requirement = Requirement::find($id);

        if(!$requirement) {
            return response()->json([
                'message' => 'No se encontro el requerimiento'
            ], 404);
        }

        return $requirement;
    }
}
