<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use Uuid;

class CommerceController extends Controller
{
    public function getCommerces()
    {
        $commerces = DB::select('select id, name_owner, name_commerce, cell, tell, address, description, image from commerces');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'Data' => $commerces
        ]);
    }

    public function getCommerce($id)
    {
        $commerce = DB::select('select id, name_owner, name_commerce, cell, tell, address, description, image from commerces where id = ?', [$id]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'Data' => $commerce
        ]);
    }

    public function register(Request $request)
    {

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

                /* Crear el comercio
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
                */

                $params_array['id'] = Uuid::generate()->string;
                $params_array['password'] = $pwd;
                $params_array['role'] = 'ROLE_COMMERCE';
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();

                //ASI SE GUARDA CON SQL
                DB::insert('insert into commerces (id, email, password, name_owner, name_commerce, role, cell, tell, recovery_email, description, address, created_at, updated_at) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                    $params_array['id'],
                    $params_array['email'],
                    $params_array['password'],
                    $params_array['name_owner'],
                    $params_array['name_commerce'],
                    $params_array['role'],
                    $params_array['cell'],
                    $params_array['tell'],
                    $params_array['recovery_email'],
                    $params_array['description'],
                    $params_array['address'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El comercio se ha creado correctamente',
                    'commerce' => $params_array
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
                'message' => 'El comercio no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver token o datos
            $signup = $jwtAuth->signup('ROLE_COMMERCE', $params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup('ROLE_COMMERCE', $params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request)
    {

        // Comprobar si el comercio está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();

        $checkToken = $jwtAuth->checkToken($token);

        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $params =  json_decode($json);

        if ($checkToken && !empty($params_array)) {

            // Sacar comercio identificado
            $commerce = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'email' => 'required|email|unique:commerces' . $commerce->id,
                'password' => 'required',
                'name_owner' => 'required',
                'name_commerce' => 'required',
                'tell' => 'required',
                'address' => 'required',
            ]);

            //En angular validar si se modifica o no la contraseña
            $pwd = hash('sha256', $params->password);
            $params->password = $pwd;
               
            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['created_at']);

            $params_array['id'] = $commerce->id;
            $params_array['password'] = $pwd;
            $params_array['updated_at'] = new \DateTime();

            DB::update('update commerces set  email = ?, password = ?, name_commerce = ?,name_owner = ?, cell = ?, tell = ?, recovery_email = ?, description = ?, address = ?,  image = ?, updated_at= ? where id = ?', [
                $params_array['email'],
                $params_array['password'],
                $params_array['name_commerce'],
                $params_array['name_owner'],
                $params_array['cell'],
                $params_array['tell'],
                $params_array['recovery_email'],
                $params_array['description'],
                $params_array['address'],
                $params_array['image'],
                $params_array['updated_at'],
                $params_array['id']
            ]);


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

    public function upload(Request $request)
    {
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

    public function detail($id)
    {
        $commerce = DB::select('select * from commerces where id = ?', [$id]);

        if (count($commerce) > 0) {
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