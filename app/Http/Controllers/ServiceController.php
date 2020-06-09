<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use App\Helpers\JwtAuth;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage'
        ]]);}

    public function show($id)
    {

        $service = DB::select('select * from services where id = ?', [$id]);

        if (count($service) > 0) {

            $category = DB::select(
                'select * from categories
            INNER JOIN services  ON categories.id = services.category_id
            where services.id = ?;',
                [$id]
            );

            $commerce = DB::select('select * from commerces where id = ?', [$service[0]->commerce_id]);

            $data = [
                'code' => 200,
                'status' => 'success',
                'services' => $service,
                'category' => $category,
                'commerce' => $commerce

            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El servicio no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger datos por Service
        $json = $request->input('json', null);//la respuesta lo convierte a JSON
        $params = json_decode($json);//Decodifica ese JSON a objeto
        $params_array = json_decode($json, true);//Pasa ese JSON a array

        //var_dump($params); die();

        if (!empty($params_array)) {
            // Conseguir comercio identificado
            $commerce = $this->getIdentity($request);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'category_id' => 'required',
                'name' => 'required',
                'description' => 'required',
                'price' => 'required'

            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el Servicio, faltan datos',
                    'error' => $validate->errors()
                ];
            } else {

                /*
                // Guardar el articulo
                $service = new Service();
                $service->commerce_id = $commerce->id;
                $service->category_id = $params->category_id;
                $service->name = $params->name;
                $service->description = $params->description;
                $service->price = $params->price;
                $service->save();
                */
                $params_array['commerce_id'] = $commerce->id;
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();

                DB::insert('insert into services (commerce_id, category_id, name, description, price, created_at, updated_at) values (?,?,?,?,?,?,?)', [
                    $params_array['commerce_id'],
                    $params_array['category_id'],
                    $params_array['name'],
                    $params_array['description'],
                    $params_array['price'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'service' => $params_array
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envia los datos correctamente'
            ];
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // Recoger los datos por Service
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);

        // Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectamente'
        );

        if (!empty($params_array)) {
            // Validar datos
            $validate = \Validator::make($params_array, [
                'commerce_id' => 'required',
                'category_id' => 'required',
                'name' => 'required',
                'description' => 'required',
                'price' => 'required'
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['commerce_id']);
            unset($params_array['created_at']);

            // Conseguir comercio identificado
            $commerce = $this->getIdentity($request);

            /* Buscar el registro a actualizar
            $service = Service::where('id', $id)
                    ->where('commerce_id', $commerce->id)
                    ->first();
            */
            $service = DB::select('select * from services where id = ?', [$id]);
            $category = DB::select('select * from categories where id = ?', [$params_array['category_id']]);

            if ((count($service) > 0) && (count($category) > 0)) {

                /* Actualizar el registro en concreto
                $service->update($params_array);
                */

                $params_array['id'] = $id;
                $params_array['updated_at'] = new \DateTime();

                DB::update('update services set  category_id = ?, name = ?, description = ?, price = ?, updated_at = ? where id = ?', [
                    $params_array['category_id'],
                    $params_array['name'],
                    $params_array['description'],
                    $params_array['price'],
                    $params_array['updated_at'],
                    $params_array['id']
                ]);

                // Devolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'service' => $service,
                    'changes' => $params_array
                );
            }

            /*
            $where = [
                'id' => $id,
                'commerce_id' => $commerce->sub
            ];
            $Service = Service::updateOrCreate($where, $params_array);
             *
             */
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        /* Conseguir el registro
        $Service = Service::where('id', $id)->first();
        */
        $service = DB::select('select * from services where id = ?', [$id]);

        if (count($service) > 0) {
            /* Borrarlo
            $Service->delete();
            */
            DB::delete('delete from services where id=?', [$id]);
            // Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'service' => $service
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El Service no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $commerce = $jwtAuth->checkToken($token, true);

        return $commerce;
    }

    public function getServicesByCommerce($id)
    {
        $services = DB::select('select * from services where commerce_id = ?', [$id]);

        return response()->json([
            'status' => 'success',
            'services' => $services
        ], 200);
    }
}