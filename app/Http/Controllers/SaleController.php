<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use App\Helpers\JwtAuth;

class SaleController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Request $request)
    {

        // Recoger los datos por venta
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Conseguir comercio identificado
            $commerce = $this->getIdentity($request);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'service_id' => 'required',
                'amount' => 'required',
            ]);

            // Guardar la ventas
            if ($validate->fails() && (count($service) > 0)) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la venta.'
                ];
            } else {

                /*
                $Sale = new Sale();
                $Sale->service_id = $params_array['service_id'];
                $Sale->amount = $params_array['amount'];
                $Sale->save();
                */
                $params_array['commerce_id'] = $commerce->id;
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();

                DB::insert('insert into sales (commerce_id, service_id, amount, created_at, updated_at) values (?,?,?,?,?)', [
                    $params_array['commerce_id'],
                    $params_array['service_id'],
                    $params_array['amount'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Sale' => $params_array
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna venta.'
            ];
        }

        // Devolver resultado
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
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'service_id' => 'required',
                'amount' => 'required',
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            // Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['commerce_id']);
            unset($params_array['created_at']);

            // Conseguir comercio identificado
            $commerce = $this->getIdentity($request);

            //Buscar el registro a actualizar
            $sale = DB::select('select * from sales where id = ?', [$id]);
            $service = DB::select('select * from services where id = ?', [$params_array['service_id']]);

            if ((count($sale) > 0) && (count($service) > 0)) {

                /* Actualizar el registro en concreto
                $sale->update($params_array);
                */

                $params_array['id'] = $id;
                $params_array['updated_at'] = new \DateTime();

                DB::update('update sales set service_id = ?, amount = ?, updated_at = ? where id = ?', [
                    $params_array['service_id'],
                    $params_array['amount'],
                    $params_array['updated_at'],
                    $params_array['id']
                ]);

                // Devolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'Service' => $sale,
                    'changes' => $params_array
                );
            }
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $commerce = $jwtAuth->checkToken($token, true);

        return $commerce;
    }

    public function getSalesByCommerce($id)
    {
        $services = DB::select('select * from sales where commerce_id = ?', [$id]);

        return response()->json([
            'status' => 'success',
            'Services' => $services
        ], 200);
    }
    //NO SE ELIMINAN LOS REGISTROS DE VENTA
}
