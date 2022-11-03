<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Http\Resources\UserResource;
use App\Http\Requests\api\v1\UserStoreRequest;
use App\Http\Requests\api\v1\UserUpdateRequest;
use App\Http\Requests\api\v1\UserIndexRequest;

use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndexRequest $request)
    {
        if ($request->exists('username'))
        {
            $username = $request->input('username');
            $user = User::findByUsername($username);
            return (new UserResource($user))
                ->response()
                ->setStatusCode(200);
        } elseif ($request->exists('email')){
            $email = $request->input('email');
            $user = User::findByEmail($email);
            return (new UserResource($user))
                ->response()
                ->setStatusCode(200);
        } else {
            $users = User::orderBy('username', 'asc')->get();
            return response()->json(['data' => UserResource::collection($users)], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $username = Str::lower($request->input('username'));
        $email = $request->input('email');
        $password = $request->input('password');
        $password = Hash::make($password);
        $user = User::create(['username'=>$username, 'email'=>$email, 'password'=>$password]);
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, User $user)
    {

        if ($request->exists('username')){
            $username = Str::lower($request->input('username'));
            $user->username = $username;
        }
        if ($request->exists('email')){
            $email = $request->input('email');
            $user->email = $email;
        }
        if ($request->exists('password')){
            $password = $request->input('password');
            $password = Hash::make($password);
            $user->password = $password;
        }
        $user->save();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function get_id_with_token(Request $request)
    {
        $id = Auth::user()->id;
        return response()->json(['data' => [
            'id' => $id
        ]])
            ->setStatusCode(200);
    }
}
