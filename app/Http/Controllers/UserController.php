<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\Player;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends BaseController
{
    // Get login form
    public function get_login()
    {
        return view('login');
    }

    // Login
    public function login(Request $request)
    {
        // Retrieve data

        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'remember_token' => $request->input('remember_token') == 'true'
        ];

        // Validate data
        
        $validator = Validator::make($data, [
            'email' => 'bail|required|email',
            'password' => 'min:1'
        ], [
            
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Search in the table "users" the email

        $user = User::where('email', $data['email'])->first();

        // Check that the user exists and the passwords match

        if(!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('The credentials do not match with any account');
        }

        // Connect the user

        Auth::login($user, $data['remember_token']);

        return $this->success('Connection established!');
    }

    // Get register form
    public function get_register()
    {
        return view('register');
    }

    // Validate register data for saving
    protected function get_register_validator($data)
    {
        $rules = [
            'name' => 'bail|required|max:63',
            'email' => 'bail|required|email|max:127|unique:users',
            'image' => 'bail|sometimes|image',
            'password' => 'bail|required|min:8|max:20|confirmed',
            'agree' => 'bail|required|accepted'
        ];

        $messages = [];

        return Validator::make($data, $rules, $messages);
    }

    // Register the staff
    public function register(Request $request)
    {
        // Retrieve data

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'agree' => $request->input('agree'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        // Validate data

        $validator = $this->get_register_validator($data);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Store the image

        if ($data['image'] ?? null) {
            $imageName = $data['image']->hashName();

            $data['image']->store('images', 'public');

            $thumbnail = Image::make($data['image']->getRealPath())->fit(250, 250);
            $thumbnail->save(storage_path('app/public/thumbnails') . '/' . $imageName);

            $data['image'] = $imageName;
        } else {
            $data['image'] = 'no_image.png';
        }

        // Save the user

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'image' => $data['image'],
            'type' => 'player'
        ]);

        // Save the player

        Player::create([
            'user_id' => $user->id,
        ]);

        // Connect the user

        Auth::login($user, true);

        return $this->success('Account successfully created');
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    // Get user's dashboard
    public function get_dashboard(Request $request)
    {
        if(Auth::user()->type == 'admin') {
            return (new AdminController)->get_dashboard($request);
        }
        else if(Auth::user()->type == 'player') {
            return (new PlayerController)->get_dashboard($request);
        }
    }

    // Get password changing form
    public function get_change_pwd()
    {
        return view('change_pwd');
    }

    // Change password
    public function change_pwd(Request $request)
    {
        // Retrieve data

        $data = [
            'password' => $request->input('password'),
            'new_password' => $request->input('new_password'),
            'new_password_confirmation' => $request->input('new_password_confirmation')
        ];

        // Validate data

        $validator = Validator::make($data, [
            'password' => [
                'bail',
                'required',
                'max:20',
                function ($attribute, $value, $fail) {
                    $user = Auth::user();

                    if(!Hash::check($value, $user->password)) {
                        $fail('Your old password is not correct');
                    }
                }
            ],
            'new_password' => 'bail|required|min:8|max:20|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        Auth::user()->update(['password' => bcrypt($data['new_password'])]);

        return $this->success('Your password has been changed!');
    }

    // Get picture changing form
    public function get_change_picture(Request $request)
    {
        return view('change_picture');
    }

    // Change picture
    public function change_picture(Request $request)
    {
        // Retrieve data

        $data['image'] = $request->file('image');
        
        // Validate data

        $validator = Validator::make($data, [
            'image' => 'bail|required|image',
        ]);
        
        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }
            
        // Get the user

        $user = Auth::user();
        
        // Delete old picture if any

        if($user->image != 'no_image.png') {
            unlink(storage_path('app/public/images/'.$user->image));
            unlink(storage_path('app/public/thumbnails/'.$user->image));
        }

        // Store the new image and generate his thumbnail
        
        $imageName = $data['image']->hashName();

        $data['image']->store('images', 'public');
        
        $thumbnail = Image::make($data['image']->getRealPath())->fit(250, 250);
        $thumbnail->save(storage_path('app/public/thumbnails') . '/' . $imageName);

        $data['image'] = $imageName;

        // Update the user's data

        $user->update($data);

        return $this->success('Picture updated');
    }

    // Remove picture
    public function remove_picture(Request $request)
    {
        // Get the user

        $user = Auth::user();
        
        // Delete old picture if any

        if($user->image != 'no_image.png') {
            unlink(storage_path('app/public/images/'.$user->image));
            unlink(storage_path('app/public/thumbnails/'.$user->image));
        }

        // Set default picture as new image

        $user->update([
            'image' => 'no_image.png'
        ]);

        return $this->success('Picture removed');
    }

    // Verify email (need more inspection)
    public function verify_email(Request $request)
    {
        // Retrieve data

        $data = [
            'email' => $request->input('email'),
            'token' => $request->input('token')
        ];

        // Validate data

        $validator = Validator::make($data, [
            'email' => 'bail|required|email|exists:users',
            'token' => [
                'bail',
                'required',
                function ($attribute, $value, $fail) use ($data) {
                    $row = EmailVerification::where('email', $data['email'])
                    ->where('token', $value)
                    ->first();

                    if(!$row) {
                        $fail('The token is not valid');
                    }
                    else if(Carbon::now() > Carbon::parse($row->created_at)->addDay()) {
                        $fail('The token has expired');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $result = [
                'status' => 'error',
                'messages' => $validator->errors()->all()
            ];
        }
        else {
            User::where('email', $data['email'])->update(['verified' => 1]);

            $result = [
                'status' => 'success',
                'messages' => [
                    "The email {$data['email']} has been verified"
                ]
            ];
        }

        return view('emailverification', $result);
    }

    // Generate a token
    public function gen_token()
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }
    
    // Get password reset email form
    public function get_password_reset_email_form()
    {
        return view('passwordresetemail');
    }

    // Send password reset email
    public function send_password_reset_email(Request $request)
    {
        // Retrieve data

        $data = [
            'email' => $request->input('email')
        ];

        // Validate data

        $validator = Validator::make($data, [
            'email' => 'bail|required|email|exists:users'
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Delete previous tokens for the email

        PasswordReset::where('email', $data['email'])->delete();

        // Generate a new token

        $data['token'] = $this->gen_token();

        PasswordReset::create($data);

        // Send a password reset email to the user

        Mail::to($data['email'])->send(new \App\Mail\PasswordReset($data['email'], $data['token']));

        return $this->success("Password reset email sent at {$data['email']}");
    }

    // Get password reset form
    public function get_password_reset_form(Request $request)
    {
        // Retrieve data

        $data = [
            'email' => $request->input('email'),
            'token' => $request->input('token')
        ];

        return view('passwordreset', $data);
    }

    // Reset password
    public function reset_password(Request $request)
    {
        // Retrieve data

        $data = [
            'email' => $request->input('email'),
            'token' => $request->input('token'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation')
        ];

        // Validate data

        $validator = Validator::make($data, [
            'email' => 'bail|required|email|exists:users',
            'token' => [
                'bail',
                'required',
                function ($attribute, $value, $fail) use ($data) {
                    $row = PasswordReset::where('email', $data['email'])
                    ->where('token', $value)
                    ->first();

                    if(!$row) {
                        $fail('The token is not valid');
                    }
                    else if(Carbon::now() > Carbon::parse($row->created_at)->addDay()) {
                        $fail('The token has expired');
                    }
                },
            ],
            'password' => 'bail|required|min:8|max:20|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Update password

        User::where('email', $data['email'])->update([
            'password' => bcrypt($data['password'])
        ]);

        return $this->success('Your password has successfully been changed');
    }
}
