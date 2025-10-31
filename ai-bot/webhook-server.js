import express from 'express';
import dotenv from 'dotenv';
import { analyzeDocument } from './ai-service.js';
import path from 'path';
import { fileURLToPath } from 'url';

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(express.json());

const PORT = process.env.BOT_WEBHOOK_PORT || 3002;
const STORAGE_PATH = path.join(__dirname, '..', process.env.STORAGE_PATH || 'public/storage');

// Webhook endpoint
app.post('/webhook/new-document', async (req, res) => {
  console.log('📨 Yeni dosya webhook alındı:', req.body);
  
  try {
    const document = req.body;
    
    // Hemen yanıt ver (Laravel beklemeden devam etsin)
    res.status(200).json({ success: true, message: 'Webhook alındı' });
    
    // Analizi arka planda yap
    setTimeout(async () => {
      try {
        const result = await analyzeDocument(document, STORAGE_PATH);
        
        if (result.success) {
          // Telegram bildirimi gönder (bot.js'e event gönder)
          console.log('✅ Analiz tamamlandı, Telegram bildirimi gönderilecek');
          // Bu kısmı bot.js ile entegre etmek gerekir
        }
      } catch (error) {
        console.error('Webhook analiz hatası:', error.message);
      }
    }, 1000);
    
  } catch (error) {
    console.error('Webhook hatası:', error.message);
    res.status(500).json({ success: false, error: error.message });
  }
});

app.listen(PORT, () => {
  console.log(`🌐 Webhook server çalışıyor: http://127.0.0.1:${PORT}`);
});