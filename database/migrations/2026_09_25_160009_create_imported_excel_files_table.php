<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportedExcelFilesTable extends Migration
{
    public function up()
    {
        Schema::create('imported_excel_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');           // original name
            $table->string('file_path');           // storage path
            $table->string('title')->nullable();
            $table->text('note')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('duplicate_rows')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imported_excel_files');
    }
}