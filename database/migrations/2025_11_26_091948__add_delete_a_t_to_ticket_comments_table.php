<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {

            /* 

             $table->timestamp('deleted_at')->nullable();
             $table->unsignedBigInteger('deleted_by')->nullable();
             
             */

            //standard laravel

            $table->softDeletes(); // crea deleted_at nullable timestamp
            $table->foreignId('deleted_by') //crea un unsingedbigint del mimso tipo que el id relacionado con un usuario, de tal manera que tenta que existr siemrpe este usuarios
                ->nullable()
                ->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {


            $table->dropColumn('deleted_at');
            $table->dropColumn('deleted_by');
        });
    }
};
