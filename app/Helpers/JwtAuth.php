<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Client;
use App\Commerce;

class JwtAuth{
    
    public $key;
    
    public function __construct() {
        $this->key = 'Proyecto_app_dating_Decimoinc_client_commerce_team_Adrian_Elvin_Josesteban_2020';
    }
    
    public function signup($role, $email, $password, $getToken = null){
    
        if($role == 'ROLE_CLIENT'){
        
         // Buscar si existe el cliente con sus credenciales
        
       /*
         $account = Client::where([
            'email' => $email,
            'password' => $password
         ])->first();
         */

         //Buscando con SQL
         $results = DB::select('select email, password from clients where email = :email and password = :password', 
         ["email" => $email, "password"=>$password]);

         $client  = new Client();

        // Comprobar si son correctas(objeto)
        if(count($results) > 0){
            $token = array(
                'id'      =>      $client->id,
                'email'   =>      $client->email,
                'name'    =>      $client->name,
                'surname' =>      $client->surname,
                'role'    =>      $client->role,
                'phone'   =>      $client->phone,
                'address' =>      $client->address,
                'image'   =>      $client->image,
                'iat'     =>      time(),
                'exp'     =>      time() + (30 * 24 * 60 * 60)
              );
        }
        
        }else if($role == 'ROLE_COMMERCE'){

        /* Buscar si existe el cliente con sus credenciales
        $account = Commerce::where([
        'email' => $email,
        'password' => $password
        ])->first();
        */
          //Buscando con SQL
          $results = DB::select('select email, password from commerces where email = :email and password = :password', 
          ["email" => $email, "password"=>$password]);

          $commerce  = new Commerce();
             // Comprobar si son correctas(objeto)
             if(count($results) > 0){
                $token = array(
                    'id'             =>   $commerce->id,
                    'email'          =>   $commerce->email,
                    'name_owner'     =>   $commerce->name_owner,
                    'name_commerce'  =>   $commerce->name_commerce,
                    'role'           =>   $commerce->role,
                    'cell'           =>   $commerce->cell,
                    'tell'           =>   $commerce->tell,
                    'recovery_email' =>   $commerce->recovery_email,
                    'description'    =>   $commerce->description,
                    'address'        =>   $commerce->address,
                    'image'          =>   $commerce->image,
                    'iat'            =>   time(),
                    'exp'            =>   time() + (30 * 24 * 60 * 60)
                  );
             }
        }

        // Generar el token con los datos del cliente idenficado
        if(count($results) > 0){
            
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
    
            // Devoler los datos decodificados o el token, en función de un parametro
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }
            
        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto.'
            );
        }
        
        return $data;
    }

    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        try{
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded->id)){
          
            $auth = true;  
        }else{
            $auth = false;
        }
        
        if($getIdentity){
            return $decoded;
        }
        
        return $auth;
    }
    
}