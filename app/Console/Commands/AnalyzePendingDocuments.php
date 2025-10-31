<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Jobs\AnalyzeDocumentJob;
use Illuminate\Console\Command;

class AnalyzePendingDocuments extends Command
{
    /**
     * Komut signature
     */
    protected $signature = 'documents:analyze-pending 
                            {--limit=50 : İşlenecek dosya sayısı}
                            {--category= : Belirli kategori ID}
                            {--year= : Belirli yıl}
                            {--user= : Belirli kullanıcı ID}';
    
    /**
     * Komut açıklaması
     */
    protected $description = 'Bekleyen dosyaları (status=2) analiz eder ve Telegram\'a bildirir';

    /**
     * Komutu çalıştır
     */
    public function handle()
    {
        $this->info('📊 Bekleyen Dosyalar Analizi Başlatılıyor...');
        $this->info('⏳ Status=2 (Beklemede) olan dosyalar işlenecek');
        $this->newLine();
        
        // Sorgu: Sadece status=2 (beklemede) olanlar
        $query = Document::with(['user', 'category'])
            ->where('status', 2) // Beklemede
            ->whereNull('ai_notified_at'); // Daha önce analiz edilmemiş
        
        // Kategori filtresi
        if ($this->option('category')) {
            $query->where('category_id', $this->option('category'));
            $this->info('🔍 Filtre: Kategori ID = ' . $this->option('category'));
        }
        
        // Yıl filtresi
        if ($this->option('year')) {
            $query->where('file_year', $this->option('year'));
            $this->info('🔍 Filtre: Yıl = ' . $this->option('year'));
        }
        
        // Kullanıcı filtresi
        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
            $this->info('🔍 Filtre: Kullanıcı ID = ' . $this->option('user'));
        }
        
        $limit = (int) $this->option('limit');
        $totalCount = $query->count();
        $documents = $query->orderBy('created_at', 'desc')->take($limit)->get();
        
        if ($documents->isEmpty()) {
            $this->warn('⚠️  Bekleyen dosya bulunamadı.');
            $this->info('💡 Tüm bekleyen dosyalar zaten analiz edilmiş.');
            return Command::SUCCESS;
        }
        
        $this->info("📊 Toplam {$totalCount} bekleyen dosya bulundu");
        $this->info("🎯 İlk {$documents->count()} dosya işlenecek...");
        $this->newLine();
        
        // Kullanıcı dağılımı
        $userGroups = $documents->groupBy('user_id');
        $this->info("👥 {$userGroups->count()} farklı kullanıcının dosyası");
        $this->newLine();
        
        // Onay iste
        $this->warn('⚠️  Bu dosyalar için Telegram bildirimi GÖNDERİLECEK!');
        if (!$this->confirm('Devam edilsin mi?', true)) {
            $this->warn('❌ İşlem iptal edildi.');
            return Command::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($documents as $document) {
            try {
                // Job'u queue'ya ekle (Telegram bildirimi ile)
                AnalyzeDocumentJob::dispatch($document);
                
                $successCount++;
                $bar->advance();
                
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Analiz job hatası Document #{$document->id}: " . $e->getMessage());
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Sonuç özeti
        $this->info('✅ Bekleyen dosyalar kuyruğa eklendi!');
        $this->newLine();
        $this->table(
            ['Durum', 'Sayı'],
            [
                ['Kuyruğa Eklendi', $successCount],
                ['Hatalı', $errorCount],
                ['Toplam', $successCount + $errorCount],
            ]
        );
        
        $this->newLine();
        $this->warn('📱 Telegram\'da yaklaşık ' . $successCount . ' bildirim alacaksınız!');
        $this->info('⏱️  Tahmini süre: ' . ceil($successCount / 2) . ' dakika');
        $this->newLine();
        $this->info('💡 Queue worker\'ın çalıştığından emin olun:');
        $this->line('   php artisan queue:work');
        $this->newLine();
        $this->info('📊 İlerlemeyi izlemek için:');
        $this->line('   pm2 logs ideadocs-ai-bot -f  (bot logs)');
        $this->line('   tail -f storage/logs/laravel.log  (Laravel logs)');
        
        return Command::SUCCESS;
    }
}

