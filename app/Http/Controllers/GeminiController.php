<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function ask(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validar pregunta
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'question' => 'required|string'
        ]);

        $question = $request->input('question');


        /*
        |--------------------------------------------------------------------------
        | Obtener requerimientos actuales
        |--------------------------------------------------------------------------
        */

        $requirements = Requirement::all([
            'id',
            'requester',
            'email',
            'titulo',
            'description',
            'requirement_type',
            'priority',
            'created_at'
        ]);

        $requirementsContext = $requirements->toJson();


        /*
        |--------------------------------------------------------------------------
        | Prompt
        |--------------------------------------------------------------------------
        */

        $prompt = "
        Eres un asistente especializado en gestión de requerimientos de software.

        Tu función es interpretar la pregunta del usuario utilizando el
        contexto de requerimientos proporcionado.

        Debes responder ÚNICAMENTE con un JSON válido.

        Los estados permitidos son:

        - no_accion
        - consultar_requerimientos
        - crear_requerimiento

        REGLAS:

        1. Si la pregunta no requiere consultar ni modificar requerimientos,
        utiliza status: no_accion.

        2. Si el usuario pregunta sobre los requerimientos existentes,
        utiliza status: consultar_requerimientos.

        3. Para consultas debes utilizar EXCLUSIVAMENTE la información
        proporcionada en el contexto.

        4. No inventes requerimientos, cantidades, títulos ni información.

        5. Si el usuario pregunta cuántos requerimientos existen,
        calcula la cantidad utilizando el contexto proporcionado.

        6. Si el usuario pregunta cuáles requerimientos cumplen algún criterio,
        utiliza el contexto para identificarlos.

        7. Si el usuario solicita crear un requerimiento,
        utiliza status: crear_requerimiento.

        8. Para crear un requerimiento debes extraer los datos proporcionados
        explícitamente por el usuario.

        9. Los campos disponibles son:

        - requester
        - email
        - titulo
        - description
        - requirement_type
        - priority

        10. Los valores válidos para titulo son:

        - titulo1
        - titulo2

        11. Los valores válidos para requirement_type son:

        - Desarrollo
        - Soporte
        - Mejora

        12. Los valores válidos para priority son:

        - Baja
        - Media
        - Alta

        13. Nunca inventes datos faltantes.

        14. Si faltan datos para crear el requerimiento,
        coloca null en esos campos.

        15. El mensaje no debe superar 20 palabras.

        16. Devuelve exactamente esta estructura:

        {
            \"status\": \"no_accion | consultar_requerimientos | crear_requerimiento\",
            \"message\": \"respuesta para el usuario\",
            \"data\": {}
        }

        EJEMPLO PARA CREAR:

        {
            \"status\": \"crear_requerimiento\",
            \"message\": \"Datos identificados para crear el requerimiento.\",
            \"data\": {
                \"requester\": \"Juan Perez\",
                \"email\": \"juan@gmail.com\",
                \"titulo\": \"titulo1\",
                \"description\": \"Sistema web para gestionar inventario.\",
                \"requirement_type\": \"Desarrollo\",
                \"priority\": \"Alta\"
            }
        }

        EJEMPLO PARA CONSULTAR:

        {
            \"status\": \"consultar_requerimientos\",
            \"message\": \"Actualmente existen 5 requerimientos.\",
            \"data\": {}
        }

        EJEMPLO SIN ACCIÓN:

        {
            \"status\": \"no_accion\",
            \"message\": \"Puedo ayudarte con la gestión de requerimientos.\",
            \"data\": {}
        }

        CONTEXTO ACTUAL DE REQUERIMIENTOS:

        $requirementsContext

        PREGUNTA DEL USUARIO:

        $question
        ";


        /*
        |--------------------------------------------------------------------------
        | Llamar a Gemini
        |--------------------------------------------------------------------------
        */

        try {

            $response = Http::timeout(120)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . env('GEMINI_API_KEY'),
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],

                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Gemini respondió con error HTTP
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {

                return response()->json([
                    'error' => 'Gemini respondió con un error.',
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'raw_response' => $response->body()
                ], $response->status());
            }


            /*
            |--------------------------------------------------------------------------
            | Obtener respuesta JSON
            |--------------------------------------------------------------------------
            */

            $data = $response->json();


            /*
            |--------------------------------------------------------------------------
            | Verificar que Gemini haya devuelto candidates
            |--------------------------------------------------------------------------
            */

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;


            if (!$text) {

                return response()->json([
                    'error' => 'Gemini no devolvió contenido.',
                    'response' => $data
                ], 500);
            }


            /*
            |--------------------------------------------------------------------------
            | Convertir respuesta de Gemini a array
            |--------------------------------------------------------------------------
            */

            $aiResponse = json_decode($text, true);


            if (json_last_error() !== JSON_ERROR_NONE) {

                return response()->json([
                    'error' => 'La respuesta de Gemini no contiene un JSON válido.',
                    'json_error' => json_last_error_msg(),
                    'gemini_response' => $text
                ], 500);
            }


            /*
            |--------------------------------------------------------------------------
            | Obtener información de la respuesta
            |--------------------------------------------------------------------------
            */

            $status = $aiResponse['status'] ?? 'no_accion';

            $message = $aiResponse['message'] ?? '';

            $aiData = $aiResponse['data'] ?? null;


            /*
            |--------------------------------------------------------------------------
            | Sin acción
            |--------------------------------------------------------------------------
            */

            if ($status === 'no_accion') {

                return response()->json([
                    'answer' => $message
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Consultar requerimientos
            |--------------------------------------------------------------------------
            */

            if ($status === 'consultar_requerimientos') {

                return response()->json([
                    'answer' => $message
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Crear requerimiento
            |--------------------------------------------------------------------------
            */

            if ($status === 'crear_requerimiento') {


                /*
                |------------------------------------------------------------------
                | Verificar datos
                |------------------------------------------------------------------
                */

                if (!$aiData || !is_array($aiData)) {

                    return response()->json([
                        'error' => 'Gemini no devolvió los datos necesarios para crear el requerimiento.',
                        'ai_response' => $aiResponse
                    ], 422);
                }


                /*
                |------------------------------------------------------------------
                | Validar datos extraídos por Gemini
                |------------------------------------------------------------------
                */

                $validator = validator($aiData, [

                    'requester' => 'required|string',

                    'email' => 'required|email',

                    'titulo' => 'required|string|in:titulo1,titulo2',

                    'description' => 'required|string',

                    'requirement_type' =>
                        'required|string|in:Desarrollo,Soporte,Mejora',

                    'priority' =>
                        'required|string|in:Baja,Media,Alta',

                ]);


                /*
                |------------------------------------------------------------------
                | Si los datos no son válidos
                |------------------------------------------------------------------
                */

                if ($validator->fails()) {

                    return response()->json([
                        'error' => 'Los datos del requerimiento no son válidos.',
                        'validation_errors' => $validator->errors(),
                        'ai_data' => $aiData
                    ], 422);
                }


                /*
                |------------------------------------------------------------------
                | Crear requerimiento
                |------------------------------------------------------------------
                */

                $validated = $validator->validated();

                $requirement = Requirement::create($validated);


                /*
                |------------------------------------------------------------------
                | Respuesta exitosa
                |------------------------------------------------------------------
                */

                return response()->json([
                    'answer' => 'Requerimiento creado correctamente.',
                    'requirement' => $requirement
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Estado desconocido
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'error' => 'No pude identificar la acción solicitada.',
                'ai_response' => $aiResponse
            ], 422);


        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            /*
            |--------------------------------------------------------------------------
            | Error de conexión / timeout
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'error' => 'No fue posible conectarse con Gemini.',
                'message' => $e->getMessage()
            ], 504);


        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Error general
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'error' => 'Ocurrió un error en el servidor.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}