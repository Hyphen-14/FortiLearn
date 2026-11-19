<?php

namespace App\Models;

use App\Support\Audit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'category',
        'title',
        'slug',
        'description',
        'price',
        'thumbnail',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Owner instructor or admin: always sees every lesson, reviewed or not. */
    public function isManagedBy(?User $user): bool
    {
        return $user !== null && ($user->isAdmin() || (int) $this->instructor_id === (int) $user->id);
    }

    /**
     * Who may open the course page, thumbnail and lessons. Besides the public (published only),
     * students who already paid keep access while an edit waits for re-review or was rejected,
     * because the course was approved once (approved_at set).
     */
    public function isAccessibleBy(?User $user): bool
    {
        if ($this->status === 'published' || $this->isManagedBy($user)) {
            return true;
        }

        return $user !== null
            && $this->approved_at !== null
            && in_array($this->status, ['pending_approval', 'rejected'], true)
            && $user->hasEnrolled($this->id);
    }

    /** Lessons added after the last approval stay hidden from students until an admin approves again. */
    public function lessonIsReleasedTo(Lesson $lesson, ?User $user): bool
    {
        if ($this->isManagedBy($user) || $this->status === 'published') {
            return true;
        }

        return $this->approved_at !== null && $lesson->created_at !== null && $lesson->created_at->lte($this->approved_at);
    }

    /**
     * Call BEFORE an instructor changes the content of a course. A published course goes back
     * to the moderation queue, so no edit can reach the catalog without an admin seeing it.
     */
    public function flagForReReview(Request $request, string $change): void
    {
        if ($this->status !== 'published') {
            return;
        }

        $this->approved_at ??= now()->subSecond();
        $this->status = 'pending_approval';
        $this->save();

        Audit::record('course.resubmitted_after_edit', $request, ['course_id' => $this->id, 'change' => $change]);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_order');
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('section_order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function purchases()
    {
        return $this->hasMany(WalletTransaction::class)->where('type', 'purchase')->where('status', 'completed');
    }
}
