<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question',
        'marks',
        'status',
    ];

    protected $casts = [
        'marks' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the exam that owns the question.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class, 'question_id');
    }
}
