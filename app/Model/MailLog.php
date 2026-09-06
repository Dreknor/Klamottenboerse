<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Protokolliert den Versand von Massen-Mails (z. B. "Anmeldung möglich"),
 * damit im Frontend nachvollzogen werden kann, wer die Mail bereits
 * erhalten hat und wer nicht (z. B. wegen des stündlichen Mail-Limits).
 */
class MailLog extends Model
{
    public $table = 'mail_logs';

    protected $fillable = [
        'interessent_id',
        'helfer_id',
        'klamottenboerse_id',
        'typ',
        'email',
        'betreff',
        'status',
        'fehler',
        'versendet_at',
    ];

    protected $casts = [
        'versendet_at' => 'datetime',
    ];

    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function interessent()
    {
        return $this->belongsTo(Interessenten::class, 'interessent_id');
    }

    public function helfer()
    {
        return $this->belongsTo(Helfer::class, 'helfer_id');
    }

    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboerse_id');
    }

    public function scopeTyp($query, string $typ)
    {
        return $query->where('typ', $typ);
    }

    public function scopeOffen($query)
    {
        return $query->whereIn('status', [self::STATUS_QUEUED, self::STATUS_FAILED]);
    }
}
