<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){

        //Obtener post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar datos
        $validate = Validator::make($params_array, [
            'email' => 'required|unique:users'

        ]);

        if($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario duplicado, no se puede registrar'
            );

            return response()->json($data,200);
        }


        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'ROLE_USER';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;

        if(!is_null($email) && !is_null($password) && !is_null($name)){

            // Crear el usuario
            $user = new User();
            $user->email = $email;

            $pwd = hash('sha256', $password);
            $user->password = $pwd;

            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;

            //Comprobamos que no este duplicado el usuario
            //$isset_user = User::where('email', '=', $email)->first();

            if($user->save()){
                //Guardamos el usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario registrado correctamente'
                );
            }else{
                //No guardamos por que ya existe
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Usuario duplicado, no se puede registrar'
                );
            }

        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no creado'
            );
        }

        return response()->json($data, 200);
    }

    public function login(Request $request){
        $jwtAuth = new JwtAuth();

        //Recibir POST
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;
        $getToken = (!is_null($json) && isset($params->getToken)) ? $params->getToken : null;

        //Cifrar la contraseña
        $pwd = hash('sha256', $password);

        if(!is_null($email) && !is_null($password) && ($getToken == null || $getToken == 'false')){
            $signup = $jwtAuth->signup($email, $pwd);

        }elseif ($getToken != null){
            $signup = $jwtAuth->signup($email, $pwd, $getToken);

        }else{
            $signup = array(
                'status' => 'error',
                'message' => 'Envia tus dats por post'
            );
        }

        return response()->json($signup,200);

    }
}
