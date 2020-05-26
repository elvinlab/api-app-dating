<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\client;
use Uuid;

class ClientController extends Controller
{
    public function register(Request $request) {

        // Recorger los datos del cliente por post
        $json = $request->input('json', null);
        $params = json_decode($json); // objeto
        $params_array = json_decode($json, true); // array

        if (!empty($params) && !empty($params_array)) {

            // Limpiar datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'email' => 'required|email|unique:clients',
                        'password' => 'required',
                        'address' => 'required',
                        'phone' => 'required'
            ]);

            if ($validate->fails()) {
                // La validación ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El cliente no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                // Validación pasada correctamente
                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                // Crear el cliente
                $client = new Client();
                $client->id = Uuid::generate()->string;
                $client->name = $params_array['name'];
                $client->surname = $params_array['surname'];
                $client->email = $params_array['email'];
                $client->password = $pwd;
                $client->role = 'ROLE_CLIENT';
                $client->	phone = $params_array['phone'];
                $client->	address = $params_array['address'];
                // Guardar el cliente
                $client->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El cliente se ha creado correctamente',
                    'client' => $client
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
            $signupclient = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El cliente no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver token o datos
            $signupclient = $jwtAuth->signupclient($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signupclient = $jwtAuth->signupclient($params->email, $pwd, true);
            }
        }

        return response()->json($signupclient, 200);
    }

    public function update(Request $request) {

        // Comprobar si el cliente está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params =  json_decode($json);

        if ($checkToken && !empty($params)) {

            // Sacar cliente identificado
            $client = $jwtAuth->checkToken($token, true);

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

            // Actualizar cliente en bbdd
            $client_update = Client::where('id', $client->id)->update($params_array);

            // Devolver array con resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'client' => $client,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El cliente no está identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger datos de la petición
        $image = $request->file('file0');
        $token = $request->header('Authorization');
        
        $jwtAuth = new \JwtAuth();        
        $client_image = $jwtAuth->checkToken($token, true);

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
            \Storage::disk('clients')->put($image_name, \File::get($image));

            //Guardamos el nombre de la imagen en la base de datos
             Client::where('id', $client_image->id)->update(array('image' => $image_name));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('clients')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('clients')->get($filename);
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
        $client = Client::find($id);

        if (is_object($client)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'client' => $client
            );
        } else {
             $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El cliente no existe.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
}