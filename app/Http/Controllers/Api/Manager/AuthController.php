<?php

namespace App\Http\Controllers\Api\Manager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\Webapp\SignupActivate;
use App\Http\Controllers\Controller;
use Avatar;
use Storage;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = new User([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'name' => $request->lastname . ' ' . $request->firstname ,
            'phone' => $request->phone ,
            'username' => $request->email,
            'email' => $request->email,
            'active' => 1 , // Unactive user
            'password' => bcrypt($request->password),
            'activation_token' => Str::random(32)
        ]);

        $user->save();

        $avatar = Avatar::create($user->name)->getImageObject()->encode('png');
        $uniqeName = Storage::putFile( 'avatars/'.$user->id , new File( (string) $avatar ) );
        $user->avatar_url = $uniqeName ;
        $user->save();

        /**
         * Create detail information of the owner of the account
         */
        $person = \App\Models\People::create([
            'firstname' => $user->firstname , 
            'lastname' => $user->lastname , 
            'gender' => $user->gender , 
            'dob' => $user->dob , 
            'mobile_phone' => $user->mobile_phone , 
            'email' => $user->email , 
            'image' => $user->avatar_url , 
        ]);
        $user->people_id = $person->id ;
        $user->save();
        
        $user->notify(new SignupActivate($user));

        return response()->json([
            'ok' => true ,
            'record' => $user ,
            'message' => 'គណនីបានបង្កើតដោយជោគជ័យ !'
        ], 200);
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);
        $credentials['deleted_at'] = null;

        if(!Auth::attempt($credentials)){
            if( User::where('email', $request->email) != null ){
                /**
                 * Account does exist but the password might miss type
                 */
                return response()->json([
                    'message' => 'សូមពិនិត្យពាក្យសម្ងាត់របស់អ្នក !'
                ], 403);
            }else{
                /**
                 * Account does exist but the password might miss type
                 */
                return response()->json([
                    'message' => 'អ៊ីមែលរបស់អ្នកមិនមានក្នុងប្រព័ន្ធឡើយ !'
                ], 403);
            }
        }

        /**
         * Retrieve account
         */
        $user = $request->user();

        /**
         * Check disability
         */
        if( $user->active <= 0 ) {
             /**
             * Account has been disabled
             */
            return response()->json([
                'message' => 'គណនីនេះត្រូវបានបិទជាបណ្ដោះអាសន្ន។'
            ], 403);
        }
        /**
         * Check roles
         */
        if( empty( array_intersect( $user->roles->pluck('id')->toArray() , \App\Models\Role::where('tag','store')->where('name','Store Manager')->pluck('id')->toArray() ) ) ){
            /**
             * User seem does not have any right to login into backend / core service
             */
            return response()->json([
                'message' => "គណនីនេះមិនមានសិទ្ធិគ្រប់គ្រាន់។"
            ],403);
        }
        
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $user = Auth::user();
        if( $user ){
            if( $user->avatar_url !== null && Storage::disk('public')->exists( $user->avatar_url ) ){
                $user->avatar_url = Storage::disk("public")->url( $user->avatar_url  );
            }else{
                $user->avatar_url = null ;
            }
        }

        return response()->json([
            'ok' => true ,
            'token' => [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ],
            'record' => $user ,
            'message' => 'ចូលប្រើប្រាស់បានជោគជ័យ !'
        ],200);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'អ្នកបានចាកចេញដោយជោគជ័យ !'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'កូតបញ្ជាក់គណនីនេះមិនត្រឹមត្រូវឡើយ !'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return response()->json(
            [
                'message' => 'តំណើរចុះឈ្មោះបានបញ្ចប់ដោយជោគជ័យ! សូមចូលប្រើប្រាស់។' ,
                'ok' => true ,
                'record' => $user
            ],200);
    }


}