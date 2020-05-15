<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Sale;

class SaleController extends Controller {
    
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index','show']]);
    }

    public function index() {
        $sales = Sale::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'sales' => $sales
        ]);
    }

    public function show($id) {
        $sales = Sale::find($id);

        if (is_object($Sale)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'Sale' => $sales
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Las ventas no existen.'
            ];
        }

        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'service_id' => 'required',
                'amount'=>'required',
            ]);

            // Guardar la ventas
            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la venta.'
                ];
            }else{
                $Sale = new Sale();
                $Sale->service_id = $params_array['service_id'];
                $Sale->amount = $params_array['amount'];
                $Sale->save();

                 $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Sale' => $Sale
                ];
            }
        }else{
            $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No has enviado ninguna venta.'
                ];
        }
           
        // Devolver resultado
        return response()->json($data, $data['code']);
    }
    
    public function update($id, Request $request){
        // Recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'service_id' => 'required',
                'amount'=>'required',
            ]);

            // Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            // Actualizar el registro(ventas)
            $sale = Sale::where('id', $id)->update($params_array);
            
            $data = [
                'code' => 200,
                'status' => 'success',
                'Sale' => $params_array
            ];
            
        }else{
            $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No has enviado ninguna venta.'
                ];
        }
        
        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

   //NO SE ELIMINAN LOS REGISTROS DE VENTA
}
