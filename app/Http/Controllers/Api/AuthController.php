<?php

namespace App\Http\Controllers\Api;

use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Auth\RegisterRequest;
use App\Models\Auth\SocialAccount;
use App\Models\Auth\User;
use App\Models\Content\Frontend\Address;
use App\Repositories\Frontend\Auth\UserRepository;
use App\Traits\ApiResponser;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    use RegistersUsers, ApiResponser;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', Rule::unique('users')],
            'password' => ['required', 'string', 'Min:8'],
            'g-recaptcha-response' => ['required_if:captcha_status,true', 'captcha'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation fail', 422, [
                'errors' => $validator->errors()
            ]);
        }

        abort_unless(config('access.registration'), 404);
        $name = $request->only('name', 'email', 'password');
        $user = $this->userRepository->create($name, false);

        if (config('access.users.confirm_email') || config('access.users.requires_approval')) {
            // event(new UserRegistered($user));
            $user->sendApiEmailVerificationNotification();
            return $this->success([], config('access.users.requires_approval') ?
                __('exceptions.frontend.auth.confirmation.created_pending') :
                __('exceptions.frontend.auth.confirmation.created_confirm'));
        }

        return $this->success([
            'token' => $user->createToken('API Token')->plainTextToken,
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $attr = $request->validate([
            'email' => 'required|string|email|',
            'password' => 'required|string|min:6'
        ]);

        if (!Auth::attempt($attr)) {
            return $this->error('Credentials not match', 422);
        }
        $user = Auth::user();

        if (!$user->isConfirmed()) {
            Auth::logout();
            // If the user is pending (account approval is on)
            if ($user->isPending()) {
                return $this->success([], __('exceptions.frontend.auth.confirmation.pending'));
            }
            return $this->success([
                'url' => route('frontend.auth.account.confirm.resend', e($user->{$user->getUuidName()}))
            ], __('exceptions.frontend.auth.confirmation.resend'));
        }

        if (!$user->isActive()) {
            Auth::logout();
            return $this->error(__('exceptions.frontend.auth.deactivated'), 401);
        }

        if (config('access.users.single_login')) {
            Auth::logoutOtherDevices($request->password);
        }

        return $this->success([
            'token' => Auth::user()->createToken('API Token')->plainTextToken,
            'user' => $user
        ]);
    }



    public function generateAndSendOTP($phone, $otp)
    {
        try {
            $appUrl = get_setting('site_url', env('APP_URL'));
            if (get_setting('sms_active_otp_message')) {
                $txt = get_setting('sms_otp_message');
                $txt = str_replace('[otp]', $otp, $txt);
                $txt = str_replace('[appUrl]', $appUrl, $txt);
            } else {
                $txt = "Your {$appUrl} One Time Password(OTP) is {$otp} Validity for OTP is 3 minutes. Helpline 01515234363";
            }
            if ($phone) {
                return send_ware_SMS($txt, $phone);
            }
        } catch (\Exception $ex) {
        }
        return false;
    }

    public function submitForTtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation fail', 422, [
                'errors' => $validator->errors()
            ]);
        }
        $otpCode = rand(1000, 9999);
        $phone = request('phone');
        $user = User::where('phone', request('phone'))->first();
        if (!$user) {
            abort_unless(config('access.registration'), 404);
            $name['name'] = $phone;
            $name['phone'] = $phone;
            $name['email'] = "$phone@user.com";
            $name['password'] = "$phone@1234";
            $user = $this->userRepository->create($name, false);
        }

        if ($user) {
            $user->otp_code = $otpCode;
            $user->save();

            $smsResponse = $this->generateAndSendOTP($phone, $otpCode);

            return $this->success([
                'status' => true,
                '$smsResponse' => $smsResponse,
                'message' => "OTP send to your phone",
                'data' => [
                    "phone" => $phone
                ]
            ]);
        }

        return $this->success([
            'status' => false,
            'message' => "OTP send fail !",
            'data' => [
                "phone" => $phone
            ]
        ]);
    }


    public function ResendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation fail', 422, [
                'errors' => $validator->errors()
            ]);
        }
        $otpCode = rand(1000, 9999);
        $phone = request('phone');
        $user = User::where('phone', request('phone'))->first();
        if ($user) {
            $user->otp_code = $otpCode;
            $user->save();
            $smsResponse =  $this->generateAndSendOTP($phone, $otpCode);
            return $this->success([
                'status' => true,
                'message' => "OTP send to your phone",
                'data' => [
                    "phone" => $phone
                ]
            ]);
        }

        return $this->success([
            'status' => false,
            'message' => "OTP send fail !",
            'data' => [
                "phone" => $phone
            ]
        ]);
    }


    public function submitOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20',
            'otp_code' => 'required|string|min:4|max:4'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation fail', 422, [
                'errors' => $validator->errors()
            ]);
        }
        $phone = request('phone');
        $otp_code = request('otp_code');
        $user = User::where('phone', $phone)
            ->where('otp_code', $otp_code)
            ->whereNotNull('active')
            ->select('id', 'name', 'email', 'phone', 'first_name', 'last_name', 'shipping_id', 'billing_id')
            ->first();

        if ($user) {
            return $this->success([
                'token' => $user->createToken('API Token')->plainTextToken,
                'user' => $user
            ]);
        }

        return $this->error('OTP not matched, Try again', 422, [
            'errors' => ['phone' => $phone, 'otp_code' => $otp_code]
        ]);
    }

    public function me()
    {
        $user =  auth()->user();
        return $this->success([
            'user' => $user
        ]);
    }


    public function logout()
    {
        Auth::user()->tokens()->delete();
        return [
            'message' => 'Tokens Revoked'
        ];
    }



    public function socialLogin()
    {
        $socialData = json_decode(request('socialData'), true);

        if (is_array($socialData)) {
            if (!array_key_exists('_profile', $socialData)) {
                $provider = 'google';
                $accessToken = '';

                $dataEmail = array_key_exists('email', $socialData) ? $socialData['email'] : '';
                $dataId = array_key_exists('sub', $socialData) ? $socialData['sub'] : '';
                $fullName = array_key_exists('name', $socialData) ? $socialData['name'] : '';
                $profilePicURL = array_key_exists('picture', $socialData) ? $socialData['picture'] : '';

                $user = User::where('email', $dataEmail)
                    ->whereNotNull('active')
                    ->select('id', 'name', 'email', 'phone', 'first_name', 'last_name', 'shipping_id', 'billing_id')
                    ->first();

                $trims = explode(" ", $fullName);
                if(count($trims) > 2) {
                    $firstName = $trims[0] . $trims[1];
                    $lastName = $trims[2];
                } else {
                    $firstName = $trims[0];
                    $lastName = $trims[1];
                }

                if (!$user) {
                    $user = User::create([
                        'name' => $fullName,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $dataEmail,
                        'active' => true,
                        'confirmed' => true,
                        'password' => null,
                        'avatar_type' => $provider,
                    ]);
                    if ($user) {
                        $user->assignRole(config('access.users.default_role'));
                    }
                }

                if (!$user->hasProvider($provider)) {
                    // Gather the provider data for saving and associate it with the user
                    $user->providers()->save(new SocialAccount([
                        'provider' => $provider,
                        'provider_id' => $dataId,
                        'token' => $accessToken,
                        'avatar' => $profilePicURL,
                    ]));
                } else {
                    // Update the users information, token and avatar can be updated.
                    $user->providers()->update([
                        'token' => $accessToken,
                        'avatar' => $profilePicURL,
                    ]);
                    $user->avatar_type = $provider;
                    $user->update();
                }

                if ($user) {
                    return $this->success([
                        'token' => $user->createToken('API Token')->plainTextToken,
                        'user' => $user
                    ]);
                }
            } else {
                $data = array_key_exists('_profile', $socialData) ? $socialData['_profile'] : [];
                $provider = array_key_exists('_provider', $socialData) ? $socialData['_provider'] : '';
                $token = array_key_exists('_token', $socialData) ? $socialData['_token'] : [];

                $accessToken = '';
                if (is_array($token)) {
                    $accessToken = array_key_exists('accessToken', $token) ? $token['accessToken'] : '';
                }

                if (is_array($data)) {
                    $dataEmail = array_key_exists('email', $data) ? $data['email'] : '';
                    $dataId = array_key_exists('id', $data) ? $data['id'] : '';
                    $fullName = array_key_exists('name', $data) ? $data['name'] : '';
                    $firstName = array_key_exists('firstName', $data) ? $data['firstName'] : '';
                    $lastName = array_key_exists('lastName', $data) ? $data['lastName'] : '';
                    $profilePicURL = array_key_exists('profilePicURL', $data) ? $data['profilePicURL'] : '';

                    $fullName = $fullName ? $fullName : ($firstName . ' ' . $lastName);
                    $user_email = $dataEmail ?: "{$dataId}@{$provider}.com";

                    $user = User::where('email', $user_email)
                        ->whereNotNull('active')
                        ->select('id', 'name', 'email', 'phone', 'first_name', 'last_name', 'shipping_id', 'billing_id')
                        ->first();

                    if (!$user) {
                        $user = User::create([
                            'name' => $fullName,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $user_email,
                            'active' => true,
                            'confirmed' => true,
                            'password' => null,
                            'avatar_type' => $provider,
                        ]);
                        if ($user) {
                            $user->assignRole(config('access.users.default_role'));
                        }
                    }


                    if (!$user->hasProvider($provider)) {
                        // Gather the provider data for saving and associate it with the user
                        $user->providers()->save(new SocialAccount([
                            'provider' => $provider,
                            'provider_id' => $dataId,
                            'token' => $accessToken,
                            'avatar' => $profilePicURL,
                        ]));
                    } else {
                        // Update the users information, token and avatar can be updated.
                        $user->providers()->update([
                            'token' => $accessToken,
                            'avatar' => $profilePicURL,
                        ]);
                        $user->avatar_type = $provider;
                        $user->update();
                    }

                    if ($user) {
                        return $this->success([
                            'token' => $user->createToken('API Token')->plainTextToken,
                            'user' => $user
                        ]);
                    }
                }
            }
        }


        return $this->error('Social login fail', 422);
    }


    public function AllAddress()
    {
        $addresses = Address::where('user_id', auth()->id())->latest()->get();

        return $this->success([
            'addresses' => $addresses,
        ]);
    }

    public function StoreNewAddress()
    {
        $id  = request('id');

        $data = [
            'name' => request('name'),
            'phone_one' => request('phone'),
            'phone_two' => '',
            'phone_three' => request('district'),
            'address' => request('address'),
            'user_id' => auth()->id(),
        ];


        if ($id) {
            $address = Address::find($id);
            if ($address) {
                $address->update($data);
            }
        } else {
            Address::create($data);
        }

        return $this->success([
            'status' => true,
            'msg' => 'Address updated successfully'
        ]);
    }

    public function deleteAddress()
    {
        $id  = request('id');

        if ($id) {
            $address = Address::find($id);
            if ($address) {
                $address->delete();
            }
        }

        return $this->success([
            'status' => true,
            'msg' => 'Address deleted successfully'
        ]);
    }
}
