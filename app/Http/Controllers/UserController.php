<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function register(Request $request) {

        // Recorger los datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json); // objeto
        $params_array = json_decode($json, true); // array

        if (!empty($params) && !empty($params_array)) {

            // Limpiar datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required',
                        'address' => 'required',
                        'phone' => 'required'
            ]);

            if ($validate->fails()) {
                // La validación ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                // Validación pasada correctamente
                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                // Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->	phone = $params_array['phone'];
                $user->	address = $params_array['address'];
                // Guardar el usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
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
            $signupUser = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver token o datos
            $signupUser = $jwtAuth->signupUser($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signupUser = $jwtAuth->signupUser($params->email, $pwd, true);
            }
        }

        return response()->json($signupUser, 200);
    }

    public function update(Request $request) {

        // Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        
        $checkToken = $jwtAuth->checkToken($token);
        
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {

            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'email' => 'required|email|unique:users,' . $user->id
            ]);

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // Actualizar usuario en bbdd
            $user_update = User::where('id', $user->id)->update($params_array);

            // Devolver array con resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

}
