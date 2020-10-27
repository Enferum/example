<?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Routing\Route;
    
    /**
     * @property mixed        celebrity
     * @property mixed        customer
     * @property mixed        status
     * @property mixed        total
     * @property mixed        duration
     * @property mixed        service_id
     * @property mixed|string code
     * @property mixed        customer_id
     * @property mixed        celebrity_id
     * @method static findOrFail(int $id)
     * @method static withCount(string $string)
     * @method static whereCode(Route|object|string|null $route)
     */
    class Order extends Model
    {
        const STATUS_DELETED  = 0;
        const STATUS_DECLINED = 1;
        const STATUS_NEW      = 5;
        const STATUS_UNPAID   = 6;
        const STATUS_PAID     = 7;
        const STATUS_APPROVED = 10;
        const STATUS_FINISHED = 20;
        
        const STATUSES = [
            self::STATUS_DELETED  => 'удален',
            self::STATUS_DECLINED => 'отклонен',
            self::STATUS_NEW      => 'новый',
            self::STATUS_UNPAID   => 'не оплачен',
            self::STATUS_PAID     => 'оплачен',
            self::STATUS_APPROVED => 'подтвержден',
            self::STATUS_FINISHED => 'закрыт',
        ];
        
        /**
         * Relation with customer who create order
         *
         * @return BelongsTo
         */
        public function customer()
        {
            return $this->belongsTo(User::class, 'customer_id')->withTrashed();
        }
        
        /**
         * Relation with celebrity person
         *
         * @return BelongsTo
         */
        public function celebrity()
        {
            return $this
                ->belongsTo(User::class, 'celebrity_id')
                ->with('page')
                ->withTrashed();
        }
        
        /**
         * Relation with ordered star service
         *
         * @return BelongsTo
         */
        public function service()
        {
            return $this
                ->belongsTo(Service::class);
        }
        
        /**
         * Relation with order payments
         *
         * @return HasMany
         */
        public function payments()
        {
            return $this
                ->hasMany(Payment::class);
        }
        
        /**
         * @return string
         */
        public function getStatusNameAttribute()
        {
            return self::STATUSES[$this->status];
        }
        
        /**
         * @param $value
         * @return string
         */
        public function getCodeAttribute($value)
        {
            return str_pad($value, 7, '0', STR_PAD_LEFT);
        }
        
        /**
         * Get the route key for the model.
         *
         * @return string
         */
        public function getRouteKeyName()
        {
            return 'code';
        }
        
        /**
         * @param $value
         * @return string
         */
        public function getTotalAttribute($value)
        {
            return number_format($value, 2);
        }
    }