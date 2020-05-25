<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

                $params_array['password'] = $pwd;
                $params_array['role'] = 'ROLE_USER';
                $params_array['updated_at'] = new \DateTime();
                $params_array['created_at'] = new \DateTime();

                DB::insert('insert into users (name, surname, email, role, password, phone, address, updated_at, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$params_array['name'],
                $params_array['surname'],
                $params_array['email'],
                $params_array['role'],
                $params_array['password'],
                $params_array['phone'],
                $params_array['address'],
                $params_array['updated_at'],
                $params_array['created_at']]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $params_array
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
        $params =  json_decode($json);

        if ($checkToken && !empty($params)) {

            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

                //en angular validar si se modifica o no la contraseña
                //$pwd = hash('sha256', $params->password);
                //$params->password = $pwd;
                $params_array =  (array)$params;

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['password']);
            unset($params_array['name']);
            unset($params_array['email']);
            unset($params_array['role']);
            unset($params_array['created_at']);

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

    public function upload(Request $request) {
        // Recoger datos de la petición
        $image = $request->file('file0');
        $token = $request->header('Authorization');
        
        $jwtAuth = new \JwtAuth();        
        $user_image = $jwtAuth->checkToken($token, true);

        // Validacion de imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {
            //Guardamos en local storage la imagen 
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            //Guardamos el nombre de la imagen en la base de datos
             User::where('id', $user_image->id)->update(array('image' => $image_name));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );

            return response()->json($data, $data['code']);
        }
    }

    public function detail($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else {
             $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
}
