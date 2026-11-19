<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Review;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with multi-category commercial courses.
     */
    public function run(): void
    {
        User::where('email', 'like', '%@secureacademy.test')->update(['is_active' => false]);

        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_SEED_EMAIL', 'admin@fortilearn.local')],
            ['name' => 'Administrator Utama', 'password' => env('ADMIN_SEED_PASSWORD', \Illuminate\Support\Str::random(32))]
        );
        $admin->role = 'admin';
        $admin->balance = 0.00;
        $admin->free_course_credits = 0;
        $admin->is_active = true;
        $admin->save();

        $instructorTech = User::updateOrCreate(
            ['email' => 'instructor@fortilearn.local'],
            ['name' => 'Rizky Pratama, M.Kom.', 'password' => \Illuminate\Support\Str::random(48)]
        );
        $instructorTech->role = 'instructor';
        $instructorTech->balance = 0.00;
        $instructorTech->free_course_credits = 0;
        $instructorTech->is_active = false;
        $instructorTech->save();

        $instructorDesign = User::updateOrCreate(
            ['email' => 'design.mentor@fortilearn.local'],
            ['name' => 'Nadia Kusuma, S.Ds.', 'password' => \Illuminate\Support\Str::random(48)]
        );
        $instructorDesign->role = 'instructor';
        $instructorDesign->balance = 0.00;
        $instructorDesign->free_course_credits = 0;
        $instructorDesign->is_active = false;
        $instructorDesign->save();

        $student1 = User::updateOrCreate(
            ['email' => 'student1@fortilearn.local'],
            ['name' => 'Alice Maharani', 'password' => \Illuminate\Support\Str::random(48)]
        );
        $student1->role = 'student';
        $student1->balance = 0.00;
        $student1->free_course_credits = 0;
        $student1->is_active = false;
        $student1->save();

        $student2 = User::updateOrCreate(
            ['email' => 'student2@fortilearn.local'],
            ['name' => 'Budi Hartono', 'password' => \Illuminate\Support\Str::random(48)]
        );
        $student2->role = 'student';
        $student2->balance = 0.00;
        $student2->free_course_credits = 0;
        $student2->is_active = false;
        $student2->save();

        // 4. Multi-Category Commercial Courses (Udemy-style Marketplace)
        $coursesData = [
            [
                'instructor_id' => $instructorTech->id,
                'category' => 'Frontend',
                'title' => 'Front-End Web Development Modern dengan React & TypeScript',
                'slug' => 'frontend-web-development-react-typescript',
                'description' => 'Pelajari pembuatan antarmuka web modern dari dasar hingga mahir. Bangun komponen interaktif, manajemen state, dan proyek portofolio siap kerja.',
                'price' => 185000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Arsitektur Komponen Modern & JSX Dasar', 'content_type' => 'video', 'duration_seconds' => 960],
                    ['title' => 'State Management, Hooks & Integrasi REST API', 'content_type' => 'video', 'duration_seconds' => 1420],
                    ['title' => 'Panduan Desain Sistem & Optimasi Performa Web', 'content_type' => 'pdf', 'duration_seconds' => 0],
                ],
            ],
            [
                'instructor_id' => $instructorTech->id,
                'category' => 'Backend',
                'title' => 'Backend Engineering: Arsitektur RESTful API & Microservices',
                'slug' => 'backend-engineering-restful-api-microservices',
                'description' => 'Kuasai perancangan server berkinerja tinggi, pemodelan database relasional, autentikasi pengguna, serta deployment aplikasi skala industri.',
                'price' => 240000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Perancangan Skema Database & Optimasi Indeks', 'content_type' => 'video', 'duration_seconds' => 1150],
                    ['title' => 'Membangun Layanan Transaksi Pembayaran yang Andal', 'content_type' => 'video', 'duration_seconds' => 1380],
                    ['title' => 'Dokumentasi Standar OpenAPI & Postman Collection', 'content_type' => 'pdf', 'duration_seconds' => 0],
                ],
            ],
            [
                'instructor_id' => $instructorDesign->id,
                'category' => 'UI/UX Design',
                'title' => 'Masterclass UI/UX Design: Riset Pengguna hingga Prototyping Figma',
                'slug' => 'masterclass-ui-ux-design-figma-prototyping',
                'description' => 'Rancang produk digital yang disukai pengguna. Pelajari alur kerja desain produk mulai dari wireframing, design tokens, hingga usability testing.',
                'price' => 165000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Prinsip Hierarki Visual, Tipografi & Sistem Grid', 'content_type' => 'video', 'duration_seconds' => 890],
                    ['title' => 'Auto-Layout Lanjutan & Interactive Components di Figma', 'content_type' => 'video', 'duration_seconds' => 1260],
                    ['title' => 'Template Design System & UI Kit Komersial', 'content_type' => 'pdf', 'duration_seconds' => 0],
                ],
            ],
            [
                'instructor_id' => $instructorTech->id,
                'category' => 'Data & AI',
                'title' => 'Data Science & Machine Learning Terapan dengan Python',
                'slug' => 'data-science-machine-learning-python',
                'description' => 'Olahan data bisnis menjadi keputusan strategis. Pelajari analisis eksploratif, visualisasi data, dan pemodelan prediktif menggunakan Python.',
                'price' => 275000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Eksplorasi Data & Pembersihan Dataset menggunakan Pandas', 'content_type' => 'video', 'duration_seconds' => 1080],
                    ['title' => 'Supervised Learning: Regresi & Klasifikasi Praktis', 'content_type' => 'video', 'duration_seconds' => 1500],
                ],
            ],
            [
                'instructor_id' => $instructorDesign->id,
                'category' => 'Bisnis',
                'title' => 'Manajemen Produk Digital & Strategi Produktivitas Tim',
                'slug' => 'manajemen-produk-digital-produktivitas-tim',
                'description' => 'Gandakan efektivitas kerja tim dan peluncuran produk. Pelajari penyusunan roadmap, prioritas fitur, dan metrik pertumbuhan bisnis.',
                'price' => 145000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Kerangka Kerja Prioritas Produk & Perencanaan Sprint', 'content_type' => 'video', 'duration_seconds' => 840],
                    ['title' => 'Membangun Kebiasaan Kerja Fokus & Manajemen Waktu', 'content_type' => 'pdf', 'duration_seconds' => 0],
                ],
            ],
            [
                'instructor_id' => $instructorTech->id,
                'category' => 'Cyber Security',
                'title' => 'Keamanan Aplikasi Web & Praktik Pemrograman Aman',
                'slug' => 'keamanan-aplikasi-web-pemrograman-aman',
                'description' => 'Lindungi aplikasi modern dari ancaman siber. Pelajari teknik pengujian keamanan aplikasi, perlindungan data pengguna, dan standar industri.',
                'price' => 220000.00,
                'status' => 'published',
                'lessons' => [
                    ['title' => 'Pengenalan Keamanan Web & Perlindungan Autentikasi', 'content_type' => 'video', 'duration_seconds' => 950],
                    ['title' => 'Praktik Terbaik Pengamanan Transaksi & Data Finansial', 'content_type' => 'video', 'duration_seconds' => 1250],
                ],
            ],
        ];

        foreach ($coursesData as $courseItem) {
            $course = Course::updateOrCreate(
                ['slug' => $courseItem['slug']],
                [
                    'instructor_id' => $courseItem['instructor_id'],
                    'category' => $courseItem['category'],
                    'title' => $courseItem['title'],
                    'description' => $courseItem['description'],
                    'price' => $courseItem['price'],
                    'status' => $courseItem['status'],
                ]
            );

            foreach ($courseItem['lessons'] as $index => $lessonItem) {
                $lesson = Lesson::updateOrCreate(
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

                \App\Services\DummyMediaGenerator::ensureLessonMediaExists($course, $lesson);
            }
        }

        // 5. Seed initial enrollment for Student 1 so "Kursus Saya" & progress rings are populated immediately
        $enrolledCourse = Course::where('slug', 'frontend-web-development-react-typescript')->first();
        if ($enrolledCourse) {
            Enrollment::firstOrCreate(
                ['user_id' => $student1->id, 'course_id' => $enrolledCourse->id],
                ['enrolled_at' => now()]
            );

            // 6. Demo sales + reviews so the instructor dashboard is populated out of the box.
            $demo = [
                [$student1, 5, 'Materinya runtut dan mudah diikuti. Sangat direkomendasikan!', 3],
                [$student2, 4, 'Penjelasan bagus, semoga ditambah studi kasus lagi.', 1],
            ];
            foreach ($demo as [$buyer, $rating, $comment, $daysAgo]) {
                Enrollment::firstOrCreate(
                    ['user_id' => $buyer->id, 'course_id' => $enrolledCourse->id],
                    ['enrolled_at' => now()->subDays($daysAgo)]
                );

                $ref = 'TRX-DEMO-' . $buyer->id . '-' . $enrolledCourse->id;
                if (! WalletTransaction::where('reference_number', $ref)->exists()) {
                    $tx = WalletTransaction::create([
                        'reference_number' => $ref,
                        'user_id' => $buyer->id,
                        'course_id' => $enrolledCourse->id,
                        'type' => 'purchase',
                        'amount' => $enrolledCourse->price,
                        'balance_before' => (float) $buyer->balance + (float) $enrolledCourse->price,
                        'balance_after' => (float) $buyer->balance,
                        'status' => 'completed',
                        'description' => 'Pembelian course: ' . $enrolledCourse->title,
                    ]);
                    $tx->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->save();
                }

                $review = Review::where('course_id', $enrolledCourse->id)->where('user_id', $buyer->id)->first() ?? new Review();
                $review->course_id = $enrolledCourse->id;
                $review->user_id = $buyer->id;
                $review->fill(['rating' => $rating, 'comment' => $comment])->save();
            }
        }
    }
}
