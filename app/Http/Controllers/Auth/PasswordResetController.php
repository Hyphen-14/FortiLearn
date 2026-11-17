<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // Same message whether or not the email exists, so the form cannot be used to enumerate accounts.
    private const GENERIC_SENT_STATUS = 'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi sudah dikirim. Tautan berlaku 30 menit.';

    private const GENERIC_INVALID_TOKEN = 'Tautan reset tidak valid atau sudah kedaluwarsa. Minta tautan baru.';

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        $result = Password::sendResetLink(['email' => $validated['email']]);

        // The real broker result stays in the audit trail only, never in the response.
        Audit::record('auth.password_reset_requested', $request, [
            'email' => $validated['email'],
            'result' => $result,
        ]);

        return back()->with('status', self::GENERIC_SENT_STATUS);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            // max:72 because bcrypt silently ignores every byte after the 72nd
            'password' => ['required', 'string', 'max:72', 'confirmed', PasswordRule::defaults()],
        ]);

        $resetUser = null;

        $status = Password::reset($validated, function (User $user, string $password) use (&$resetUser) {
            $user->password = $password; // hashed by the model cast
            $user->setRememberToken(Str::random(60)); // kills "remember me" cookies on other devices
            $user->save();

            // A reset usually means the account may be compromised: end every existing session too.
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            event(new PasswordReset($user));
            $resetUser = $user;
        });

        if ($status !== Password::PASSWORD_RESET) {
            Audit::record('auth.password_reset_failed', $request, [
                'email' => $validated['email'],
                'result' => $status,
            ]);

            return back()->withErrors(['email' => self::GENERIC_INVALID_TOKEN])->onlyInput('email');
        }

        Audit::record('auth.password_reset_completed', $request, [], $resetUser?->id);

        return redirect()->route('login')
            ->with('status', 'Kata sandi berhasil diganti. Silakan masuk dengan kata sandi baru.');
    }
}
