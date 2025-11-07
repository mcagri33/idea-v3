<?php

namespace App\Console\Commands;

use App\Mail\AdminReminderReportMail;
use App\Mail\DocumentReminderMail;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'documents:send-reminders {--year=}';

    /**
     * The console command description.
     */
    protected $description = 'Eksik belge bulunan kullanıcılara otomatik hatırlatma maili gönderir (varsayılan: bir önceki yıl)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->option('year') ?? now()->year - 1;
        $currentYear = now()->year;

        $this->info(str_repeat('═', 47));
        $this->info('📅 Belge Hatırlatma Sistemi');
        $this->info(str_repeat('═', 47));
        $this->info('Bugün: ' . now()->format('d.m.Y H:i'));
        $this->info("Kontrol Edilen Yıl: {$year}");
        $this->info("Not: {$currentYear} yılındayken {$year} yılının belgelerini kontrol ediyoruz.");
        $this->info(str_repeat('═', 47));
        $this->newLine();

        $customers = User::role('Customer')
            ->where('status', 1)
            ->get();

        if ($customers->isEmpty()) {
            $this->warn('⚠️  Sistemde aktif müşteri bulunamadı.');
            return self::SUCCESS;
        }

        $categories = DocumentCategory::orderBy('order')->get();

        if ($categories->isEmpty()) {
            $this->warn('⚠️  Sistemde tanımlı kategori bulunamadı.');
            return self::SUCCESS;
        }

        $remindersSent = [];
        $usersWithMissingDocs = 0;
        $usersWithCompleteDocs = 0;

        $this->info("🔍 {$customers->count()} müşteri kontrol ediliyor...\n");

        foreach ($customers as $customer) {
            $missingCategories = [];

            foreach ($categories as $category) {
                $hasDocument = Document::where('user_id', $customer->id)
                    ->where('category_id', $category->id)
                    ->where('file_year', $year)
                    ->exists();

                if (! $hasDocument) {
                    $missingCategories[] = $category;
                }
            }

            if (count($missingCategories) === 0) {
                $usersWithCompleteDocs++;
                $this->comment("⏭️  {$customer->name} - Tüm belgeler tamamlanmış");
                continue;
            }

            $this->sendReminderToCustomer($customer, $missingCategories, $year);

            $remindersSent[] = [
                'user' => $customer,
                'missing_count' => count($missingCategories),
                'categories' => $missingCategories,
            ];

            $usersWithMissingDocs++;
            $this->info("✅ {$customer->name} ({$customer->email})");
            $this->comment('   └─ ' . count($missingCategories) . ' eksik kategori');
        }

        if (! empty($remindersSent)) {
            $this->newLine();
            $this->info('📧 Admin’e özet rapor gönderiliyor...');
            $this->sendAdminReport($remindersSent, $year);
            $this->info('✅ Admin raporu gönderildi.');
        } else {
            $this->newLine();
            $this->info('🎉 Tüm kullanıcıların belgeleri eksiksiz. Mail gönderilmedi.');
        }

        $this->newLine();
        $this->info(str_repeat('═', 47));
        $this->info('✅ İşlem Tamamlandı!');
        $this->info(str_repeat('═', 47));
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Toplam Müşteri', $customers->count()],
                ['Eksik Belgesi Olan', $usersWithMissingDocs],
                ['Tamamlanmış Belgesi Olan', $usersWithCompleteDocs],
                ['Gönderilen Hatırlatma Maili', count($remindersSent)],
                ['Admin Rapor Maili', empty($remindersSent) ? 0 : 1],
                ['Toplam Mail', empty($remindersSent) ? 0 : count($remindersSent) + 1],
            ]
        );
        $this->info(str_repeat('═', 47));
        $this->newLine();

        Log::info('Document reminders sent', [
            'year' => $year,
            'current_year' => $currentYear,
            'total_customers' => $customers->count(),
            'users_with_missing_docs' => $usersWithMissingDocs,
            'users_with_complete_docs' => $usersWithCompleteDocs,
            'reminders_sent' => count($remindersSent),
        ]);

        return self::SUCCESS;
    }

    protected function sendReminderToCustomer(User $customer, array $missingCategories, int $year): void
    {
        try {
            Mail::to($customer->email)->send(
                new DocumentReminderMail($customer, collect($missingCategories), $year)
            );
        } catch (\Exception $exception) {
            Log::error("Belge hatırlatma maili gönderilemedi: {$customer->email}", [
                'error' => $exception->getMessage(),
            ]);

            $this->error("   ❌ Mail gönderilemedi: {$exception->getMessage()}");
        }
    }

    protected function sendAdminReport(array $remindersSent, int $year): void
    {
        try {
            $admins = User::role('Admin')
                ->where('status', 1)
                ->get();

            if ($admins->isEmpty()) {
                $this->warn('⚠️  Sistemde aktif admin bulunamadı.');
                return;
            }

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(
                    new AdminReminderReportMail($remindersSent, $year)
                );
            }
        } catch (\Exception $exception) {
            Log::error('Admin raporu gönderilemedi', [
                'error' => $exception->getMessage(),
            ]);

            $this->error('❌ Admin raporu gönderilemedi: ' . $exception->getMessage());
        }
    }
}
