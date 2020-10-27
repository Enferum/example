<?php
    
    namespace App\Services;
    
    use App\Models\PasswordSecurity;
    use BaconQrCode\Renderer\Image\Png;
    use BaconQrCode\Writer;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Redirector;
    use Illuminate\Support\Facades\Auth;
    use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
    use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
    use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
    use PragmaRX\Google2FA\Google2FA;
    use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;
    
    class TwoFA
    {
        public $user;
        
        /**
         * Verify input code with Google Authenticator
         *
         * @param Request $request
         * @return RedirectResponse|Response
         * @throws IncompatibleWithGoogleAuthenticatorException
         * @throws InvalidCharactersException
         * @throws SecretKeyTooShortException
         */
        public static function verify(Request $request)
        {
            $user      = Auth::guard('admin')->user();
            $google2fa = new Google2FA();
            $secret    = $request->input('otp');
            $valid     = $google2fa->verifyKey($user->passwordSecurity->latest()->first()->google2fa_secret, $secret);
            
            if ($valid) {
                $user->google2fa = 1;
                $user->save();
                $user->passwordSecurity->google2fa_enable = 1;
                $user->passwordSecurity->save();
                return redirect()->route('backend.dashboard');
            } else {
                return redirect()->back()->withErrors(['Код введен неверно']);
            }
        }
        
        /**
         * Generate new Secret
         *
         * @return mixed
         */
        public static function generateKey()
        {
            $user = Auth::guard('admin')->user();
            $google2fa = new Google2FA();
            $key = PasswordSecurity::create([
                'user_id'          => $user->id,
                'google2fa_enable' => 1,
                'google2fa_secret' => $google2fa->generateSecretKey(),
            ]);
            
            return $key;
        }
        
        /**
         * Generate QR code from Secret
         *
         * @param $user
         * @return string
         */
        public static function generateQrCode($user)
        {
            $google2faqr = new Google2FAQRCode();
            $key         = self::generateKey();
            $qrCode      = $google2faqr->getQRCodeUrl('Starcall', $user->email, $key->google2fa_secret);
            $renderer    = new Png();
            $renderer->setHeight(256);
            $renderer->setWidth(256);
            $writer          = new Writer($renderer);
            $google2fa_image = base64_encode($writer->writeString($qrCode));
            return $google2fa_image;
        }
    
        /**
         * Disable 2FA for user
         *
         * @return mixed
         */
        public static function disable2FA()
        {
            $user            = Auth::guard('admin')->user();
            $user->google2fa = 0;
            $user->passwordSecurity->delete();
            $user->save();
            return redirect()
                ->route('backend.profile')
                ->with('success', 'Двухфакторная аутентификация выключена');
        }
    }