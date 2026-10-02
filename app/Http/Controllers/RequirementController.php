<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequirementRequest;
use App\Models\Requirement;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RequirementController extends Controller
{
    #[OA\Get(
        path: "/api/requirements",
        tags: ["Requirements"],
        summary: "Obtener todos los requerimientos",
        description: "Obtiene todos los requerimientos registrados.",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de requerimientos"
            )
        ]
    )]
    public function index()
    {
        return Requirement::all();
    }

    #[OA\Post(
        path: "/api/requirements/create",
        tags: ["Requirements"],
        summary: "Crear un requerimiento",
        description: "Registra un nuevo requerimiento.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "requester",
                    "email",
                    "description",
                    "requirement_type",
                    "priority",
                    "titulo"
                ],
                properties: [
                    new OA\Property(
                        property: "requester",
                        type: "string",
                        example: "Juan Perez"
                    ),
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "juan@gmail.com"
                    ),
                    new OA\Property(
                        property: "description",
                        type: "string",
                        example: "Necesito un sistema de inventario"
                    ),
                    new OA\Property(
                        property: "requirement_type",
                        type: "string",
                        example: "Software"
                    ),
                    new OA\Property(
                        property: "priority",
                        type: "string",
                        example: "Alta"
                    ),
                    new OA\Property(
                        property: "titulo",
                        type: "string",
                        example: "Sistema inventario"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Requerimiento creado correctamente"
            ),
            new OA\Response(
                response: 422,
                description: "Datos inválidos"
            )
        ]
    )]
    public function store(StoreRequirementRequest $request)
    {
        $requirement = Requirement::create($request->validated());

        return $requirement;
    }

    #[OA\Get(
        path: "/api/requirements/{id}",
        tags: ["Requirements"],
        summary: "Obtener un requerimiento por ID",
        description: "Obtiene un requerimiento específico mediante su ID.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del requerimiento",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Requerimiento encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Requerimiento no encontrado"
            )
        ]
    )]
    public function show($id)
    {
        $requirement = Requirement::find($id);

        if (!$requirement) {
            return response()->json([
                'message' => 'No se encontro el requerimiento'
            ], 404);
        }

        return $requirement;
    }

    #[OA\Put(
        path: "/api/requirements/{id}",
        tags: ["Requirements"],
        summary: "Actualizar un requerimiento",
        description: "Actualiza la información de un requerimiento existente mediante su ID.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del requerimiento",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "requester",
                    "email",
                    "description",
                    "requirement_type",
                    "priority",
                    "titulo"
                ],
                properties: [
                    new OA\Property(
                        property: "requester",
                        type: "string",
                        example: "Juan Perez"
                    ),
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "juan@gmail.com"
                    ),
                    new OA\Property(
                        property: "description",
                        type: "string",
                        example: "Descripción actualizada"
                    ),
                    new OA\Property(
                        property: "requirement_type",
                        type: "string",
                        example: "Software"
                    ),
                    new OA\Property(
                        property: "priority",
                        type: "string",
                        example: "Media"
                    ),
                    new OA\Property(
                        property: "titulo",
                        type: "string",
                        example: "Sistema de inventario actualizado"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Requerimiento actualizado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Requerimiento no encontrado"
            ),
            new OA\Response(
                response: 422,
                description: "Datos inválidos"
            )
        ]
    )]
    public function update(Request $request, $id)
    {
        $requirement = Requirement::find($id);

        if (!$requirement) {
            return response()->json([
                'message' => 'No se encontro el requerimiento'
            ], 404);
        }

        $validated = $request->validate([
            'requester' => 'required|string',
            'email' => 'required|email',
            'description' => 'required|string',
            'requirement_type' => 'required|string',
            'priority' => 'required|string',
            'titulo' => 'required|string',
        ]);

        $requirement->update($validated);

        return response()->json([
            'message' => 'Requerimiento actualizado correctamente',
            'requirement' => $requirement
        ], 200);
    }

    #[OA\Delete(
        path: "/api/requirements/{id}",
        tags: ["Requirements"],
        summary: "Eliminar un requerimiento",
        description: "Elimina un requerimiento existente mediante su ID.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del requerimiento",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Requerimiento eliminado correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Requerimiento no encontrado"
            )
        ]
    )]
    public function destroy($id)
    {
        $requirement = Requirement::find($id);

        if (!$requirement) {
            return response()->json([
                'message' => 'No se encontro el requerimiento'
            ], 404);
        }

        $requirement->delete();

        return response()->json([
            'message' => 'Requerimiento eliminado correctamente'
        ], 200);
    }
}