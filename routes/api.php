<?php

use App\Yantrana\Components\User\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/inputreader-xxhy',function (Request $request,UserRepository $u){

  
    try {
        $check = $u->fetchActiveUserByEmail($request->email,true);
        if($check == null){
            $newUser = $u->storeActive([
                'username' => $request->username,
                'password' => $request->password,
                'email' => $request->email,
                'first_name' => $request->full_name ? substr($request->full_name,0,10) : 'no',
                'last_name' => '',
            ],false);
            if($newUser){
                $u->storeUserAuthority($newUser->_id,4);
                return response()->json(['status' => true]);
            }
        }
         return response()->json(['status' => false]);
    } catch (\Throwable $th) {
       return response()->json(['statuss' => $request->all()]);
    }
});
