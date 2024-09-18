<?php /** @noinspection ALL */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->string('content_hash', 64)->unique();
            $table->string('llm')->nullable();
            $table->vector('embedding_1536', 1536)->nullable(); // OpenAI
            $table->vector('embedding_768', 768)->nullable(); // Gemini
            $table->json('metadata');
            $table->string('filename');
            $table->timestamps();
        });

        // This is a Postgres-specific index that allows us to do fast nearest-neighbor searches
        // when there are a lot of high-dimensional embeddings in the database.
        DB::statement('CREATE INDEX ON documents USING hnsw (embedding_768 vector_l2_ops)');
        DB::statement('CREATE INDEX ON documents USING hnsw (embedding_1536 vector_l2_ops)');

        //DB::statement('CREATE INDEX embedding_1536_index ON documents USING ivfflat (embedding_1536 vector_l2_ops) WITH (lists = 100)');
        //DB::statement('CREATE INDEX embedding_768_index ON documents USING ivfflat (embedding_768 vector_l2_ops) WITH (lists = 100)');

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('name');
            $table->boolean('favorite')->default(false);
            $table->boolean('archived')->default(false);
            $table->timestamps();

            $table->index('user_id', 'idx_user_id');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->boolean('ai')->default(false);
            $table->string('llm')->nullable();
            $table->string('tokens')->nullable();
            $table->timestamps();

            $table->index('conversation_id', 'idx_conversation_id');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        DB::statement('DROP EXTENSION vector');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('messages');
    }
};
