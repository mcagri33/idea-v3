<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIBotController extends Controller
{
    /**
     * Telegram bot'tan AI kararını al
     * 
     * POST /api/documents/{id}/ai-decision
     */
    public function receiveAIDecision(Request $request, $documentId)
    {
        try {
            // Validasyon
            $validator = Validator::make($request->all(), [
                'approved' => 'required|boolean',
                'timestamp' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasyon hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Document'i bul
            $document = Document::find($documentId);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokuman bulunamadı'
                ], 404);
            }

            // AI kararını kaydet
            $approved = $request->input('approved');
            
            $document->update([
                'ai_decision' => $approved ? 'approved' : 'rejected',
                'ai_decided_at' => now(),
                // İsteğe bağlı olarak status'u da güncelleyebilirsiniz
                // 'status' => $approved ? 1 : 0,
            ]);

            Log::info("AI kararı kaydedildi: Document #{$documentId} -> " . ($approved ? 'Onay' : 'Red'));

            return response()->json([
                'success' => true,
                'message' => 'AI kararı kaydedildi',
                'document_id' => $documentId,
                'decision' => $approved ? 'approved' : 'rejected'
            ]);

        } catch (\Exception $e) {
            Log::error("AI karar alma hatası: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sunucu hatası',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Telegram bot durumunu kontrol et
     * 
     * GET /api/bot/status
     */
    public function checkBotStatus(Request $request)
    {
        try {
            $botWebhookUrl = env('BOT_WEBHOOK_URL', 'http://localhost:3002/health');
            
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($botWebhookUrl);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'bot_status' => 'online',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'bot_status' => 'offline',
                    'message' => 'Bot erişilebilir değil'
                ], 503);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'bot_status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI analiz istatistikleri
     * 
     * GET /api/ai/statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = [
                'total_documents' => Document::count(),
                'ai_analyzed' => Document::whereNotNull('ai_notified_at')->count(),
                'ai_approved' => Document::where('ai_decision', 'approved')->count(),
                'ai_rejected' => Document::where('ai_decision', 'rejected')->count(),
                'pending' => Document::whereNull('ai_decision')
                    ->whereNotNull('ai_notified_at')
                    ->count(),
            ];

            $stats['success_rate'] = $stats['ai_analyzed'] > 0 
                ? round(($stats['ai_approved'] / $stats['ai_analyzed']) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

