<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Otp;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function index()
    {
        return Inertia::render('Login');
    }

    public function identify(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(?:254|\+254|0)?(7|1)(?:(?:[0-9][0-9])|(?:0[0-8]))[0-9]{6}$/'],
        ]);

        $phone = $request->phone;
        // Normalize phone to international format without +
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        // $fanRole = Role::where('name', 'fan')->first();
        $indusLeaderRole = Role::where('name', 'judge')->first();

        // Find or create the user only with phone
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['role_id' => $indusLeaderRole->id]
        );

        $isNewUser = $user->wasRecentlyCreated || empty($user->nick_name);

        // Generate 5-digit OTP
        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Send SMS via ExpressSMS API
        $this->sendSms($phone, $code);

        return back()->with('is_new_user', $isNewUser);
    }

    private function sendSms($phone, $otp)
    {
        $message = "Your MVote OTP is: " . $otp . ". Valid for 5 minutes.";
        $apiKey = env('SMS_API_KEY');

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->withOptions([
                    'verify' => !app()->environment('local'),
                ])
                ->post(env('SMS_BASE_URL'), [
                    'api_key' => $apiKey,
                    'message' => $message,
                    'phone' => $phone,
                ]);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            Log::info('ExpressSMS API Response', [
                'phone' => $phone,
                'response' => $response->json() ?? $response->body(),
            ]);

        } catch (\Throwable $e) {
            Log::error('ExpressSMS Connection Failure', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function verify(Request $request)
    {
        // 1. Basic validation
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'array', 'size:5'],
        ]);

        // 2. Normalize phone to match DB format
        $phone = $request->phone;
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $normalizedPhone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $normalizedPhone = substr($phone, 1);
        } else {
            $normalizedPhone = $phone;
        }

        $user = User::where('phone', $normalizedPhone)->firstOrFail();

        // 3. Conditional Nickname Validation (Required only if they don't have one)
        if (empty($user->nick_name)) {
            $request->validate([
                'nick_name' => ['required', 'string', 'min:2', 'max:20'],
            ]);
        }

        // 4. OTP Verification
        $otpCode = implode('', $request->otp);
        $validOtp = Otp::where('user_id', $user->id)
            ->where('code', $otpCode)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$validOtp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // 5. Cleanup and Update
        $validOtp->delete();

        if ($request->filled('nick_name')) {
            $user->update(['nick_name' => $request->nick_name]);
        }

        Auth::login($user, true);

        if ($user->role && $user->role->name === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect()->route('lineup');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
