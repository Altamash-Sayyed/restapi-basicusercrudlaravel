<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($flage)
    {
        //flage =query in url
        $query = User::select('name', 'email');

        if ($flage == 1) {
            $query->where('status', 1);
        } else if ($flage == 0) {
            // $query->where('status',0);
        } else {
            return response()->json(['message' => 'value either 1 or 0'], 200);
        }
        $users = $query->get();
        if (count($users) > 0) {
            $response = [
                'message' => count($users) . " Users found",
                'status' => 1,
                'data' => $users
            ];
            return response()->json($response, 200);
        } else {
            return response()->json(['message' => 'Users Not Found'], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ];
            DB::beginTransaction();
            try {
                // p($data);
                $user = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                p($e->getMessage());
            }
            if ($user != null) {
                return response()->json(['message' => 'User Created Successfully'], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
        // p($request->all());


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        // $user = User::where('id',$id)->get();
        $user = User::find($id);
        if ($user !== null) {
            $response = [
                'message' => "User found",
                'status' => 1,
                'data' => $user
            ];
        } else {
            $response = [
                'message' => "User not found",
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'User Not Found',
                'status' => 0
            ];
            $respCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->name =$request['name'];
                $user->email =$request['email'];
                $user->pincode =$request['pincode'];
                $user->address =$request['address'];
                $user->contact =$request['contact'];
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0,
                    'error_msg'=>$e->getMessage()
                ];
                DB::rollBack();
                $user =null;
                $respCode = 500;
            }
        }
        if(is_null($user)){
            return response()->json($response, $respCode);
        }else{
            $response = [
                'message' => 'User Updated SucceessFully!',
                'status' => 1
            ];
            return response()->json($response, 200);
        }
    }

    public function changePassword(Request $request,$id){
        $user = User::find($id);
        if (is_null($user)) {
           return response()->json( [
                'message' => 'User Not Found',
                'status' => 0
            ], 404);
        } else {
        //
        if($user->password == $request['old_password']){
            if($request['new_password']==$request['confirm_password']){
                DB::beginTransaction();
                try {
                    $user->password =$request['new_password'];
                    $user->save();
                    DB::commit();
                    return response()->json( [
                        'message' => 'password change successfully',
                        'status' => 1
                    ],400);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json( [
                        'message' => 'internal server error',
                        'status' => 0
                    ],400);
                }
            }else{
                return response()->json( [
                    'message' => 'new password and confirm password does not match',
                    'status' => 0
                ],400);
            }
        }else{
           return response()->json( [
                'message' => 'old password does not match',
                'status' => 0
            ],400);
        }

        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => "User not found",
                'status' => 0,
            ];
            $respCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => "User Deleted Succesfully!",
                    'status' => 1,
                ];
                $respCode = 200;
            } catch (\Exception $e) {
                //throw $th;
                $response = [
                    'message' => "Internal Server Error",
                    'status' => 0,
                ];
                DB::rollBack();
            }
        }
        return response()->json($response, $respCode);
    }
}
