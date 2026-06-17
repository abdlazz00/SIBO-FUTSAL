<?php

namespace App\Services;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Events\RealTimeNotification;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getUnread(int $userId)
    {
        return $this->notificationRepository->getUnreadByUserId($userId);
    }

    public function getAll(int $userId, int $limit = 20)
    {
        return $this->notificationRepository->getAllByUserId($userId, $limit);
    }

    public function markRead(int $id): bool
    {
        return $this->notificationRepository->markAsRead($id);
    }

    public function markAllRead(int $userId): bool
    {
        return $this->notificationRepository->markAllAsRead($userId);
    }

    /**
     * Log notification to DB and broadcast via Reverb
     */
    public function sendNotification(
        int $userId,
        string $title,
        string $message,
        string $type = 'booking',
        ?int $referenceId = null,
        ?string $referenceType = null
    ): Notification {
        $notification = $this->notificationRepository->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'is_read' => false,
            'created_at' => now()
        ]);

        // Dispatch Reverb event for real-time notification
        broadcast(new RealTimeNotification($notification))->toOthers();

        return $notification;
    }

    /**
     * Broadcast to all admins and owners
     */
    public function sendToAdminsAndOwners(
        string $title,
        string $message,
        string $type = 'booking',
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void {
        $adminsAndOwners = User::whereIn('role', ['admin', 'owner'])->get();

        foreach ($adminsAndOwners as $user) {
            $this->sendNotification($user->id, $title, $message, $type, $referenceId, $referenceType);
        }
    }

    /**
     * Helper: Notify when a booking is created
     */
    public function notifyBookingCreated(Booking $booking): void
    {
        // 1. Notify Customer (if registered)
        if ($booking->user_id) {
            $this->sendNotification(
                $booking->user_id,
                'Booking Lapangan Berhasil!',
                "Pemesanan lapangan Anda (#{$booking->booking_number}) tanggal " . $booking->date->format('Y-m-d') . " pukul " . substr($booking->start_time, 0, 5) . " telah dikonfirmasi.",
                'booking',
                $booking->id,
                'booking'
            );
        }

        // 2. Notify Owners & Admins
        $this->sendToAdminsAndOwners(
            'Booking Baru Masuk!',
            "Customer {$booking->customer_name} telah memesan lapangan {$booking->court->name} untuk tanggal " . $booking->date->format('Y-m-d') . " pukul " . substr($booking->start_time, 0, 5) . ".",
            'booking',
            $booking->id,
            'booking'
        );
    }

    /**
     * Helper: Notify when a booking is rescheduled
     */
    public function notifyBookingRescheduled(Booking $booking): void
    {
        // 1. Notify Customer (if registered)
        if ($booking->user_id) {
            $this->sendNotification(
                $booking->user_id,
                'Jadwal Booking Diperbarui!',
                "Jadwal sewa lapangan Anda (#{$booking->booking_number}) dipindahkan ke tanggal " . $booking->date->format('Y-m-d') . " pukul " . substr($booking->start_time, 0, 5) . ".",
                'booking',
                $booking->id,
                'booking'
            );
        }

        // 2. Notify Owners & Admins
        $this->sendToAdminsAndOwners(
            'Jadwal Booking Berubah!',
            "Admin telah memindahkan jadwal booking (#{$booking->booking_number}) ke tanggal " . $booking->date->format('Y-m-d') . " pukul " . substr($booking->start_time, 0, 5) . ".",
            'booking',
            $booking->id,
            'booking'
        );
    }

    /**
     * Helper: Notify when a booking is cancelled
     */
    public function notifyBookingCancelled(Booking $booking, string $reason): void
    {
        // 1. Notify Customer (if registered)
        if ($booking->user_id) {
            $this->sendNotification(
                $booking->user_id,
                'Booking Anda Dibatalkan',
                "Pemesanan lapangan Anda (#{$booking->booking_number}) telah dibatalkan dengan alasan: {$reason}.",
                'booking',
                $booking->id,
                'booking'
            );
        }

        // 2. Notify Owners & Admins
        $this->sendToAdminsAndOwners(
            'Booking Dibatalkan',
            "Pemesanan (#{$booking->booking_number}) oleh {$booking->customer_name} dibatalkan. Alasan: {$reason}.",
            'booking',
            $booking->id,
            'booking'
        );
    }

    /**
     * Helper: Notify when a payment is confirmed
     */
    public function notifyPaymentConfirmed(Booking $booking, Payment $payment): void
    {
        // 1. Notify Customer (if registered)
        if ($booking->user_id) {
            $this->sendNotification(
                $booking->user_id,
                'Pembayaran Diterima!',
                "Pembayaran sewa lapangan Anda (#{$booking->booking_number}) via " . strtoupper($payment->payment_method) . " telah dikonfirmasi lunas.",
                'payment',
                $booking->id,
                'booking'
            );
        }

        // 2. Notify Owners & Admins
        $this->sendToAdminsAndOwners(
            'Pembayaran Terkonfirmasi',
            "Pembayaran sewa lapangan (#{$booking->booking_number}) lunas sebesar " . number_format($payment->amount, 0, ',', '.') . " via " . strtoupper($payment->payment_method) . ".",
            'payment',
            $booking->id,
            'booking'
        );
    }
}
