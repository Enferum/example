<?php
    
    namespace App\Collections;
    
    use App\Models\Order;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Support\Facades\DB;
    
    class OrderCollection extends Collection
    {
        /**
         * Get orders list
         *
         * @param int $perPage
         * @return LengthAwarePaginator
         */
        public static function getOrdersList(int $perPage = 20): LengthAwarePaginator
        {
            $searchQuery = (ltrim((request('q', '')), 0));
            
            return Order::query()
                ->with([
                    'celebrity:id,name_nick,name_first,name_last',
                    'customer:id,name_first,name_last',
                ])
                ->when( is_numeric($searchQuery) !== '', function (Builder $query) use ($searchQuery) {
                    return $query
                        ->where('code', 'like',  '%' . $searchQuery . '%')
                        ->orWhereHas('customer', function ($query) use ($searchQuery) {
                            return $query->where(DB::raw('CONCAT(name_last, " ", name_first)'), 'like', '%' . $searchQuery . '%');
                        })
                        ->orWhereHas('celebrity', function ($query) use ($searchQuery) {
                            return $query->where('name_nick', 'like', '%' . $searchQuery . '%');
                        });
                })
                ->withCount(['customer', 'celebrity'])
                ->paginate($perPage);
        }
        
        /**
         * Get order from code
         *
         * @param int|null $code
         * @return mixed
         */
        public static function getOrder(int $code = null)
        {
            if (is_null($code)) {
                $code = request()->route('order');
            }
            return Order::query()
                ->with([
                    'service',
                    'celebrity' => function ($query) {
                        return $query->withCount('celebrity_orders');
                    },
                    'celebrity.page',
                    'celebrity.page.category:id,name',
                    'celebrity.page.genre:id,name',
                    'celebrity.page.tags:star_page_id,name',
                    'customer'  => function ($query) {
                        return $query->withCount('customer_orders');
                    }])
                ->whereCode($code)
                ->first();
        }
    }