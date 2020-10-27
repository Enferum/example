<?php
    
    namespace App\Http\Middleware;
    
    use App\Services\TwoFA;
    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use PragmaRX\Google2FALaravel\Support\Authenticator;
    
    class TwoFactorAuth
    {
        /**
         * Check auth user enable 2fa
         *
         * @param Request $request
         * @return string|null
         */
        public function handle($request, Closure $next)
        {
            $user = Auth::guard('admin')->user();
            
            // user doenst have OTP attached
            if (!$user->google2fa) {
                $google2fa_image = TwoFA::generateQrCode($user);
                return response()->view('backend.auth.google2fa.index', compact('google2fa_image', 'user'));
            }
            
            // user didnt pass OTP check
            $authenticator = new Authenticator($request);
            if (!$authenticator->isAuthenticated() || $user->passwordSecurity->google2fa_enable === 0) {
                return response()->view('backend.auth.google2fa.index', compact('user'));
            }
            
            return $next($request);
        }
    }