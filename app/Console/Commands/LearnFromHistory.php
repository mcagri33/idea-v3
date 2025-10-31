<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LearnFromHistory extends Command
{
    /**
     * Komut signature
     */
    protected $signature = 'documents:learn-from-history 
                            {--limit=100 : İşlenecek dosya sayısı}
                            {--category= : Belirli kategori ID}
                            {--year= : Belirli yıl}
                            {--dry-run : Sadece rapor göster, öğrenme yapma}';
    
    /**
     * Komut açıklaması
     */
    protected $description = 'Onaylanmış/reddedilmiş dosyalardan AI\'ı sessizce eğitir (Telegram bildirimi GÖNDERMEZ)';

    /**
     * Komutu çalıştır
     */
    public function handle()
    {
        $this->info('🧠 AI Geçmişten Öğrenme Başlatılıyor...');
        $this->info('📚 Onaylanmış ve reddedilmiş dosyalardan feedback alınacak');
        $this->newLine();
        
        // Sorgu: Sadece status=0 veya 1 olanlar (admin karar vermiş)
        $query = Document::with(['user', 'category'])
            ->whereIn('status', [0, 1]) // Onaylanmış veya reddedilmiş
            ->whereNull('ai_learned_at'); // Daha önce öğrenilmemiş
        
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
        
        $limit = (int) $this->option('limit');
        $totalCount = $query->count();
        $documents = $query->orderBy('created_at', 'desc')->take($limit)->get();
        
        if ($documents->isEmpty()) {
            $this->warn('⚠️  Öğrenilecek dosya bulunamadı.');
            $this->info('💡 Tüm dosyalar zaten işlenmiş veya status=2 (beklemede)');
            return Command::SUCCESS;
        }
        
        $this->info("📊 Toplam {$totalCount} öğrenilebilir dosya bulundu");
        $this->info("🎯 İlk {$documents->count()} dosya işlenecek...");
        $this->newLine();
        
        // İstatistikler
        $approved = $documents->where('status', 1)->count();
        $rejected = $documents->where('status', 0)->count();
        
        $this->table(
            ['Durum', 'Sayı'],
            [
                ['Onaylanmış (Status=1)', $approved],
                ['Reddedilmiş (Status=0)', $rejected],
                ['Toplam', $documents->count()],
            ]
        );
        
        $this->newLine();
        
        // Dry-run kontrolü
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY-RUN modu - Sadece rapor gösterildi, öğrenme yapılmadı.');
            return Command::SUCCESS;
        }
        
        // Onay iste
        if (!$this->confirm('AI bu dosyalardan öğrensin mi?', true)) {
            $this->warn('❌ İşlem iptal edildi.');
            return Command::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        $correctPredictions = 0;
        $wrongPredictions = 0;
        
        foreach ($documents as $document) {
            try {
                // Dosya mevcut mu kontrol et
                $filePath = Storage::disk('public')->path($document->file_path);
                
                if (!file_exists($filePath)) {
                    $errorCount++;
                    $bar->advance();
                    continue;
                }
                
                // AI'dan analiz al (bot'a HTTP istek at)
                $botUrl = env('BOT_WEBHOOK_URL', 'http://localhost:3002/notify');
                $analysisUrl = str_replace('/notify', '/analyze', $botUrl);
                
                // Basit analiz yap (local - bot'suz da olur)
                $aiPrediction = $this->getSimplePrediction($document, $filePath);
                
                // Admin kararı (mevcut status)
                $adminApproved = ($document->status == 1);
                
                // AI tahmini
                $aiApproved = ($aiPrediction['risk_score'] < 50);
                
                // Doğru tahmin mi?
                $isCorrect = ($aiApproved == $adminApproved);
                
                if ($isCorrect) {
                    $correctPredictions++;
                } else {
                    $wrongPredictions++;
                }
                
                // Feedback verisi hazırla
                $feedbackData = [
                    'documentId' => $document->id,
                    'approved' => $adminApproved,
                    'warnings' => $aiPrediction['warnings'] ?? [],
                    'category' => $document->category->name ?? 'Diğer',
                    'riskScore' => $aiPrediction['risk_score'],
                    'aiPrediction' => $aiPrediction['validity'],
                    'adminNote' => $document->rejection_note ?? ''
                ];
                
                // Bot'a feedback gönder (sessiz öğrenme)
                $this->sendFeedbackToBot($feedbackData);
                
                // Document'i işaretla (bir daha öğrenilmesin)
                $document->update([
                    'ai_learned_at' => now(),
                    'ai_was_correct' => $isCorrect
                ]);
                
                $successCount++;
                $bar->advance();
                
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Öğrenme hatası Document #{$document->id}: " . $e->getMessage());
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Sonuç özeti
        $this->info('✅ Öğrenme tamamlandı!');
        $this->newLine();
        
        $accuracy = $successCount > 0 ? round(($correctPredictions / $successCount) * 100, 1) : 0;
        
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['İşlenen Dosya', $successCount],
                ['Hatalı', $errorCount],
                ['Doğru Tahmin', $correctPredictions],
                ['Yanlış Tahmin', $wrongPredictions],
                ['AI Doğruluk', $accuracy . '%'],
            ]
        );
        
        $this->newLine();
        $this->info('🎯 AI pattern\'ları güncellendi!');
        $this->info('📊 Artık daha doğru analizler yapacak.');
        $this->newLine();
        $this->line('💡 Bekleyen dosyaları analiz etmek için:');
        $this->line('   php artisan documents:analyze-pending');
        
        return Command::SUCCESS;
    }
    
    /**
     * Basit AI tahmini yap (bot olmadan da çalışır)
     */
    private function getSimplePrediction($document, $filePath)
    {
        // Dosya adı ve açıklamadan basit analiz
        $text = strtolower($document->document_name . ' ' . $document->description);
        
        $warnings = [];
        $riskScore = 0;
        
        // Basit kurallar
        if (strlen($text) < 10) {
            $warnings[] = 'Dosya açıklaması çok kısa';
            $riskScore += 30;
        }
        
        if (strpos($text, 'eksik') !== false) {
            $warnings[] = 'Eksik ifadesi bulundu';
            $riskScore += 25;
        }
        
        if (strpos($text, 'hata') !== false || strpos($text, 'yanlis') !== false) {
            $warnings[] = 'Hata ifadesi bulundu';
            $riskScore += 30;
        }
        
        if (!$document->file_year) {
            $warnings[] = 'Yıl bilgisi yok';
            $riskScore += 15;
        }
        
        // Risk skorunu sınırla
        $riskScore = min(100, max(0, $riskScore));
        
        return [
            'risk_score' => $riskScore,
            'validity' => $riskScore < 30 ? 'ok' : ($riskScore < 70 ? 'warning' : 'critical'),
            'warnings' => $warnings,
            'category' => $document->category->name ?? 'Diğer',
            'year' => $document->file_year ?? date('Y')
        ];
    }
    
    /**
     * Bot'a feedback gönder (öğrenme için)
     */
    private function sendFeedbackToBot($feedbackData)
    {
        try {
            $botUrl = env('BOT_WEBHOOK_URL', 'http://localhost:3002');
            $feedbackUrl = str_replace('/notify', '/feedback', $botUrl);
            
            Http::timeout(5)->post($feedbackUrl, $feedbackData);
            
        } catch (\Exception $e) {
            // Hata loglansın ama işlem devam etsin
            Log::warning("Bot feedback gönderilemedi: " . $e->getMessage());
        }
    }
}

