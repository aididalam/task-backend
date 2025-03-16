<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskQueryParamsTable extends Migration {
    public function up() {
        Schema::create('task_query_params', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('query_params')->nullable(); // Store parameters as JSON
        });
    }

    public function down() {
        Schema::dropIfExists('task_query_params');
    }
}
