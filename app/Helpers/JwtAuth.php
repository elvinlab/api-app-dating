<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Commerce;

class JwtAuth{
    
    public $key;
    
    public function __construct() {
        $this->key = 'Proyecto_app_dating_Decimoinc_user_commerce_team_Adrian_Elvin_Josesteban_2020';
    }
    
    public function signupUser($email, $password, $getToken = null){
        
        // Buscar si existe el usuario con sus credenciales
         $user = User::where([
                'email' => $email,
                'password' => $password
         ])->first();
         
        // Comprobar si son correctas(objeto)
        $signupUser = false;
        if(is_object($user)){
            $signupUser = true;
        }
        
        // Generar el token con los datos del usuario idenficado
        if($signupUser){
            
            $token = array(
              'id'      =>      $user->id,
              'email'   =>      $user->email,
              'name'    =>      $user->name,
              'surname' =>      $user->surname,
              'role'    =>      $user->role,
              'phone'   =>      $user->phone,
              'address' =>      $user->address,
              'image'   =>      $user->image,
              'iat'     =>      time(),
              'exp'     =>      time() + (7 * 24 * 60 * 60)
            );
            
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

    public function signupCommerce($email, $password, $getToken = null){
        
        // Buscar si existe el commercio con sus credenciales
         $commerce = Commerce::where([
                'email' => $email,
                'password' => $password
         ])->first();
         
        // Comprobar si son correctas(objeto)
        $signupCommerce = false;
        if(is_object($commerce)){
            $signupCommerce = true;
        }
        
        // Generar el token con los datos del usuario idenficado
        if($signupCommerce){
            
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
              'exp'            =>   time() + (7 * 24 * 60 * 60)
            );
            
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