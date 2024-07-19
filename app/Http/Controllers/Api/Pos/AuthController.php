<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Avatar;
use Storage;

class AuthController extends Controller
{
    
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
            if( \App\Models\User::where('email', $request->email)->first() != null ){
                /**
                 * Account does exist but the password might miss type
                 */
                return response()->json([
                    'user' => \App\Models\User::where('email', $request->email)->first() ,
                    'message' => 'សូមពិនិត្យពាក្យសម្ងាត់របស់អ្នក !'
                ], 401);
            }else{
                /**
                 * Account does exist but the password might miss type
                 */
                return response()->json([
                    'message' => 'ពាក្យសម្ងាត់ និង អ៊ីមែលរបស់អ្នកមិនត្រឹមត្រូវឡើយ។'
                ], 401);
            }
        }
            
        // Check role

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
        if( empty( array_intersect( $user->roles->pluck('id')->toArray() , \App\Models\Role::where('tag','store')->pluck('id')->toArray() ) ) ){
            /**
             * System don't check whether the user of the store is owner or staff, but let the user to process terminal / pos
             */
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
            // $user->avatar_url = null ;
            if( $user->avatar_url !== null && Storage::disk('public')->exists( $user->avatar_url ) ){
                $user->avatar_url = Storage::disk("public")->url( $user->avatar_url  );
            }
        }

        /**
         * Get user store
         */
        $user->stores;

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
  
}