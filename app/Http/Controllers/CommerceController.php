<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Commerce;
use Uuid;

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
                $commerce->id = Uuid::generate()->string;
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

    public function update(Request $request) {

        // Comprobar si el comercio está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        
        $checkToken = $jwtAuth->checkToken($token);
        
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {

            // Sacar comercio identificado
            $commerce = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'tell' => 'required',
                'address' => 'required'
            ]);

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['created_at']);

            // Actualizar comercio en bbdd
            $commerce_update = Commerce::where('id', $commerce->id)->update($params_array);

            // Devolver array con resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'commerce' => $commerce,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El comercio no está identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger datos de la petición
        $image = $request->file('file0');
        $token = $request->header('Authorization');
        
        $jwtAuth = new \JwtAuth();        
        $commerce_image = $jwtAuth->checkToken($token, true);

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
            \Storage::disk('commerces')->put($image_name, \File::get($image));

            //Guardamos el nombre de la imagen en la base de datos
             commerce::where('id', $commerce_image->id)->update(array('image' => $image_name));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('commerces')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('commerces')->get($filename);
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
        $commerce = Commerce::find($id);

        if (is_object($commerce)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'commerce' => $commerce
            );
        } else {
             $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El comercio no existe.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
}
