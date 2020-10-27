<?php
    
    use App\Models\Order;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    class CreateOrdersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->integer('code')->unique();
                $table->foreignId('customer_id')->nullable()->references('id')->on('users');
                $table->foreignId('celebrity_id')->nullable()->references('id')->on('users');
                $table->foreignId('service_id')->nullable()->references('id')->on('services');
                $table->foreignId('slot_id')->nullable()->references('id')->on('slots');
                $table->tinyInteger('status')->default(Order::STATUS_NEW);
                $table->integer('total')->default(0)->comment('Service cost with cents');
                $table->integer('duration')->default(0)->comment('Duration in minutes');
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }
        
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('orders');
        }
    }