<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use Uuid; //Generamos ID unico para cada registro

class ClientController extends Controller
{
    public function register(Request $request)
    {

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


                /* DE ESTA MANERA SE GUARDA CON EL ORM
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
                */
                //ASI SE GUARDA CON SQL

                $params_array['id'] = Uuid::generate()->string;
                $params_array['password'] = $pwd;
                $params_array['role'] = 'ROLE_CLIENT';
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();

                DB::insert('insert into clients (id, name, surname, email, password, role, phone, address, created_at, updated_at) values (?,?,?,?,?,?,?,?,?,?)', [
                    $params_array['id'],
                    $params_array['name'],
                    $params_array['surname'],
                    $params_array['email'],
                    $params_array['password'],
                    $params_array['role'],
                    $params_array['phone'],
                    $params_array['address'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El cliente se ha registrado correctamente',
                    'client' => $params_array
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

    public function login(Request $request)
    {

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
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El cliente no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver token o datos
            $signup = $jwtAuth->signup('ROLE_CLIENT', $params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup('ROLE_CLIENT', $params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request)
    {

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

            $params_array =  (array) $params;
            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'email' => 'required|email|unique:clients' . $client->id,
                'password' => 'required',
                'address' => 'required',
                'phone' => 'required'
            ]);

            //en angular validar si se modifica o no la contraseña
            //$pwd = hash('sha256', $params->password);
            //$params->password = $pwd;

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['password']);
            unset($params_array['email']);
            unset($params_array['role']);
            unset($params_array['created_at']);

            /* Actualizar cliente en bbdd
            $client_update = Client::where('id', $client->id)->update($params_array);
            */
            $params_array['id'] = $client->id;
            $params_array['updated_at'] = new \DateTime();

            DB::update('update clients set name = ?, surname = ?, phone = ?, address = ?, updated_at = ? where id = ?', [
                $params_array['name'],
                $params_array['surname'],
                $params_array['phone'],
                $params_array['address'],
                $params_array['updated_at'],
                $params_array['id']
            ]);

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

    public function upload(Request $request)
    {
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
            DB::update('update clients set image = ? where id = ?', [$image_name, $client_image->id]);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
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

    public function detail($id)
    {

        $client = DB::select('select * from clients where id = ?', [$id]);


        if (count($client) > 0) {
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