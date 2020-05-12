<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Commerce;

class CommerceController extends Controller
{
    public function register(Request $request) {

        // Recorger los datos del comercio por post
        $json = $request->input('json', null);
        $params = json_decode($json); // objeto
        $params_array = json_decode($json, true); // array

        if (!empty($params) && !empty($params_array)) {

            // Limpiar datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                        'email' => 'required|email|unique:commerces',
                        'password' => 'required',
                        'name_owner' => 'required',
                        'name_commerce' => 'required',
                        'tell' => 'required',
                        'address' => 'required',
            ]);

            if ($validate->fails()) {
                // La validación ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El comercio no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                // Validación pasada correctamente
                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                // Crear el comercio
                $commerce = new Commerce();
                $commerce->email = $params_array['email'];
                $commerce->password = $pwd;
                $commerce->name_owner = $params_array['name_owner'];
                $commerce->name_commerce = $params_array['name_commerce'];
                $commerce->role = 'ROLE_COMMERCE';
                $commerce->cell = $params_array['cell'];
                $commerce->tell = $params_array['tell'];
                $commerce->recovery_email = $params_array['recovery_email'];
                $commerce->description = $params_array['description'];
                $commerce->	address = $params_array['address'];
                // Guardar el comercio
                $commerce->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El comercio se ha creado correctamente',
                    'commerce' => $commerce
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }


    public function login(Request $request) {

        $jwtAuth = new \JwtAuth();

        // Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar esos datos
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            // La validación ha fallado
            $signupcommerce = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El comercio no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver token o datos
            $signupcommerce = $jwtAuth->signupcommerce($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signupcommerce = $jwtAuth->signupcommerce($params->email, $pwd, true);
            }
        }

        return response()->json($signupcommerce, 200);
    }   

}
