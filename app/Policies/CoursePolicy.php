<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can stream / access the course lessons.
     * Prevents IDOR and unauthorized course viewing.
     */
    public function viewContent(User $user, Course $course): bool
    {
        // Admin has full oversight
        if ($user->isAdmin()) {
            return true;
        }

        // The course instructor can view their own content
        if ($course->instructor_id === $user->id) {
            return true;
        }

        // Student must have a valid enrollment record
        return $user->hasEnrolled($course->id);
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->instructor_id === $user->id;
    }
}
