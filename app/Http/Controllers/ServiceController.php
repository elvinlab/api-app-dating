<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Service;
use App\Category;
use App\Helpers\JwtAuth;

class ServiceController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getServicesBycommerce'
        ]]);
    }
    
    public function index(){
        $services = Service::all()->load('commerce', 'category');
        
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'Services' => $services
        ], 200);
    }
    
    public function show($id){
        $service = Service::find($id)->load('commerce','category' );
        
        if(is_object($service)){
           $data = [
                'code' => 200,
                'status' => 'success',
                'Services' => $service
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El servicio no existe.'
            ];
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        // Recoger datos por Service
        $json = $request->input('json', null);//la respuesta lo convierte a JSON
        $params = json_decode($json);//Decodifica ese JSON a objeto
        $params_array = json_decode($json, true);//Pasa ese JSON a array

        //var_dump($params); die();
        
        if(!empty($params_array)){
            // Conseguir comercio identificado
            $commerce = $this->getIdentity($request);
            
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'commerce_id'=>'required',
                'category_id'=>'required',
                'name'=>'required',
                'description'=>'required',
                'price'=>'required'

            ]);
            
            if($validate->fails()){
                $data = [
                  'code' => 400,
                  'status' => 'error',
                  'message' => 'No se ha guardado el Servicio, faltan datos',
                  'error' => $validate->errors()
                ];
            }else{
                // Guardar el articulo
                $service = new Service();
                $service->commerce_id = $params->commerce_id;
                $service->category_id = $params->category_id;
                $service->name = $params->name;
                $service->description = $params->description;
                $service->price = $params->price;
                $service->save();
                
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Service' => $service
                  ];
            }
            
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envia los datos correctamente'
              ];
        }
        
        // Devolver respuesta
        return response()->json($data, $data['code']);
    }
    
    public function update($id, Request $request){
        // Recoger los datos por Service
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);

        // Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectamente'
        );
        
        if(!empty($params_array)){
            // Validar datos
            $validate = \Validator::make($params_array, [
                'commerce_id'=>'required',
                'category_id'=>'required',
                'name'=>'required',
                'description'=>'required',
                'price'=>'required'
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            
            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['commerce_id']);
            unset($params_array['created_at']);
            unset($params_array['commerce']);
            
            // Conseguir usuario identificado
            $commerce = $this->getIdentity($request);

            // Buscar el registro a actualizar
            $service = Service::where('id', $id)
                    ->where('commerce_id', $commerce->id)
                    ->first();


            if(!empty($service) && is_object($service)){
                
                // Actualizar el registro en concreto
                $service->update($params_array);
              
                // Devolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'Service' => $service,
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
    
    public function destroy($id, Request $request){
        // Conseguir usuario identificado
        $commerce = $this->getIdentity($request);

        //  Conseguir el registro
        $Service = Service::where('id', $id)
                    ->where('commerce_id', $commerce->id)
                    ->first();
        
        if(!empty($Service)){
            // Borrarlo
            $Service->delete();

            // Devolver algo
            $data = [
              'code' => 200,
              'status' => 'success',
              'Service' => $Service
            ];
        }else{
            $data = [
              'code' => 404,
              'status' => 'error',
              'message' => 'El Service no existe'
            ]; 
        }
        
        return response()->json($data, $data['code']);
    }
    
    private function getIdentity($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $commerce = $jwtAuth->checkToken($token, true);
        
        return $commerce;
    }
    
    
    public function getServicesBycommerce($id){
        $Services = Service::where('commerce_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'Services' => $Services
        ], 200);
    }
}
