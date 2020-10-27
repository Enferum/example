<?php
    
    use App\Models\StarPage;
    use App\Models\Tag;
    use App\Models\User;
    use Illuminate\Database\Seeder;
    
    class UserSeeder extends Seeder
    {
        
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            $permissions = collect(config('permissions'))->pluck('name');
            
            $password = bcrypt('password');
            
            $admin              = new User();
            $admin->name_first  = 'Admin';
            $admin->role        = User::ROLE_ADMIN;
            $admin->email       = 'admin@example.com';
            $admin->password    = $password;
            $admin->permissions = $permissions;
            $admin->save();
            
            
            factory(User::class, 20)
                ->create(['role' => User::ROLE_CELEBRITY])
                ->each(function ($user) {
                    $page = $user->page()->save(factory(StarPage::class)->make());
                    $page->tags()->saveMany(factory(Tag::class, 10)->make());
                });
            factory(User::class, 30)->create(['role' => User::ROLE_CLIENT]);
        }
    }