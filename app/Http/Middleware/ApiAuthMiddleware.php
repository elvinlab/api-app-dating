<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {




        // Comprobar si el cliente está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){ 
            return $next($request)
           ->header(‘Access-Control-Allow-Origin’, ‘*’)
           ->header(‘Access-Control-Allow-Methods’, ‘GET, POST, PUT, DELETE, OPTIONS’)
           ->header(‘Access-Control-Allow-Headers’, ‘X-Requested-With, Content-Type, X-Token-Auth, Authorization’);  
        }else{
   
             $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No te encuentras identificado en la app'
            );
            return response()->json($data, $data['code']);
        }
       
    }
}
