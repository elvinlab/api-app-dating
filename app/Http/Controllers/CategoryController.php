<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use App\Helpers\JwtAuth;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function index()
    {
        $categories = DB::select('select * from categories');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id)
    {
        $category = DB::select('select * from categories where id = ?', [$id]);

        if (count($category) > 0) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Conseguir usuario identificado
            $commerce = $this->getIdentity($request);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|unique:categories',
            ]);

            // Guardar la categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria.',
                    'error' => $validate->errors()
                ];
            } else {
                /*
                $category = new Category();
                $category->name = $params_array['name'];
                $category->description = $params_array['description'];
                $category->save();
                */

                $params_array['commerce_id'] = $commerce->id;
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();

                DB::insert('insert into categories (name, commerce_id, description, created_at, updated_at) values (?,?,?,?,?)', [
                    $params_array['name'],
                    $params_array['commerce_id'],
                    $params_array['description'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $params_array
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'
            ];
        }

        // Devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // Recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|unique:categories' . $id
            ]);

            // Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            /* Actualizar el registro(categoria)
            $category = Category::where('id', $id)->update($params_array);
            */

            $params_array['id'] = $id;
            $params_array['updated_at'] = new \DateTime();

            DB::update('update categories set description = ?, updated_at = ? where id = ?', [
                $params_array['description'],
                $params_array['updated_at'],
                $params_array['id']
            ]);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $params_array
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'
            ];
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        /* Conseguir el registro
        $category = category::where('id', $id)->first();
        */

        $category = DB::select('select * from categories where id = ?', [$id]);

        if (count($category) > 0) {
            /*Borrarlo
            $category->delete();
            */

            DB::delete('delete from categories where id=?', [$id]);

            // Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getCategoriesByCommerce($id)
    {
        $commerces = DB::select('select * from commerces where commerce_id = ?', [$id]);

        return response()->json([
            'status' => 'success',
            'Services' => $commerces
        ], 200);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $commerce = $jwtAuth->checkToken($token, true);

        return $commerce;
    }
   

}