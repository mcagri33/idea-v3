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
        try { 
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

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $documentsByUserAndCategory = Document::query()
            ->where('file_year', $year)
            ->whereIn('user_id', $customers->pluck('id'))
            ->get()
            ->groupBy(function (Document $document) {
                return $document->user_id . ':' . $document->category_id;
            });

        foreach ($customers as $customer) {
            $missingCategories = [];

            foreach ($categories as $category) {
                $key = $customer->id . ':' . $category->id;

                if (! $documentsByUserAndCategory->has($key)) {
                    $missingCategories[] = $category;
                }
            }

            if (count($missingCategories) === 0) {
                $usersWithCompleteDocs++;
                $this->comment("⏭️  {$customer->name} - Tüm belgeler tamamlanmış");
                $bar->advance();
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
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

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

        return self::SUCCESS; } 
        catch (\Exception $exception) { 
             $this->sendErrorReportToAdmins($exception);
        
            Log::error('Document reminders command failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return self::FAILURE; 
        }
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

    protected function sendErrorReportToAdmins(\Exception $exception): void
{
    try {
        $admins = User::role('Admin')
            ->where('status', 1)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new \App\Mail\CommandErrorMail($exception)
            );
        }
    } catch (\Exception $e) {
        Log::error('Error report mail gönderilemedi', [
            'error' => $e->getMessage(),
        ]);
    }
}
}
