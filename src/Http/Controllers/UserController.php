<?php
namespace Actinity\Actinite\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController
    extends Controller
{
    public function index()
    {
        return User::orderBy('name')->paginate(50);
    }

    public function me()
    {
        return auth()->user();
    }

    public function show(User $user)
    {
        return $user;
    }

    public function store(Request $request)
    {
        $this->authorize('actinite:admin');

        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|unique:users,email',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = 'not set';
        $user->is_admin = !!$request->is_admin;
        $user->restrict_to_nodes = $request->restrict_to_nodes;
        $user->save();

        Password::broker()->sendResetLink(['email' => $request->email]);

        return $user;

    }

    public function update(User $user, Request $request)
    {
        $this->authorize('actinite:admin');

        $this->validate($request,[
            'name' => 'required',
            'email' => [
                'required',
                Rule::unique('users')->ignore($user),
            ],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_admin = !!$request->is_admin;
        $user->restrict_to_nodes = $request->restrict_to_nodes;
        $user->save();

        return $user;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
