<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'nombre_usuario' => $request->username,
            'password' => $request->password
        ];

        // Buscar usuario por nombre_usuario
        $user = User::where('nombre_usuario', $request->username)->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Usuario o contraseña incorrectos.'
            ])->withInput();
        }

        // Verificar estado del usuario
        if (!$user->activo) {
            return back()->withErrors([
                'username' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'
            ])->withInput();
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->clave_hash)) {
            return back()->withErrors([
                'username' => 'Usuario o contraseña incorrectos.'
            ])->withInput();
        }

        // Autenticar usuario
        Auth::login($user, $request->filled('remember'));

        // Verificar si necesita cambiar contraseña
        if ($user->cambiar_password) {
            return redirect()->route('password.change');
        }

        return $this->redirectBasedOnRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nueva_clave' => 'required|string|min:8|confirmed',
        ], [
            'nueva_clave.required' => 'La nueva contraseña es obligatoria.',
            'nueva_clave.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'nueva_clave.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $user = Auth::user();
        $user->update([
            'clave_hash' => Hash::make($request->nueva_clave),
            'cambiar_password' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.'
        ]);
    }

    private function redirectBasedOnRole()
    {
        $user = Auth::user();
        
        return match($user->rol) {
            'admin' => redirect()->route('dashboard'),
            'editor' => redirect()->route('alfoli.index'),
            'visualizador' => redirect()->route('dashboard'),
            default => redirect()->route('login')
        };
    }
}