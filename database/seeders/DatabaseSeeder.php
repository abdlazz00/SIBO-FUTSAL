<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Court;
use App\Models\Testimonial;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Owner
        User::create([
            'name' => 'Owner Vitka Futsal',
            'email' => 'owner@vitkafutsal.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // 2. Seed Admin
        User::create([
            'name' => 'Admin Vitka Futsal',
            'email' => 'admin@vitkafutsal.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Seed Customers
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567892',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'phone' => '081234567893',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // 4. Seed Courts
        Court::create([
            'name' => 'Lapangan A (Vinil Premium)',
            'type' => 'indoor',
            'price' => 150000.00,
            'slot_duration' => 60,
            'open_time' => '08:00:00',
            'close_time' => '23:00:00',
            'status' => 'active',
        ]);

        Court::create([
            'name' => 'Lapangan B (Rumput Sintetis)',
            'type' => 'indoor',
            'price' => 120000.00,
            'slot_duration' => 60,
            'open_time' => '08:00:00',
            'close_time' => '23:00:00',
            'status' => 'active',
        ]);

        Court::create([
            'name' => 'Lapangan C (Semen Outdoor)',
            'type' => 'outdoor',
            'price' => 90000.00,
            'slot_duration' => 60,
            'open_time' => '08:00:00',
            'close_time' => '22:00:00',
            'status' => 'active',
        ]);

        // 5. Seed Testimonials
        Testimonial::create([
            'customer_name' => 'Agus Pratama',
            'rating' => 5,
            'content' => 'Fasilitas lapangan sangat bagus! Lapangan semen rata dan lapangannya bersih sekali. Proses booking juga cepat.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'customer_name' => 'Rian Hidayat',
            'rating' => 4,
            'content' => 'Rumput sintetisnya empuk dan terawat. Nyaman buat main malam-malam bareng teman kantor.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Testimonial::create([
            'customer_name' => 'Siti Rahma',
            'rating' => 5,
            'content' => 'Lokasinya strategis dan parkir luas. Pelayanan adminnya ramah banget. Sukses terus Vitka Futsal!',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // 6. Seed historical Bookings, Payments, Expenses for reports
        $admin = User::where('role', 'admin')->first();
        $customerBudi = User::where('email', 'budi@example.com')->first();
        $courts = Court::all();

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            
            // Seed 8-12 bookings per month
            $numBookings = rand(8, 12);
            for ($j = 0; $j < $numBookings; $j++) {
                $court = $courts->random();
                $bookingDate = $monthDate->copy()->startOfMonth()->addDays(rand(0, 27));
                
                if ($bookingDate->isFuture()) {
                    continue;
                }

                $startHour = rand(8, 21);
                $startTime = sprintf('%02d:00:00', $startHour);
                $endTime = sprintf('%02d:00:00', $startHour + 1);
                $totalPrice = $court->price;

                $status = 'completed';
                if ($i === 0 && $bookingDate->isAfter(Carbon::today())) {
                    $status = 'confirmed';
                } elseif (rand(1, 10) === 10) {
                    $status = 'cancelled';
                }

                $booking = Booking::create([
                    'booking_number' => 'VF-' . strtoupper(Str::random(6)),
                    'court_id' => $court->id,
                    'user_id' => $customerBudi->id,
                    'customer_name' => $customerBudi->name,
                    'customer_phone' => $customerBudi->phone,
                    'customer_email' => $customerBudi->email,
                    'date' => $bookingDate->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_price' => $totalPrice,
                    'status' => $status,
                    'is_manual' => rand(0, 1) === 1,
                    'created_by' => $admin->id,
                ]);

                if ($status === 'completed') {
                    $method = ['cash', 'transfer', 'qris'][rand(0, 2)];
                    Payment::create([
                        'booking_id' => $booking->id,
                        'payment_method' => $method,
                        'amount' => $totalPrice,
                        'refund_amount' => 0.00,
                        'confirmed_by' => $admin->id,
                        'confirmed_at' => Carbon::parse($bookingDate->toDateString() . ' ' . $startTime)->addMinutes(rand(10, 50)),
                    ]);
                } elseif ($status === 'cancelled') {
                    if (rand(0, 1) === 1) {
                        $method = ['cash', 'transfer', 'qris'][rand(0, 2)];
                        Payment::create([
                            'booking_id' => $booking->id,
                            'payment_method' => $method,
                            'amount' => $totalPrice,
                            'refund_amount' => $totalPrice,
                            'refund_reason' => 'Permintaan customer batal main',
                            'confirmed_by' => $admin->id,
                            'confirmed_at' => Carbon::parse($bookingDate->toDateString() . ' ' . $startTime)->subHours(rand(1, 5)),
                        ]);
                    }
                }
            }

            // Seed 3-4 expenses per month
            $categories = ['utilities', 'maintenance', 'salaries', 'other'];
            $expDescriptions = [
                'utilities' => 'Pembayaran Listrik Bulanan & Tagihan Air',
                'maintenance' => 'Servis AC & Perbaikan Jaring Lapangan',
                'salaries' => 'Gaji Bulanan Staf Kebersihan & Admin',
                'other' => 'Pembelian Sabun Cuci & Pembersih Lantai'
            ];

            foreach ($categories as $cat) {
                $expenseDate = $monthDate->copy()->startOfMonth()->addDays(rand(1, 28));
                if ($expenseDate->isFuture()) {
                    continue;
                }

                $amount = 0;
                switch ($cat) {
                    case 'utilities':
                        $amount = rand(300000, 600000);
                        break;
                    case 'maintenance':
                        $amount = rand(150000, 400000);
                        break;
                    case 'salaries':
                        $amount = rand(1000000, 1500000);
                        break;
                    case 'other':
                        $amount = rand(50000, 150000);
                        break;
                }

                Expense::create([
                    'category' => $cat,
                    'description' => $expDescriptions[$cat],
                    'amount' => $amount,
                    'expense_date' => $expenseDate->toDateString(),
                    'recorded_by' => $admin->id,
                ]);
            }
        }
    }
}
