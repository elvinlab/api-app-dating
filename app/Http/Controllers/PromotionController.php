<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Promotion;
use App\Helpers\JwtAuth;

class PromotionController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPromotionsBycommerce'
        ]]);
    }
    
    public function index(){
        $promotions = Promotion::all()->load('commerce');
        
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'Promotions' => $promotions
        ], 200);
    }
    
    public function show($id){
        $promotion = Promotion::find($id)->load('commerce');
        
        if(is_object($promotion)){
           $data = [
                'code' => 200,
                'status' => 'success',
                'Promotions' => $promotion
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ];
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        // Recoger datos por Promotion
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //var_dump($params); die();
        
        if(!empty($params_array)){
            // Conseguir usuario identificado
            $commerce = $this->getIdentity($request);
            
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'commerce_id'=>'required',
                'coupon'=>'required',
                'max'=>'required',
                'expiry'=>'required',
                'description'=>'required',
                'discount'=>'required',
                'image'=>'required',

            ]);
            
            if($validate->fails()){
                $data = [
                  'code' => 400,
                  'status' => 'error',
                  'message' => 'No se ha guardado la promotion, faltan datos',
                  'error' => $validate->errors()
                ];
            }else{
                // Guardar el articulo
                $promotion = new Promotion();
                $promotion->commerce_id = $params->commerce_id;
                $promotion->coupon = $params->coupon;
                $promotion->max = $params->max;
                $promotion->expiry = $params->expiry;
                $promotion->description = $params->description;
                $promotion->image = $params->image;
                $promotion->discount = $params->discount;
                $promotion->save();
                
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Promotion' => $promotion
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
        // Recoger los datos por Promotion
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);

        // Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        );
        
        if(!empty($params_array)){
            // Validar datos
            $validate = \Validator::make($params_array, [
                'commerce_id'=>'required',
                'coupon'=>'required',
                'max'=>'required',
                'expiry'=>'required',
                'description'=>'required',
                'discount'=>'required',
                'image'=>'required',
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
            $promotion = Promotion::where('id', $id)
                    ->where('commerce_id', $commerce->id)
                    ->first();


            if(!empty($promotion) && is_object($promotion)){
                
                // Actualizar el registro en concreto
                $promotion->update($params_array);
              
                // Devolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'Promotion' => $promotion,
                    'changes' => $params_array
                );
            }
            
            /*
            $where = [
                'id' => $id,
                'commerce_id' => $commerce->sub
            ];
            $Promotion = Promotion::updateOrCreate($where, $params_array);
             * 
             */

            
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function destroy($id, Request $request){
        // Conseguir usuario identificado
        $commerce = $this->getIdentity($request);

        //  Conseguir el registro
        $Promotion = Promotion::where('id', $id)
                    ->where('commerce_id', $commerce->id)
                    ->first();
        
        if(!empty($Promotion)){
            // Borrarlo
            $Promotion->delete();

            // Devolver algo
            $data = [
              'code' => 200,
              'status' => 'success',
              'Promotion' => $Promotion
            ];
        }else{
            $data = [
              'code' => 404,
              'status' => 'error',
              'message' => 'El Promotion no existe'
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
    
    public function upload($id, Request $request){
        // Recoger la imagen de la petición

        $image = $request->file('file0');
               
        // Validar imagen
        $validate = \Validator::make($request->all(), [
           'file0' => 'required|image|mimes:jpg,jpeg,png,gif' 
        ]);
        
        // Guardar la imagen
        if(!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            
            \Storage::disk('promotions')->put($image_name, \File::get($image));

             //Guardamos el nombre de la imagen en la base de datos
             Promotion::where('id', $id)->update(array('image' => $image_name));
            
            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        
        // Devolver datos
        return response()->json($data, $data['code']);
    }
    
    public function getImage($filename){
        // Comprobar si existe el fichero
        $isset = \Storage::disk('promotions')->exists($filename);
        
        if($isset){
            // Conseguir la imagen
            $file = \Storage::disk('promotions')->get($filename);
            
            // Devolver la imagen
            return new Response($file, 200);
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }
        
        return response()->json($data, $data['code']);
    }

    public function getPromotionsBycommerce($id){
        $Promotions = Promotion::where('commerce_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'Promotions' => $Promotions
        ], 200);
    }
}
