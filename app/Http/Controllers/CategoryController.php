<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    //
    public function __construct(){
        //middleware
    }
    public function index(){ //GET
        //Devolvera todos los elementos de categorias
        $data=Category::all();
        $response=array(
            'status'=>'success',
            'code'=>200,
            'data'=>$data
        );
        return response()->json($response,200);
    }
    public function show($id){ //GET
        //Devolvera un elemento por su Id
        $data = Category::find($id);
        if(is_object($data)){
            $response=array(
                'status'=>'success',
                'code'=> 200,
                'data'=>$data
            );
        }else{
            $response=array(
                'status'=>'error',
                'code'=>404,
                'message'=>'Recurso no encontrado'
            );
        }
        return response()->json($response,$response['code']);
    }
    public function store(Request $request){ //POST
        //Guardará un nuevo elemento
        $json=$request->input('json',null);
        $data = json_decode($json,true);//objeto

        if(!empty($data)){
            $data=array_map('trim',$data);//Limpiar datos

            $rules=[
                'name'=>'required|alpha',
                'descripton' =>'required'
            ];

            $validate=\validator($data,$rules);//valida datos

            if($validate->fails()){
                $response=array(
                    'status'=>'error',
                    'code'=>406,
                    'message'=>'La Categoria no se ha creado.',
                    'errors'=>$validate->errors()
                );
            }else{
                $category= new Category();
                $category->name=$data['name'];
                $category->description=$data['descripton'];

                $category->save();//Se guarda la categoria

                $response=array(
                    'status'=>'success',
                    'code'=>201,
                    'message'=>'Datos almacenados satisfactoriamente',
                    'category'=>$category
                );
            }
        }
        else{
            $response=array(
                'status'=>'error',
                'code'=>400,
                'message'=>'Faltan campos.'
            );
        }
        return response()->json($response,$response['code']);
    }
    public function update(Request $request){ //PUT
        //Actualiza un elemento
        $json= $request->input('json',null);
        $data=json_decode($json,true);

        if(!empty($data)){
            $data=array_map('trim',$data);
            //Da
            $rules=[
                'name'=>'required|alpha',
                'descripton'=>'required'
            ];

            $validate=\validator($data,$rules);
            if($validate->fails()){
                $response=array(
                    'status'=>'error',
                    'code'=>406,
                    'message'=>'Los datos son incorrectos',
                    'errors'=>$validate->errors()
                );
            }
            else{
                $id=$data['id'];

                //Sacar parametros
                unset($data['id']);

                $updated=Category::where('id',$id)->update($data);
                if($updated>0){
                    $response=array(
                    'status'=>'success',
                    'code'=>200,
                    'message'=>'Categoria actualizada satisfactoriamente'
                    );
                }else{
                    $response=array(
                        'status'=>'error',
                        'code'=>400,
                        'message'=>'Problemas en la actualización'
                        );
                }
            }
        }else{
            $response=array(
                'status'=>'error',
                'code'=>400,
                'message'=>'faltan parametros'
            );
        }
        return response()->json($response,$response['code']);

    }
    public function destroy($id){ //DELETE
        //Elimina un elemento
        if(isset($id)){
            $deleted=Category::where('id',$id)->delete();
            if($deleted){
                $response=array(
                    'status'=>'success',
                    'code'=>200,
                    'message'=>'Eliminado correctamente'
                    );
            }else{
                $response=array(
                    'status'=>'error',
                    'code'=>400,
                    'message'=>'No se pudo eliminar'
                    );
            }
        }
        else{
            $response=array(
                'status'=>'error',
                'code'=>400,
                'message'=>'Falta el identificador del recurso'
                );
        }
        return response()->json($response,$response['code']);
    }
}