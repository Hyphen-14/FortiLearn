<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@secureacademy.test'],
            [
                'name' => 'Security Administrator',
                'password' => 'PasswordAdmin123!',
                'role' => 'admin',
                'balance' => 0.00,
                'is_active' => true,
            ]
        );

        // 2. Instructor Account
        $instructor = User::firstOrCreate(
            ['email' => 'instructor@secureacademy.test'],
            [
                'name' => 'Prof. Cyber Instructor',
                'password' => 'PasswordInstruktur123!',
                'role' => 'instructor',
                'balance' => 0.00,
                'is_active' => true,
            ]
        );

        // 3. Student Account with Wallet Balance
        $student1 = User::firstOrCreate(
            ['email' => 'student1@secureacademy.test'],
            [
                'name' => 'Alice Student',
                'password' => 'PasswordStudent123!',
                'role' => 'student',
                'balance' => 500000.00, // Rp 500.000 saldo awal
                'is_active' => true,
            ]
        );

        $student2 = User::firstOrCreate(
            ['email' => 'student2@secureacademy.test'],
            [
                'name' => 'Bob Student',
                'password' => 'PasswordStudent123!',
                'role' => 'student',
                'balance' => 100000.00, // Rp 100.000 saldo awal
                'is_active' => true,
            ]
        );

        // 4. Sample Published Courses for Catalog & Checkout Testing
        $coursesData = [
            [
                'title' => 'Web Application Security & White-Box Pentesting',
                'slug' => 'web-application-security-white-box-pentesting',
                'description' => 'Pelajari teknik audit kode sumber mendalam, identifikasi kerentanan OWASP Top 10, dan mitigasi eksploitasi berbahaya pada aplikasi web modern.',
                'price' => 250000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Pengenalan Arsitektur Web & Analisis Vektor Serangan', 'content_type' => 'video', 'duration_seconds' => 900],
                    ['title' => 'Eksploitasi & Mitigasi SQL Injection pada Prepared Statements', 'content_type' => 'video', 'duration_seconds' => 1200],
                    ['title' => 'Cheat Sheet Mitigasi Kerentanan Web (Panduan PDF)', 'content_type' => 'pdf', 'duration_seconds' => 0],
                ]
            ],
            [
                'title' => 'OWASP Top 10: Defensive Secure Programming',
                'slug' => 'owasp-top-10-defensive-secure-programming',
                'description' => 'Kuasai standar rekayasa perangkat lunak aman: sanitasi XSS, pencegahan CSRF, session fixation defense, dan validasi file upload anti-RCE.',
                'price' => 175000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Anatomi Broken Access Control & IDOR', 'content_type' => 'video', 'duration_seconds' => 850],
                    ['title' => 'Mengamankan Upload File dengan Magic Bytes & Private Storage', 'content_type' => 'video', 'duration_seconds' => 1100],
                ]
            ],
            [
                'title' => 'Secure Transaction Engineering & Concurrency Defense',
                'slug' => 'secure-transaction-engineering-concurrency-defense',
                'description' => 'Membangun sistem pembayaran e-commerce anti-kebocoran dana: mitigasi Race Condition, Pessimistic Locking, dan integritas ACID.',
                'price' => 350000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Mekanisme Time-Of-Check To Time-Of-Use (TOCTOU) Exploitation', 'content_type' => 'video', 'duration_seconds' => 1050],
                    ['title' => 'Implementasi Database Row-Level Locking (SELECT FOR UPDATE)', 'content_type' => 'video', 'duration_seconds' => 1350],
                ]
            ],
            [
                'title' => 'Ethical Hacking & Network Defense Fundamentals',
                'slug' => 'ethical-hacking-network-defense-fundamentals',
                'description' => 'Panduan fundamental cybersecurity untuk pemula: pemindaian jaringan, hardening server, dan enkripsi data end-to-end.',
                'price' => 95000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Dasar Jaringan & Model Pertahanan Perimeter', 'content_type' => 'video', 'duration_seconds' => 700],
                ]
            ],
        ];

        foreach ($coursesData as $courseItem) {
            $course = \App\Models\Course::firstOrCreate(
                ['slug' => $courseItem['slug']],
                [
                    'instructor_id' => $instructor->id,
                    'title' => $courseItem['title'],
                    'description' => $courseItem['description'],
                    'price' => $courseItem['price'],
                    'status' => $courseItem['status'],
                ]
            );

            foreach ($courseItem['lessons'] as $index => $lessonItem) {
                \App\Models\Lesson::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $lessonItem['title'],
                    ],
                    [
                        'lesson_order' => $index + 1,
                        'content_type' => $lessonItem['content_type'],
                        'file_path' => 'private/courses/sample-' . $course->id . '-' . ($index + 1) . '.' . ($lessonItem['content_type'] === 'video' ? 'mp4' : 'pdf'),
                        'original_filename' => $lessonItem['title'] . '.' . ($lessonItem['content_type'] === 'video' ? 'mp4' : 'pdf'),
                        'duration_seconds' => $lessonItem['duration_seconds'],
                    ]
                );
            }
        }

        // 5. Seed initial enrollment for Student 1 in Course 4
        $course4 = \App\Models\Course::where('slug', 'ethical-hacking-network-defense-fundamentals')->first();
        if ($course4) {
            \App\Models\Enrollment::firstOrCreate(
                ['user_id' => $student1->id, 'course_id' => $course4->id],
                ['enrolled_at' => now()]
            );
        }
    }
}
