<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackAntwort extends Model
{
    protected $table = 'feedback_antworten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['zahl' => 'integer'];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function frage(): BelongsTo
    {
        return $this->belongsTo(FeedbackFrage::class, 'feedback_frage_id');
    }
}
