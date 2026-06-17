<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export bookings to CSV
     */
    public function exportBookingsCsv($bookings): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="daftar-booking-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Nomor Booking', 'Nama Customer', 'WhatsApp/HP', 'Email', 
            'Lapangan', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 
            'Total Harga', 'Status', 'Tanggal Dibuat'
        ];

        $callback = function() use ($bookings, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->booking_number,
                    $booking->customer_name,
                    $booking->customer_phone,
                    $booking->customer_email ?? '-',
                    $booking->court->name ?? '-',
                    $booking->date->format('Y-m-d'),
                    substr($booking->start_time, 0, 5),
                    substr($booking->end_time, 0, 5),
                    (int) $booking->total_price,
                    strtoupper($booking->status),
                    $booking->created_at->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export transactions/payments to CSV
     */
    public function exportPaymentsCsv($payments): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-transaksi-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID Transaksi', 'Nomor Booking', 'Nama Customer', 'Lapangan',
            'Metode Pembayaran', 'Jumlah Bayar', 'Jumlah Refund', 
            'Alasan Refund', 'Dikonfirmasi Oleh', 'Waktu Konfirmasi'
        ];

        $callback = function() use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->booking->booking_number ?? '-',
                    $payment->booking->customer_name ?? '-',
                    $payment->booking->court->name ?? '-',
                    strtoupper($payment->payment_method ?? 'CASH'),
                    (int) $payment->amount,
                    (int) $payment->refund_amount,
                    $payment->refund_reason ?? '-',
                    $payment->confirmedBy->name ?? '-',
                    $payment->confirmed_at ? $payment->confirmed_at->format('Y-m-d H:i:s') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Financial Report (Revenue vs Expenses) to CSV
     */
    public function exportFinancialReportCsv(array $summary, array $revenueByCourt, array $expenseByCategory, array $monthlyTrend): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-keuangan-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($summary, $revenueByCourt, $expenseByCategory, $monthlyTrend) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Ringkasan
            fputcsv($file, ['RINGKASAN KEUANGAN'], ';');
            fputcsv($file, ['Total Pendapatan Bersih', (int) $summary['total_revenue']], ';');
            fputcsv($file, ['Total Pengeluaran', (int) $summary['total_expense']], ';');
            fputcsv($file, ['Keuntungan/Kerugian Bersih', (int) $summary['net_profit']], ';');
            fputcsv($file, [], ';');

            // Section 2: Pendapatan per Lapangan
            fputcsv($file, ['PENDAPATAN PER LAPANGAN'], ';');
            fputcsv($file, ['Lapangan', 'Jumlah Booking', 'Total Pendapatan'], ';');
            foreach ($revenueByCourt as $court) {
                fputcsv($file, [$court['court_name'], $court['bookings_count'], (int)$court['revenue']], ';');
            }
            fputcsv($file, [], ';');

            // Section 3: Pengeluaran per Kategori
            fputcsv($file, ['PENGELUARAN PER KATEGORI'], ';');
            fputcsv($file, ['Kategori', 'Total Pengeluaran'], ';');
            foreach ($expenseByCategory as $cat => $amount) {
                fputcsv($file, [strtoupper($cat), (int)$amount], ';');
            }
            fputcsv($file, [], ';');

            // Section 4: Tren Bulanan
            fputcsv($file, ['TREN KEUANGAN BULANAN'], ';');
            fputcsv($file, ['Bulan', 'Pendapatan', 'Pengeluaran', 'Keuntungan'], ';');
            foreach ($monthlyTrend as $item) {
                fputcsv($file, [$item['name'], (int)$item['revenue'], (int)$item['expense'], (int)$item['profit']], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
