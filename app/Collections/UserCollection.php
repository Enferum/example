<?php
    
    namespace App\Collections;
    
    use App\Models\User;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;
    
    class UserCollection extends Collection
    {
        /**
         * Get users except given roles
         *
         * @param array $user_role
         * @param int   $perPage
         * @return LengthAwarePaginator
         * @return
         */
        public static function getUserListExcept(array $user_role, int $perPage = 20): LengthAwarePaginator
        {
            return User::query()
                ->with('page')
                ->when(request('q') !== '', function (Builder $query) {
                    return $query
                        ->where(function (Builder $q) {
                            $q->where(DB::raw('CONCAT(name_last, " ", name_first)'), 'like', '%' . request('q') . '%')
                                ->orWhere(DB::raw('CONCAT(name_first, " ", name_last)'), 'like', '%' . request('q') . '%')
                                ->orWhere('name_first', 'like', '%' . request('q') . '%')
                                ->orWhere('name_middle', 'like', '%' . request('q') . '%')
                                ->orWhere('name_nick', 'like', '%' . request('q') . '%')
                                ->orWhere('email', 'like', '%' . request('q') . '%');
                        });
                    
                })
                ->when(request('role', '') !== '', function (Builder $query) {
                    return $query->whereRole(request('role'));
                })
                ->withTrashed()
                ->whereNotIn('role', $user_role)
                ->latest()
                ->paginate($perPage);
        }
        
        /**
         * Get list of roles except
         *
         * @param array $roles
         * @return array
         */
        public static function getRolesExcept(array $roles): array
        {
            return Arr::except(User::ROLES, $roles);
        }
        
        /**
         * Get list of Supervisors
         *
         * @return LengthAwarePaginator
         */
        public static function getSupervisorList()
        {
            $rolesList = [
                User::ROLE_CELEBRITY,
                User::ROLE_CLIENT,
            ];
            if (auth('admin')->user()->isSupervisor()) {
                $rolesList[] = User::ROLE_ADMIN;
            }
            return self::getUserListExcept($rolesList);
        }
    
        /**
         * Get count of users with role admin
         *
         * @return int
         */
        public static function adminCount()
        {
            return count(User::whereRole(User::ROLE_ADMIN)->get());
        }
    }