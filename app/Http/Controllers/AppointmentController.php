<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Appointment;
use App\Helpers\JwtAuth;

class AppointmentController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getAppointmentsBycommerce',
            'getAppointmentsByclient'
        ]]);
    }
    
    public function index(){
        $appointments = Appointment::all()->load('client','commerce', 'service');
        
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'Appointments' => $appointments
        ], 200);
    }
    
    public function show($id){
        $appointment = Appointment::find($id)->load('client','commerce', 'service');
        
        if(is_object($appointment)){
           $data = [
                'code' => 200,
                'status' => 'success',
                'Appointments' => $appointment
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La cita no existe.'
            ];
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        // Recoger datos por Appointment
        $json = $request->input('json', null);//la respuesta lo convierte a JSON
        $params = json_decode($json);//Decodifica ese JSON a objeto
        $params_array = json_decode($json, true);//Pasa ese JSON a array

        //var_dump($params); die();
        
        if(!empty($params_array)){
            // Conseguir comercio identificado
            $client = $this->getIdentity($request);
            
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'client_id'=>'required',
                'commerce_id'=>'required',
                'service_id'=>'required',
                'schedule_day'=>'required',
                'schedule_hour'=>'required',
            ]);
            
            if($validate->fails()){
                $data = [
                  'code' => 400,
                  'status' => 'error',
                  'message' => 'No se ha guardado la cita, faltan datos',
                  'error' => $validate->errors()
                ];
            }else{
                // Guardar el articulo
                $Appointment = new Appointment();
                $Appointment->client_id = $client->id;
                $Appointment->commerce_id = $params->commerce_id;
                $Appointment->service_id = $params->service_id;
                $Appointment->status = 'PENDIENTE';
                $Appointment->schedule_day = $params->schedule_day;
                $Appointment->schedule_hour = $params->schedule_hour;
                $Appointment->save();
                
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Appointment' => $Appointment
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
        // Recoger los datos por Appointment
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
                'service_id'=>'required',
                'schedule_day'=>'required',
                'schedule_hour'=>'required',
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            
            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['client_id']);
            unset($params_array['status']);
            unset($params_array['created_at']);
            unset($params_array['commerce']);
            
            // Conseguir usuario identificado
            $client = $this->getIdentity($request);

            // Buscar el registro a actualizar
            $appointment = Appointment::where('id', $id)->first();

            if(!empty($appointment) && is_object($appointment)){
                
                // Actualizar el registro en concreto
                $appointment->update($params_array);
              
                // Devolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'Appointment' => $appointment,
                    'changes' => $params_array
                );
            }
            
            /*
            $where = [
                'id' => $id,
                'commerce_id' => $commerce->sub
            ];
            $Appointment = Appointment::updateOrCreate($where, $params_array);
             * 
             */
            
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function destroy($id, Request $request){
        // Conseguir usuario identificado
        $commerce = $this->getIdentity($request);

        //  Conseguir el registro
        $appointment = Appointment::where('id', $id)->first();
        
        if(!empty($appointment)){
            // Borrarlo
            $appointment->delete();

            // Devolver algo
            $data = [
              'code' => 200,
              'status' => 'success',
              'Appointment' => $appointment
            ];
        }else{
            $data = [
              'code' => 404,
              'status' => 'error',
              'message' => 'la cita no existe'
            ]; 
        }
        
        return response()->json($data, $data['code']);
    }
    
    private function getIdentity($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $client = $jwtAuth->checkToken($token, true);
        
        return $client;
    }
    
    public function getAppointmentsBycommerce($id){
        $appointments = Appointment::where('commerce_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'Appointments' => $appointments
        ], 200);
    }

    public function getAppointmentsByclient($id){
        $appointments = Appointment::where('client_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'Appointments' => $appointments
        ], 200);
    }
}
