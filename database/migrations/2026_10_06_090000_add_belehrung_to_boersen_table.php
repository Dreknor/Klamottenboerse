<?php

use App\Support\Belehrung;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Text zum Unterschreiben bei der Kistenabgabe
        Schema::table('boersen', function (Blueprint $table) {
            $table->text('belehrung')->nullable()->after('hinweise');
        });

        // Der V1-Import hatte die Belehrung (HTML) in die internen Hinweise geschrieben
        DB::table('boersen')->where('hinweise', 'like', '%<p>%')->get(['id', 'hinweise'])->each(fn ($b) => DB::table('boersen')
            ->where('id', $b->id)->update(['belehrung' => Belehrung::ausV1Html($b->hinweise), 'hinweise' => null]));
    }

    public function down(): void
    {
        Schema::table('boersen', fn (Blueprint $table) => $table->dropColumn('belehrung'));
    }
};
