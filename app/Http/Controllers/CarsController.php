<?php

namespace App\Http\Controllers;

use App\Car;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request)
    {
        $hash = $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            $cars = Car::all()->load('user');
            return response()->json(array(
                'cars' => $cars,
                'status' => 'success'
            ),200);
        }else{
            echo "No autenticado"; die();
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $hash = $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Recoger datos por POST
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            // Conseguir el usuario identificado
            $user = $jwtAuth->checkToken($hash, true);

            //Validacion
            $validate = Validator::make($params_array, [
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validate->fails()){
                return response()->json($validate->errors(),400);
            }


            // Guardar el choche
            $car = new Car();
            $car->user_id = $user->sub;
            $car->title = $params->title;
            $car->description = $params->description;
            $car->price = $params->price;
            $car->status = $params->status;

            $car->save();

            $data = array(
                'car' => $car,
                'status' => 'success',
                'code' => 200
            );

        }else{
            //Devolver error

            $data = array(
                'message' => 'Login Incorrecto',
                'status' => 'error',
                'code' => 300
            );
        }

        return response()->json($data, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $car = Car::find($id);
        if(is_object($car)){
            $car = Car::find($id)->load('user');
            return response()->json(array('car' => $car, 'status' => 'success'),200);
        }else{
            return response()->json(array('message' => 'El coche no existe', 'status' => 'error'),200);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $hash = $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken) {
            //Recoger params POST
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            // Validar datos
            $validate = Validator::make($params_array, [
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validate->fails()){
                return response()->json($validate->errors(),400);
            }

            //Actualizar registro
            unset($params_array['id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            $car = Car::where('id', $id)->update($params_array);

            $data = array(
              'car' => $params,
              'status' => 'success',
              'code' => 200
            );

        }else{
            //Devolver error

            $data = array(
                'message' => 'Login Incorrecto',
                'status' => 'error',
                'code' => 300
            );
        }
        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Comprobar que existe el registro
            $car = Car::find($id);

            //Borrarlo
            $car->delete();

            //Devolverlo
            $data = array(
              'car' => $car,
              'status' => 'success',
              'code'  => 200
            );
        }else{
            $data = array(
              'status' => 'error',
              'code' => 400,
              'menssage' => 'Login incorrecto !!'
            );
        }
        return response()->json($data,200);
    }
}
