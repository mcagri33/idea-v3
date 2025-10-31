<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyzeDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("AI analiz başlatılıyor: Document #{$this->document->id}");

            // Dosya yolunu kontrol et
            $filePath = Storage::disk('public')->path($this->document->file_path);
            
            if (!file_exists($filePath)) {
                Log::error("Dosya bulunamadı: {$filePath}");
                return;
            }

            // Telegram bot'a bildirim gönder
            $botWebhookUrl = env('BOT_WEBHOOK_URL', 'http://localhost:3002/notify');
            
            $payload = [
                'document_id' => $this->document->id,
                'firm' => $this->document->user->company ?? $this->document->user->name,
                'category' => $this->document->category->name ?? 'Diğer',
                'year' => $this->document->file_year ?? date('Y'),
                'risk_score' => $this->document->ai_risk_score ?? 0,
                'validity' => $this->document->ai_validity ?? 'pending',
                'warnings' => $this->document->ai_warnings ?? [],
                'summary' => $this->document->ai_summary ?? 'Analiz bekleniyor',
            ];

            $response = Http::timeout(30)->post($botWebhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Telegram bot'a bildirim gönderildi: Document #{$this->document->id}");
                
                // Document'e analiz durumu ekle
                $this->document->update([
                    'ai_notified_at' => now(),
                ]);
            } else {
                Log::error("Telegram bot bildirimi başarısız: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("AnalyzeDocumentJob hatası: " . $e->getMessage());
            
            // Hata durumunda tekrar denenebilir
            if ($this->attempts() < 3) {
                $this->release(60); // 60 saniye sonra tekrar dene
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeDocumentJob kalıcı hata: Document #{$this->document->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}

