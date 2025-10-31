<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Jobs\AnalyzeDocumentJob;
use Illuminate\Console\Command;

class AnalyzeExistingDocuments extends Command
{
    /**
     * Komut signature
     */
    protected $signature = 'documents:analyze-existing 
                            {--limit=50 : Her seferde kaç dosya işlensin}
                            {--only-unanalyzed : Sadece analiz edilmemiş dosyalar}
                            {--category= : Belirli bir kategori ID}
                            {--year= : Belirli bir yıl}
                            {--user= : Belirli bir kullanıcı ID}';
    
    /**
     * Komut açıklaması
     */
    protected $description = 'Mevcut dosyaları AI ile analiz eder (toplu analiz)';

    /**
     * Komutu çalıştır
     */
    public function handle()
    {
        $this->info('🤖 Toplu Dosya Analizi Başlatılıyor...');
        $this->newLine();
        
        // Sorgu oluştur
        $query = Document::with(['user', 'category']);
        
        // Sadece analiz edilmemiş dosyalar mı?
        if ($this->option('only-unanalyzed')) {
            $query->whereNull('ai_notified_at');
            $this->info('🔍 Filtre: Sadece analiz edilmemiş dosyalar');
        }
        
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
            $this->warn('⚠️  İşlenecek dosya bulunamadı.');
            return Command::SUCCESS;
        }
        
        $this->info("📊 Toplam {$totalCount} dosya bulundu");
        $this->info("🎯 İlk {$documents->count()} dosya işlenecek...");
        $this->newLine();
        
        // Onay iste
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
                // Job'u queue'ya ekle
                AnalyzeDocumentJob::dispatch($document);
                
                $successCount++;
                $bar->advance();
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("❌ Document #{$document->id} hatası: " . $e->getMessage());
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Sonuç özeti
        $this->info('✅ Toplu analiz tamamlandı!');
        $this->newLine();
        $this->table(
            ['Durum', 'Sayı'],
            [
                ['Başarılı', $successCount],
                ['Hatalı', $errorCount],
                ['Toplam', $successCount + $errorCount],
            ]
        );
        
        $this->newLine();
        $this->warn('💡 Queue worker çalıştığından emin olun:');
        $this->line('   php artisan queue:work');
        $this->newLine();
        $this->info('📊 İstatistikleri görüntülemek için:');
        $this->line('   php artisan queue:failed  (başarısız job\'lar)');
        $this->line('   tail -f storage/logs/laravel.log  (log izleme)');
        
        return Command::SUCCESS;
    }
}

