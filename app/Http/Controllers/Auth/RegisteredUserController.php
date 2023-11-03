<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Creditor;
use App\Models\FinancialInstitute;
use App\Models\User;
use App\Models\Parameter;
use App\Models\Staff;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        //dd(Parameter::USER_TYPES);
        $url = 'http://127.0.0.1:8000/api/bank';

        $response = Http::get($url);
        $result = $response->json();
        return view('auth.register', compact('result'),['types' => Parameter::USER_TYPES, 'levels' => Parameter::USER_LEVELS]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'name' => ['required', 'string', 'max:255'],
        //     'type' => ['required', 'string', 'max:255'],
        //     'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        //     'image' => ['required'],
        //     'password' => ['required', 'confirmed', Rules\Password::defaults()],
        // ]);

        // if($request->hasfile('image')){

        //     $file = $request->file('image');
        //     $extension=$file ->getClientOriginalExtension();
        //     $filename=time() . '.' . $extension;
        //     $file->move('uploads/projet',$filename);
        //     $projet->image=$filename;
        // }else{

        //     return $request;
        //     $projet->image='';
        // }

        if ($request->type == 'customer') {

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'image' => ['required'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
    

            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->type = $request->type;
            $user->password = Hash::make($request->password);

            if ($request->hasfile('image')) {

                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/idPic', $filename);
                $user->image = $filename;
            } else {

                return $request;
                $user->image = '';
            }

            $user->save();
            $staff = new Staff;
            $staff->staff_id = $request->employee_number;
            $staff->user_id = $user->id;
            $staff->save();

        } else {

            $request->validate([
                'bankname' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'image' => ['required'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
    

            $user = new User;
            $user->name = $request->name;
            $user->bank_name = $request->input('bankname');
            $user->email = $request->email;
            $user->type = $request->type;
            $user->password = Hash::make($request->password);

            if ($request->hasfile('image')) {

                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/idPic', $filename);
                $user->image = $filename;
            } else {

                return $request;
                $user->image = '';
            }

            $user->save();
            $bank = new FinancialInstitute;
            $bank->name = $request->input('bankname');
            $bank->save();
            $creditor = new Creditor;
            $creditor->type = 'admin';
            $creditor->level = 0;
            $creditor->financial_institute_id = $bank->id;
            $creditor->user_id = $user->id;
            $creditor->save();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
