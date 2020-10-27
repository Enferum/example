<?php
    
    use App\Models\User;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    class CreateUsersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name_last')->nullable();
                $table->string('name_first')->nullable();
                $table->string('name_middle')->nullable();
                $table->string('name_nick')->nullable();
                $table->string('phone')->nullable();
                $table->string('photo')->nullable();
                $table->tinyInteger('sex')->default(User::SEX_NOT_SET);
                $table->tinyInteger('role')->default(User::ROLE_CLIENT);
                $table->tinyInteger('vip')->default(0);
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->boolean('google2fa')->default(0);
                $table->string('password');
                $table->json('permissions')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('users');
        }
    }