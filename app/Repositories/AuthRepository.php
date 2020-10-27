<?php
    
    namespace App\Repositories;
    
    use App\Models\User;
    use App\Notifications\PasswordResetNotification;
    use Illuminate\Support\Facades\Notification;
    use Illuminate\Support\Str;
    
    class AuthRepository
    {
        public static function update($user)
        {
            $getUser = User::whereId($user)->first();
            
            $getUser->password = Str::random(16);
            
            $getUser->setRememberToken(Str::random(60));
            
            $getUser->save();
            
            Notification::send($getUser, new PasswordResetNotification($getUser));
        }
    }