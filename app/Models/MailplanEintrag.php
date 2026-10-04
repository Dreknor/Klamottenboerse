<?php

namespace App\Models;

use App\Enums\Bezugsdatum;
use App\Enums\Zielgruppe;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailplanEintrag extends Model
{
    protected $table = 'mailplan_eintraege';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'zielgruppe' => Zielgruppe::class,
            'bezugsdatum' => Bezugsdatum::class,
            'aktiv' => 'boolean',
            'eingeplant_at' => 'datetime',
        ];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function vorlage(): BelongsTo
    {
        return $this->belongsTo(Mailvorlage::class, 'mailvorlage_id');
    }

    /** Zeitpunkt, ab dem die Mail eingeplant wird. */
    public function faelligAb(): ?CarbonInterface
    {
        $basis = $this->bezugsdatum === Bezugsdatum::Verkaufstag
            ? $this->boerse->verkaufstag->copy()->setTime(8, 0)
            : $this->boerse->{$this->bezugsdatum->value};

        return $basis?->copy()->addDays($this->versatz_tage);
    }
}
