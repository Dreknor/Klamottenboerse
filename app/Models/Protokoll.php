<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** Besprechungsprotokoll des Teams. Text in Markdown; "- [ ] …" sind offene Punkte, "Beschluss: …" Beschlüsse. */
class Protokoll extends Model
{
    use SoftDeletes;

    protected $table = 'protokolle';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['datum' => 'date'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'autor_id');
    }

    /** @return array<int, string> Zeilennummer => offener Punkt */
    public function offenePunkte(): array
    {
        $punkte = [];
        foreach (preg_split('/\R/', (string) $this->inhalt) as $nr => $zeile) {
            if (preg_match('/^\s*[-*] \[ \] (.+)$/', $zeile, $treffer)) {
                $punkte[$nr] = trim($treffer[1]);
            }
        }

        return $punkte;
    }

    /** @return list<string> */
    public function beschluesse(): array
    {
        preg_match_all('/^\s*(?:[-*] )?\**Beschluss:?\**:?\s*(.+)$/mi', (string) $this->inhalt, $treffer);

        return array_map('trim', $treffer[1]);
    }

    public function html(): string
    {
        $text = preg_replace(['/^(\s*[-*]) \[ \] /m', '/^(\s*[-*]) \[x\] /mi'], ['$1 ☐ ', '$1 ☑ '], (string) $this->inhalt);

        return Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    }
}
