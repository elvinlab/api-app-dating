<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // Con esto podemos hacer consultas por sql
use App\Helpers\JwtAuth;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getValidPromotion',
            'getPromotionsByCommerce'
        ]]);}

    public function index()
    {
        $promotions = DB::select(
            'select * from commerces
            INNER JOIN promotions ON commerces.id = promotions.commerce_id'
        );

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'promotions' => $promotions
        ], 200);
    }

    public function show($id)
    {
        $promotion = DB::select('select * from promotions where id = ?', [$id]);

        if (count($promotion) > 0) {

            $commerce = DB::select('select * from commerces where id = ?', [$promotion[0]->commerce_id]);
            $data = [
                'code' => 200,
                'status' => 'success',
                'promotion' => $promotion,
                'commerce' => $commerce,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La promocion no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger datos por Promotion
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //var_dump($params); die();

        if (!empty($params_array)) {
            // Conseguir usuario identificado
            $commerce = $this->getIdentity($request);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'coupon' => 'required|unique:promotions',
                'max' => 'required',
                'expiry' => 'required',
                'description' => 'required',
                'discount' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la promocion, faltan datos',
                    'error' => $validate->errors()
                ];
            } else {
                /*Guardar el promocion
                $promotion = new Promotion();
                $promotion->commerce_id = $commerce->id;
                $promotion->coupon = $params->coupon;
                $promotion->max = $params->max;
                $promotion->expiry = $params->expiry;
                $promotion->description = $params->description;
                $promotion->image = $params->image;
                $promotion->discount = $params->discount;
                $promotion->save();
                */
                $params_array['commerce_id'] = $commerce->id;
                strtotime($params_array['expiry']);
                $params_array['created_at'] = new \DateTime();
                $params_array['updated_at'] = new \DateTime();
                    
                DB::insert('insert into promotions (commerce_id, coupon, max, amount, expiry, description, discount, image, created_at, updated_at) values (?,?,?,?,?,?,?,?,?,?)', [
                    $params_array['commerce_id'],
                    $params_array['coupon'],
                    $params_array['max'],
                    $params_array['amount'],
                    $params_array['expiry'],
                    $params_array['description'],
                    $params_array['discount'],
                    $params_array['image'],
                    $params_array['created_at'],
                    $params_array['updated_at']
                ]);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'Promotion' => $params_array
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
        // Recoger los datos por Promotion
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);

        // Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        );

        if (!empty($params_array)) {
            // Validar datos
            $validate = \Validator::make($params_array, [
                'commerce_id' => 'required',
                'max' => 'required',
                'expiry' => 'required',
                'description' => 'required',
                'discount' => 'required',
                'image' => 'required',
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['commerce_id']);
            unset($params_array['created_at']);

            // Conseguir usuario identificado
            $commerce = $this->getIdentity($request);

            /* Buscar el registro a actualizar
            $promotion = Promotion::where('id', $id)->first();
            */
            //Buscar el registro a actualizar
            $promotion = DB::select('select * from promotions where id = ?', [$id]);


            if ((count($promotion) > 0)) {

                /* Actualizar el registro en concreto
                $promotion->update($params_array);
              */

                $params_array['id'] = $id;
                $params_array['updated_at'] = new \DateTime();

                DB::update('update promotions set max = ?, amount = ?, expiry = ?, description = ?, discount = ?, image = ?,  updated_at = ? where id = ?', [
                    $params_array['max'],
                    $params_array['amount'],
                    $params_array['expiry'],
                    $params_array['description'],
                    $params_array['discount'],
                    $params_array['image'],
                    $params_array['updated_at'],
                    $params_array['id']
                ]);

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

    public function destroy($id, Request $request)
    {
        /* Conseguir el registro
        $promotion = Promotion::where('id', $id)->first();
        */

        $promotion = DB::select('select * from promotions where id = ?', [$id]);

        if (count($promotion) > 0) {
            /*Borrarlo
            $promotion->delete();
            */

            DB::delete('delete from promotions where id=?', [$id]);

            // Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'Promotion' => $promotion
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El Promotion no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // Recoger la imagen de la petición

        $image = $request->file('file0');

        // Validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('promotions')->put($image_name, \File::get($image));

            /*Guardamos el nombre de la imagen en la base de datos
             Promotion::where('id', $id)->update(array('image' => $image_name));
            */

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        // Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        // Comprobar si existe el fichero
        $isset = \Storage::disk('promotions')->exists($filename);

        if ($isset) {
            // Conseguir la imagen
            $file = \Storage::disk('promotions')->get($filename);

            // Devolver la imagen
            return new Response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getPromotionsByCommerce($id)
    {
        $promotions = DB::select('select * from promotions where commerce_id = ?', [$id]);

        return response()->json([
            'status' => 'success',
            'promotions' => $promotions
        ], 200);
    }



    public function getValidPromotion($expiry)
    {
        strtotime($expiry);
        $promotions = DB::select('select * from commerces
        INNER JOIN promotions  where (expiry >= ? AND amount < max) and commerces.id = promotions.commerce_id', [$expiry]);

        return response()->json([
            'status' => 'success',
            'promotions' => $promotions
        ], 200);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $commerce = $jwtAuth->checkToken($token, true);

        return $commerce;
    }
}