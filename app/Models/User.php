<?php
    
    namespace App\Models;
    
    use App\Models\Support\Support;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Notifications\Notification;
    use Illuminate\Support\Facades\Storage;
    
    /**
     * @method static findOrFail(int $id)
     * @method static whereId($user)
     * @method static inRandomOrder()
     * @method static withCount(string $string)
     * @method static whereRole(int $ROLE_CLIENT)
     * @method static where(string $username, $value)
     * @property mixed email
     * @property mixed role
     * @property mixed name_last
     * @property mixed name_first
     * @property mixed name_middle
     * @property mixed sex
     * @property mixed permissions
     */
    class User extends Authenticatable
    {
        use Notifiable, SoftDeletes;
        
        const ROLE_CLIENT = 1;
        const ROLE_CELEBRITY = 5;
        const ROLE_SUPERVISOR = 10;
        const ROLE_SUPPORT = 11;
        const ROLE_ADMIN = 15;
        
        const ROLES = [
            self::ROLE_CLIENT => 'клиент',
            self::ROLE_CELEBRITY => 'селебрити',
            self::ROLE_SUPERVISOR => 'супервайзер',
            self::ROLE_SUPPORT => 'Поддержка',
            self::ROLE_ADMIN => 'администратор',
        ];
        
        const SEX_NOT_SET = 0;
        const SEX_MALE = 1;
        const SEX_FEMALE = 2;
        const SEX_ANOTHER = 3;
        
        const SEXES = [
            self::SEX_NOT_SET => 'не установлен',
            self::SEX_MALE => 'мужской',
            self::SEX_FEMALE => 'женский',
            self::SEX_ANOTHER => 'другой',
        ];
        
        const FIRST_LINE = 1;
        const SECOND_LINE = 2;
        const FINANCE_QUESTION_LINE = 3;
        const LEGAL_QUESTION_LINE = 4;
        
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name_last',
            'name_first',
            'name_middle',
            'name_nick',
            'phone',
            'photo',
            'role',
            'email',
            'sex',
            'permissions',
            'email_verified_at',
        ];
        
        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token',
        ];
        
        /**
         * The attributes that should be cast to native types.
         *
         * @var array
         */
        protected $casts = [
            'email_verified_at' => 'datetime',
            'permissions' => 'json',
        ];
        
        /**
         * Relation with star page
         * Available for user with role ROLE_STAR
         *
         * @return HasOne
         */
        public function page(): HasOne
        {
            return $this
                ->hasOne(StarPage::class);
        }
        
        /**
         * Relation with orders made by user
         *
         * @return HasMany
         */
        public function customer_orders(): HasMany
        {
            return $this
                ->hasMany(Order::class, 'customer_id');
        }
        
        /**
         * Relation with Support's made by user
         *
         * @return HasMany
         */
        public function supports(): HasMany
        {
            return $this
                ->hasMany(Support::class);
        }
        
        /**
         * Relation with orders made by user
         *
         * @return HasMany
         */
        public function celebrity_orders(): HasMany
        {
            return $this
                ->hasMany(Order::class, 'celebrity_id');
        }
        
        /**
         * Relation with 2fa
         *
         * @return HasOne
         */
        public function passwordSecurity(): HasOne
        {
            return $this
                ->hasOne(PasswordSecurity::class);
        }
        
        /**
         * Route notifications for the mail channel.
         *
         * @param Notification $notification
         * @return array|string
         */
        public function routeNotificationForMail($notification): string
        {
            return $this->email;
        }
        
        /**
         * @return string
         */
        public function getRoleName(): string
        {
            return self::ROLES[$this->role];
        }
        
        /**
         * @return bool
         */
        public function isAdmin(): bool
        {
            return $this->role === self::ROLE_ADMIN;
        }
        
        /**
         * @return bool
         */
        public function isSupervisor(): bool
        {
            return $this->role === self::ROLE_SUPERVISOR;
        }
        
        /**
         * @return bool
         */
        public function isSupport(): bool
        {
            return $this->role === self::ROLE_SUPPORT;
        }
        
        /**
         * @return bool
         */
        public function isCelebrity(): bool
        {
            return $this->role === self::ROLE_CELEBRITY;
        }
        
        /**
         * @return bool
         */
        public function isClient(): bool
        {
            return $this->role === self::ROLE_CLIENT;
        }
        
        /**
         * @param $query
         * @return mixed
         */
        public function scopeActive($query)
        {
            return $query->where('deleted_at', null);
        }
        
        /**
         * @return string
         */
        public function getFullnameAttribute(): string
        {
            return $this->name_last . ' ' . $this->name_first . ' ' . $this->name_middle;
        }
        
        /**
         * @param $value
         * @return string|string[]|null
         */
        public function getEmailAttribute($value)
        {
            if ($this->isCelebrity()) {
                
                return preg_replace('/(\D{2}).+@(\D{2}).+\.(\D+)/', '$1*******@$2****.$3', $value);
            }
            return $value;
        }
        
        /**
         * @param $value
         * @return string|string[]|null
         */
        public function getPhoneAttribute($value)
        {
            if ($this->isCelebrity()) {
                $format = preg_replace('/(?<!^)\+|[^\d+]+/', '', $value);
                return preg_replace('/\+(\d{2})\d+(\d{2})$/', '+$1*******$2', $format);
            }
            return $value;
        }
        
        /**
         * @param $value
         * @return mixed
         */
        public function getPhotoAttribute($value)
        {
            return is_null($value)
                ? $value
                : Storage::disk('avatar')->url($value);
        }
        
        /**
         * @return string
         */
        public function getSexName(): string
        {
            return self::SEXES[$this->sex];
        }
        
        /**
         * Check for existing permission
         *
         * @param $permission
         * @param string|null $prefix
         * @return bool
         */
        public function hasPermission($permission, string $prefix = null): bool
        {
            if (is_null($this->permissions)) {
                return false;
            }
            return in_array(($prefix ? $prefix . '-' : '') . $permission, $this->permissions);
        }
        
        /**
         * @return mixed
         */
        public function routeNotificationForEpochtaSMS(): string
        {
            return $this->phone;
        }
        
        /**
         * @return bool
         */
        public function check2FA(): bool
        {
            return isset($this->passwordSecurity->user_id);
        }
    }