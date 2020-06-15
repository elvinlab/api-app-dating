<?php
namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
    use App\Helpers\JwtAuth;

    class AppointmentController extends Controller
    {
        public function __construct()
        {
            $this->middleware('api.auth');
        }
    
        public function index()
        {
            $appointments = DB::select('select * from appointments');
    
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'appointments' => $appointments
            ]);
        }

        public function show($id)
        {
            $appointment = DB::select('select * from appointments where id = ?', [$id]);

            if (count($appointment) > 0) {
                $service = DB::select('select * from services where id = ?', [$appointment[0]->service_id]);
                $commerce = DB::select('select * from commerces where id = ?', [$appointment[0]->commerce_id]);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'appointment' => $appointment,
                    'service' => $service,
                    'commerce' => $commerce,
                ];
            } else {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'La cita no existe.'
                ];
            }

            return response()->json($data, $data['code']);
        }


    public function destroy($id, Request $request)
    {
        /* Conseguir el registro
        $promotion = Promotion::where('id', $id)->first();
        */

        $appointment = DB::select('select * from appointments where id = ?', [$id]);

        if (count($appointment) > 0) {
            /*Borrarlo
            $promotion->delete();
            */

            DB::delete('delete from appointments where id=?', [$id]);

            // Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'appointment' => $appointment
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El cita no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }


        public function store(Request $request)
        {
            // Recoger datos por Appointment
            $json = $request->input('json', null);//la respuesta lo convierte a JSON
            $params = json_decode($json);//Decodifica ese JSON a objeto
            $params_array = json_decode($json, true);//Pasa ese JSON a array

            //var_dump($params); die();

            if (!empty($params_array)) {
                // Conseguir comercio identificado
                $client = $this->getIdentity($request);

                // Validar los datos
                $validate = \Validator::make($params_array, [
                    'commerce_id' => 'required',
                    'service_id' => 'required',
                    'schedule_day' => 'required',
                    'schedule_hour' => 'required',
                ]);

                if ($validate->fails()) {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No se ha guardado la cita, faltan datos',
                        'error' => $validate->errors()
                    ];
                } else {
                    /* Guardar el articulo
        $Appointment = new Appointment();
        $Appointment->client_id = $client->id;
        $Appointment->commerce_id = $params->commerce_id;
        $Appointment->service_id = $params->service_id;
        $Appointment->status = 'PENDIENTE';
        $Appointment->schedule_day = $params->schedule_day;
        $Appointment->schedule_hour = $params->schedule_hour;
        $Appointment->save();
        */

                    $params_array['client_id'] = $client->id;
                    $params_array['status'] = 'PENDIENTE';
                    $params_array['created_at'] = new \DateTime();
                    $params_array['updated_at'] = new \DateTime();

                    DB::insert('insert into appointments (client_id, commerce_id, service_id, schedule_day, schedule_hour, status, created_at, updated_at) values (?,?,?,?,?,?,?,?)', [
                        $params_array['client_id'],
                        $params_array['commerce_id'],
                        $params_array['service_id'],
                        $params_array['schedule_day'],
                        $params_array['schedule_hour'],
                        $params_array['status'],
                        $params_array['created_at'],
                        $params_array['updated_at']
                    ]);
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'Appointment' => $params_array
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
            // Recoger los datos por Appointment
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
                    'schedule_day' => 'required',
                    'schedule_hour' => 'required',
                ]);

                if ($validate->fails()) {
                    $data['errors'] = $validate->errors();
                    return response()->json($data, $data['code']);
                }

                // Eliminar lo que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['client_id']);
                unset($params_array['status']);
                unset($params_array['created_at']);

                // Conseguir usuario identificado
                $client = $this->getIdentity($request);

                /*Buscar el registro a actualizar
    $appointment = Appointment::where('id', $id)->first();
    */
                $appointment = DB::select('select * from appointments where id = ?', [$id]);
                $service = DB::select('select * from services where id = ?', [$params_array['service_id']]);
                $commerce = DB::select('select * from commerces where id = ?', [$params_array['commerce_id']]);

                if ((count($appointment) > 0) && (count($service) > 0) && (count($commerce) > 0)) {

                    $params_array['id'] = $id;
                    $params_array['updated_at'] = new \DateTime();

                    DB::update('update appointments set  commerce_id = ?, service_id = ?, schedule_day = ?, schedule_hour = ?, updated_at = ? where id = ?', [
                        $params_array['commerce_id'],
                        $params_array['service_id'],
                        $params_array['schedule_day'],
                        $params_array['schedule_hour'],
                        $params_array['updated_at'],
                        $params_array['id']
                    ]);

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


        public function changeStatus(Request $request)
        {
            // Recoger los datos por Appointment
            $json = $request->input('json', null);
            $params_array = json_decode($json, true);

        DB::update('update appointments set  status = ? where id = ?', [$params_array['status'], $params_array['id']]);
  
        $data = array(
            'code' => 200,
            'status' => 'success',
        );
            
            return response()->json($data, $data['code']);
        }


        public function getAppointmentsByClientRecord($id)
        {
     
            $appointments = DB::select('SELECT  commerces.name_commerce, services.name, services.price,  appointments.client_id, appointments.id, appointments.commerce_id,appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE clients.id = ?', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }
        public function getAppointmentsByClientConfirmed($id)
        {
     
            $appointments = DB::select('SELECT  commerces.name_commerce, services.name, services.price,  appointments.client_id, appointments.id, appointments.commerce_id,appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE  (clients.id = ? AND appointments.status = "CONFIRMADA")', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        public function getAppointmentsByClientCanceled($id)
        {
     
            $appointments = DB::select('SELECT  commerces.name_commerce, services.name, services.price,  appointments.client_id, appointments.id, appointments.commerce_id,appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE (clients.id = ? AND appointments.status = "CANCELADA")', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        public function getAppointmentsByClientPending($id)
        {
            $appointments = DB::select('SELECT  commerces.name_commerce, services.name, services.price,  appointments.client_id, appointments.id, appointments.commerce_id, appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE (clients.id = ? AND appointments.status = "PENDIENTE")', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        public function getAppointmentsByCommercePending($id)
        {
            $appointments = DB::select('SELECT  commerces.name_commerce, clients.name AS nameClient, services.price,  services.name ,clients.phone, appointments.client_id, appointments.id, appointments.commerce_id, appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE (commerces.id = ? AND appointments.status = "PENDIENTE")', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        public function getAppointmentsByCommerceValid($expiry, Request $request)
        {
            $commerce = $this->getIdentity($request);
            $appointments = DB::select('SELECT  commerces.name_commerce, clients.name AS nameClient, services.price,  services.name ,clients.phone, appointments.client_id, appointments.id, appointments.commerce_id, appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE (commerces.id = ? AND appointments.status = "CONFIRMADA" AND  schedule_day >= ?)', [$commerce->id, $expiry]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        public function getAppointmentsByCommerceRecord($id)
        {
     
            $appointments = DB::select('SELECT commerces.name_commerce, clients.name AS nameClient, clients.phone, services.name, services.price,  appointments.client_id, appointments.id, appointments.commerce_id, appointments.schedule_day, appointments.schedule_hour, appointments.updated_at, appointments.created_at, appointments.status
            FROM appointments
            INNER JOIN  commerces ON commerces.id = appointments.commerce_id 
            INNER JOIN  services ON services.id = appointments.service_id
            INNER JOIN  clients ON clients.id = appointments.client_id
            WHERE commerces.id = ? ', [$id]);
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
          
            ], 200);
        }

        private function getIdentity($request)
        {
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $client = $jwtAuth->checkToken($token, true);

            return $client;
        }

    }