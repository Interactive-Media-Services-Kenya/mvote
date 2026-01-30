<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Otp;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'nick_name' => ['nullable', 'string', 'max:20'],
        ]);

        $phone = $request->phone;
        // Normalize phone to international format without +
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        $fanRole = Role::where('name', 'fan')->first();

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'nick_name' => $request->nick_name,
                'role_id' => $fanRole->id,
            ]
        );

        if ($request->filled('nick_name')) {
            $user->update(['nick_name' => $request->nick_name]);
        }

        // Generate 5-digit OTP
        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Send SMS via ExpressSMS API
        $this->sendSms($phone, $code);

        return back();
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
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'array', 'size:5'],
        ]);

        $phone = $request->phone;
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        $otpCode = implode('', $request->otp);
        $user = User::where('phone', $phone)->firstOrFail();

        $validOtp = Otp::where('user_id', $user->id)
            ->where('code', $otpCode)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$validOtp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Delete the used OTP
        $validOtp->delete();

        Auth::login($user, true);

        return redirect('/lineup');
    }
}
