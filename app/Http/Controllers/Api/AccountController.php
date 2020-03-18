<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Response;
use Illuminate\Support\Facades\Hash;
use App\PasswordReset;
use Mail;
use App\Mail\ForgotPassword;
class AccountController extends Controller
{
    public function register(Request $request){
        $emailchk=User::where('email',$request->email)->first();
        $usernamechk=User::where('username',$request->username)->first();
        $phonechk=User::where('phonenumber',$request->phonenumber)->first();
        if ($emailchk)
        {
            return Response::json(['success' => '0','validation'=>'0','message' => 'Email already exist']);
        }
        elseif($usernamechk){
            return Response::json(['success' => '0','validation'=>'0','message' => 'Usernaem already exist']);
        }
        elseif($phonechk){
            return Response::json(['success' => '0','validation'=>'0','message' => 'Phonenumber already exist']);
        }
        else{
            $obj=new User();
            $obj->username=$request->username;
            $obj->email=$request->email;
            $obj->phonenumber=$request->phonenumber;
            $obj->password=Hash::make($request->password);
            if($obj->save()){
                return Response::json(['success'=>'1','message'=>'Account Successfully Created']);
            }
            else{ 
                return Response::json(['success'=>'0','message'=>'Something is wrong! Please try again']);
            }
        }
        
    }

    public function login(Request $request){
        $user=User::where('email',$request->email)->first();
        if($user){
            if($user->status=='1'){
                if(Hash::check($request->password,$user->password)){
                    return Response::json(['success' => '1','message' => 'Logedin successfully','data'=>$user]);
                }
                else{
                    return Response::json(['success'=>'0','message'=>'Invalid email or password!']);

                }
            }
            elseif($user->status=='0'){
                return Response::json(['success' => '0','message' => 'Your Account is blocked by admin!']);
            }
            else{
                return Response::json(['success'=>'0','message'=>'Invalid email or password!']);
            }
        }
        else{
            return Response::json(['success'=>'0','message'=>'Invalid email or password!']);
        }
    }

    public function sendcode(Request $request){
        if(User::where('email',$request->email)->first()){
            PasswordReset::where('email', $request->email)->delete();
            $token = PasswordReset::forceCreate([
                'email' => $request->email,
                'code' => rand(1000,9999)
            ]);
            Mail::to($request->email)->send(new ForgotPassword($token));
            
                 return Response::json(['success'=>'1','message'=>'We have sent code to your email']);
            
        }else{
            return Response::json(['success'=>'0','message'=>'This email does not belong to any account']);

        }
    }

    public function verifycode(Request $request){
        $user=PasswordReset::where('email',$request->email)->first();
        if($user){
            $verify=PasswordReset::where('code',$request->code)->first();
            if($verify){
                $user=User::where('email',$user->email)->first();
                $user->status=1;
                if($user->save()){
                    PasswordReset::where('email', $request->email)->delete();
                return Response::json(['success'=>'1','message'=>'Your account has been verified']); 
                }
            }else{
                return Response::json(['success'=>'0','message'=>'Your verification code is wrong!']); 
            }
        }else{
            return Response::json(['success'=>'0','message'=>'Something is wrong!']); 

        }
    }

    public function userprofile(Request $request){
        $user=User::find($request->id);
        $user->username=$request->username;
        $user->email=$request->email;
        $user->phonenumber=$request->phonenumber;
        $user->country=$request->country;
        $user->city=$request->city;
        $user->gender=$request->gender;
        $user->password=Hash::make($request->password);
        if($user->save()){
            return Response::json(['success'=>'1','message'=>'Your profile has been saved']); 
        }else{
            return Response::json(['success'=>'0','message'=>'Something is wrong!']); 

        }

    }



}
