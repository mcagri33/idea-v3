/**
 * IdeaDocs AI Telegram Bot
 * Firma bazlı doküman analizi ve otomatik risk değerlendirmesi
 * Tamamen local ve CPU dostu çalışır
 */

import { Telegraf, Markup } from 'telegraf';
import dotenv from 'dotenv';
import axios from 'axios';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

// PDF2JSON Warning Filtresi - Gereksiz logları bastır
const ORIGINAL_WARN = console.warn;
console.warn = function(...args) {
  const message = args[0]?.toString() || '';
  
  // pdf2json'dan gelen gereksiz warning'leri filtrele
  const ignoredWarnings = [
    'Setting up fake worker',
    'TT: undefined function',
    'TT: complementing',
    'Unsupported: field.type',
    'NOT valid form element',
    'Bad uncompressed size',
    'The decode map is not',
    'Unterminated string'
  ];
  
  if (ignoredWarnings.some(ignored => message.includes(ignored))) {
    return; // Bu warning'leri gösterme
  }
  
  ORIGINAL_WARN.apply(console, args); // Diğer warning'leri göster
};

// Modüller
import { 
  analyzeDocument, 
  analyzeBatch, 
  generateFirmReport,
  generateCategoryReport,
  getRiskyDocuments,
  getSystemStats
} from './ai-service.js';

import {
  loadFirmMemory,
  loadGlobalMemory,
  saveGlobalMemory,
  processApprovalFeedback,
  processRejectionFeedback,
  listAllFirms
} from './feedback.js';

import { extractText } from './utils/extractor.js';

// Konfigürasyon
dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const BOT_TOKEN = process.env.TG_BOT_TOKEN;
const ALLOWED_IDS = process.env.ALLOWED_TELEGRAM_IDS?.split(',').map(id => parseInt(id)) || [];
const LARAVEL_API = process.env.LARAVEL_API_URL || 'http://127.0.0.1:8000/api';

// Storage path - absolute veya relative path desteği (production-ready)
const STORAGE_PATH = process.env.STORAGE_PATH 
  ? (path.isAbsolute(process.env.STORAGE_PATH) 
      ? process.env.STORAGE_PATH 
      : path.join(__dirname, '..', process.env.STORAGE_PATH))
  : path.join(__dirname, '..', 'public/storage');

if (!BOT_TOKEN) {
  console.error('❌ TG_BOT_TOKEN bulunamadı! .env dosyasını kontrol edin.');
  process.exit(1);
}

// Bot başlat
const bot = new Telegraf(BOT_TOKEN);

// Yetki kontrolü middleware
bot.use(async (ctx, next) => {
  const userId = ctx.from?.id;
  
  if (ALLOWED_IDS.length > 0 && !ALLOWED_IDS.includes(userId)) {
    console.warn(`⚠️ Yetkisiz erişim denemesi: ${userId}`);
    return ctx.reply('❌ Bu botu kullanma yetkiniz yok.');
  }
  
  return next();
});

// Komut menüsünü ayarla
bot.telegram.setMyCommands([
  { command: 'start', description: '🤖 Sistemi başlat' },
  { command: 'dashboard', description: '📊 Genel sistem durumu' },
  { command: 'firmalar_durum', description: '📋 Tüm firmalar (pagination)' },
  { command: 'firma_stats', description: '📊 Firma detaylı istatistik' },
  { command: 'bekleyen_firmalar', description: '⏳ Bekleyen dosyalar (pagination)' },
  { command: 'riskli_firmalar', description: '🚨 Riskli firmalar' },
  { command: 'top10_basarili', description: '🏆 En başarılı 10 firma' },
  { command: 'top10_riskli', description: '⚠️ En riskli 10 firma' },
  { command: 'kategori_performance', description: '📁 Kategori başarı raporu' },
  { command: 'yil_karsilastir', description: '📅 Yıl bazlı trend' },
  { command: 'analiz_hepsi', description: '🤖 AI analizi başlat' },
  { command: 'ai_durum', description: '🧠 AI öğrenme durumu' },
  { command: 'rapor', description: '📈 Günlük rapor' }
]);

// Temporary storage for callbacks
const pendingActions = new Map();

// Pagination state storage
const paginationCache = new Map();

/**
 * /start komutu
 */
bot.command('start', async (ctx) => {
  const botUsername = bot.botInfo?.username || 'ideadocs_ai_bot';
  const welcomeMessage = `
🤖 <b>IdeaDocs AI Denetim Asistanı</b>

Hoş geldiniz! Ben, dokümanlarınızı analiz eden ve kapsamlı raporlar sunan AI asistanınızım.

📊 <b>Rapor Komutları (Pagination Destekli):</b>
• /dashboard - Genel durum özeti
• /firmalar_durum - Tüm firmalar (sayfa sayfa) 📄
• /firma_stats [id] - Firma detaylı rapor
• /bekleyen_firmalar - Bekleyenler (sayfa sayfa) 📄
• /riskli_firmalar - Dikkat gereken firmalar
• /top10_basarili - En başarılı firmalar 🏆
• /top10_riskli - En riskli firmalar 🚨

📁 <b>Kategori ve Analiz:</b>
• /kategori_performance - Kategori başarı oranları
• /yil_karsilastir [id] - Firma yıl bazlı trend
• /analiz_hepsi - Bekleyen dosyaları AI analiz et

🔍 <b>INLINE ARAMA:</b>
<code>@${botUsername} firma_adi</code>

💡 <b>Hızlı Başlangıç:</b>
1. /dashboard → Genel durumu görün
2. /bekleyen_firmalar → Bekleyen dosyaları kontrol edin
3. /analiz_hepsi → AI analizi başlatın

🧠 <b>AI Öğrenme:</b>
Bot yıl ve kategori bazlı öğrenir, sürekli gelişir!
  `;
  
  await ctx.replyWithHTML(welcomeMessage);
});

/**
 * /firma komutu - Inline search ile firma arama
 */
bot.command('firma', async (ctx) => {
  const helpMessage = `
🔍 <b>Firma Arama</b>

<b>Kullanım:</b>
1. Mesaj kutusuna <code>@${bot.botInfo?.username || 'bot'} firma_adi</code> yazın
2. Açılan listeden firmayı seçin

<b>Alternatif:</b>
• <code>/firma_id [id]</code> - Direkt ID ile rapor
  Örnek: <code>/firma_id 17</code>

• <code>/firma_list</code> - İlk 20 firmayı listele

💡 <b>İpucu:</b> Inline arama ile hızlıca firma bulabilirsiniz!
  `;
  
  await ctx.replyWithHTML(helpMessage);
});

/**
 * /firma_id [id] - Direkt ID ile firma raporu
 */
bot.command(/firma_id\s+(\d+)/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const report = generateFirmReport(userId);
    
    const message = `
📊 <b>Firma Raporu</b>

👤 <b>Firma ID:</b> ${userId}
📄 <b>Toplam Dosya:</b> ${report.stats.total_documents}
✅ <b>Onaylanan:</b> ${report.stats.total_approved}
❌ <b>Reddedilen:</b> ${report.stats.total_rejected}
📈 <b>Onay Oranı:</b> %${report.stats.approval_rate}

📅 <b>Yıl Bazlı Dağılım:</b>
${Object.entries(report.years || {}).sort((a, b) => b[0].localeCompare(a[0])).map(([year, stats]) => 
  `- ${year}: ✅${stats.approved} ❌${stats.rejected}`
).join('\n') || 'Henüz yıl verisi yok'}

🔍 <b>Risk Anahtar Kelimeleri:</b>
${report.stats.risk_keywords.length > 0 ? report.stats.risk_keywords.join(', ') : 'Henüz yok'}

📁 <b>Kategoriler:</b>
${Object.entries(report.categories).map(([cat, stats]) => 
  `- ${cat}: ✅${stats.approved} ❌${stats.rejected}`
).join('\n') || 'Henüz kategori yok'}

🕐 <b>Son Güncelleme:</b> ${report.last_updated || 'Bilinmiyor'}
    `;

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Firma raporu hatası:', error.message);
    await ctx.reply('❌ Firma raporu oluşturulamadı.');
  }
});

/**
 * /firma_list - İlk 20 firmayı listele
 */
bot.command('firma_list', async (ctx) => {
  try {
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;
    const users = [...new Set(documents.map(d => d.user))].filter(u => u);
    
    if (users.length === 0) {
      return ctx.reply('❌ Henüz firma bulunamadı.');
    }

    // Telegram mesaj limiti ~4096 karakter - max 100 firma göster
    const displayUsers = users.slice(0, 100);
    
    let message = `📊 <b>Firma Listesi (${displayUsers.length}/${users.length})</b>\n\n`;
    
    displayUsers.forEach((user, index) => {
      message += `${index + 1}. ${user.company || user.name} - ID: ${user.id}\n`;
      message += `   <code>/firma_id ${user.id}</code>\n\n`;
    });
    
    if (users.length > 100) {
      message += `\n⚠️ Toplam ${users.length} firma var. İlk 100 gösteriliyor.\n`;
      message += `💡 Inline arama ile diğerlerini bulabilirsiniz: @${bot.botInfo?.username} firma_adi`;
    }

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Firma listesi hatası:', error.message);
    await ctx.reply('❌ Firma listesi alınamadı.');
  }
});

/**
 * Firma raporu callback
 */
bot.action(/^firm_report_(\d+)$/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const report = generateFirmReport(userId);
    
    const message = `
📊 <b>Firma Raporu</b>

👤 <b>Firma ID:</b> ${userId}
📄 <b>Toplam Dosya:</b> ${report.stats.total_documents}
✅ <b>Onaylanan:</b> ${report.stats.total_approved}
❌ <b>Reddedilen:</b> ${report.stats.total_rejected}
📈 <b>Onay Oranı:</b> %${report.stats.approval_rate}

📅 <b>Yıl Bazlı Dağılım:</b>
${Object.entries(report.years || {}).sort((a, b) => b[0].localeCompare(a[0])).map(([year, stats]) => 
  `- ${year}: ✅${stats.approved} ❌${stats.rejected}`
).join('\n') || 'Henüz yıl verisi yok'}

🔍 <b>Risk Anahtar Kelimeleri:</b>
${report.stats.risk_keywords.length > 0 ? report.stats.risk_keywords.join(', ') : 'Henüz yok'}

📁 <b>Kategoriler:</b>
${Object.entries(report.categories).map(([cat, stats]) => 
  `- ${cat}: ✅${stats.approved} ❌${stats.rejected}`
).join('\n') || 'Henüz kategori yok'}

🕐 <b>Son Güncelleme:</b> ${report.last_updated || 'Bilinmiyor'}
    `;

    await ctx.answerCbQuery();
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Firma raporu hatası:', error.message);
    await ctx.answerCbQuery('❌ Rapor oluşturulamadı');
  }
});

/**
 * /ai_firma komutu - Inline search kullanımı
 */
bot.command('ai_firma', async (ctx) => {
  const helpMessage = `
🧠 <b>AI Hafızası Görüntüleme</b>

<b>Kullanım:</b>
• Inline: <code>@${bot.botInfo?.username || 'bot'} firma_adi</code> → "AI Hafızası" seçin
• Direkt: <code>/ai_id [id]</code>
  Örnek: <code>/ai_id 17</code>

💡 Inline arama ile hızlıca firmayı bulup AI hafızasını görüntüleyin!
  `;
  
  await ctx.replyWithHTML(helpMessage);
});

/**
 * /ai_id [id] - Direkt ID ile AI hafızası
 */
bot.command(/ai_id\s+(\d+)/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const memory = loadFirmMemory(userId);
    
    // Yıl bazlı toplam hesapla
    const years = memory.years || {};
    let totalApproved = 0;
    let totalRejected = 0;
    
    Object.values(years).forEach(yearData => {
      totalApproved += Object.keys(yearData.approved || {}).length;
      totalRejected += Object.keys(yearData.rejected || {}).length;
    });
    
    const message = `
🧠 <b>AI Hafızası - Firma ${userId}</b>

📚 <b>Öğrenilen Dosyalar:</b>
✅ Onaylı: ${totalApproved} dosya
❌ Reddedilen: ${totalRejected} dosya

📅 <b>Yıl Bazlı:</b>
${Object.entries(years).sort((a, b) => b[0].localeCompare(a[0])).map(([year, data]) => {
  const yApproved = Object.keys(data.approved || {}).length;
  const yRejected = Object.keys(data.rejected || {}).length;
  return `- ${year}: ✅${yApproved} ❌${yRejected}`;
}).join('\n') || 'Henüz yıl verisi yok'}

📊 <b>İstatistikler:</b>
- Ortalama Benzerlik: ${(memory.stats?.avg_similarity || 0).toFixed(2)}
- Toplam Analiz: ${memory.stats?.total_documents || 0}

⚠️ <b>Risk Kelimeleri:</b>
${memory.stats?.risk_keywords?.join(', ') || 'Henüz yok'}

📂 <b>Kategoriler:</b>
${Object.entries(memory.categories || {}).map(([cat, stats]) => 
  `- ${cat}: ${stats.approved + stats.rejected} dosya`
).join('\n') || 'Henüz yok'}
    `;

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('AI hafızası hatası:', error.message);
    await ctx.reply('❌ AI hafızası yüklenemedi.');
  }
});

/**
 * AI hafızası callback
 */
bot.action(/^ai_memory_(\d+)$/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const memory = loadFirmMemory(userId);
    
    // Yıl bazlı toplam hesapla
    const years = memory.years || {};
    let totalApproved = 0;
    let totalRejected = 0;
    
    Object.values(years).forEach(yearData => {
      totalApproved += Object.keys(yearData.approved || {}).length;
      totalRejected += Object.keys(yearData.rejected || {}).length;
    });
    
    const message = `
🧠 <b>AI Hafızası - Firma ${userId}</b>

📚 <b>Öğrenilen Dosyalar:</b>
✅ Onaylı: ${totalApproved} dosya
❌ Reddedilen: ${totalRejected} dosya

📅 <b>Yıl Bazlı:</b>
${Object.entries(years).sort((a, b) => b[0].localeCompare(a[0])).map(([year, data]) => {
  const yApproved = Object.keys(data.approved || {}).length;
  const yRejected = Object.keys(data.rejected || {}).length;
  return `- ${year}: ✅${yApproved} ❌${yRejected}`;
}).join('\n') || 'Henüz yıl verisi yok'}

📊 <b>İstatistikler:</b>
- Ortalama Benzerlik: ${(memory.stats?.avg_similarity || 0).toFixed(2)}
- Toplam Analiz: ${memory.stats?.total_documents || 0}

⚠️ <b>Risk Kelimeleri:</b>
${memory.stats?.risk_keywords?.join(', ') || 'Henüz yok'}

📂 <b>Kategoriler:</b>
${Object.entries(memory.categories || {}).map(([cat, stats]) => 
  `- ${cat}: ${stats.approved + stats.rejected} dosya`
).join('\n') || 'Henüz yok'}
    `;

    await ctx.answerCbQuery();
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('AI hafızası hatası:', error.message);
    await ctx.answerCbQuery('❌ Hafıza yüklenemedi');
  }
});

/**
 * /riskli komutu
 */
bot.command('riskli', async (ctx) => {
  try {
    await ctx.reply('🔍 Riskli dosyalar taranıyor...');

    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data.filter(d => d.status === 2); // Sadece bekleyenler

    if (documents.length === 0) {
      return ctx.reply('✅ Bekleyen dosya yok.');
    }

    // Analiz et
    const results = await analyzeBatch(documents, STORAGE_PATH);
    const riskyDocs = getRiskyDocuments(results);

    if (riskyDocs.length === 0) {
      return ctx.reply('✅ Riskli dosya bulunamadı.');
    }

    let message = `🚨 <b>Riskli Dosyalar (${riskyDocs.length}):</b>\n\n`;

    riskyDocs.slice(0, 10).forEach((result, index) => {
      const doc = result.document;
      const analysis = result.analysis;
      
      message += `${index + 1}. ${analysis.risk_emoji} <b>${doc.document_name || path.basename(doc.file_path)}</b>\n`;
      message += `   - Firma: ${doc.user?.company || doc.user?.name}\n`;
      message += `   - Benzerlik: %${analysis.similarity_percentage}\n`;
      message += `   - Risk: ${analysis.risk_level}\n\n`;
    });

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Riskli dosyalar hatası:', error.message);
    await ctx.reply('❌ Riskli dosyalar listelenemedi.');
  }
});

/**
 * /kategori komutu - Inline search kullanımı
 */
bot.command('kategori', async (ctx) => {
  const helpMessage = `
📁 <b>Kategori Analiz Raporu</b>

<b>Kullanım:</b>
• Inline: <code>@${bot.botInfo?.username || 'bot'} firma_adi</code> → "Kategori Raporu" seçin
• Direkt: <code>/kat_id [id]</code>
  Örnek: <code>/kat_id 17</code>

💡 Inline arama ile firmayı bulup kategori raporunu görüntüleyin!
  `;
  
  await ctx.replyWithHTML(helpMessage);
});

/**
 * /kat_id [id] - Direkt ID ile kategori raporu
 */
bot.command(/kat_id\s+(\d+)/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const report = generateCategoryReport(userId);
    
    if (Object.keys(report).length === 0) {
      return ctx.reply('❌ Bu firma için kategori verisi yok.');
    }

    let message = `📁 <b>Kategori Raporu - Firma ${userId}</b>\n\n`;

    Object.entries(report).forEach(([category, stats]) => {
      message += `<b>${category}</b>\n`;
      message += `✅ Onaylı: ${stats.approved}\n`;
      message += `❌ Reddedilen: ${stats.rejected}\n`;
      message += `⏳ Bekleyen: ${stats.pending}\n`;
      message += `📊 Onay Oranı: %${stats.approval_rate}\n\n`;
    });

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Kategori raporu hatası:', error.message);
    await ctx.reply('❌ Kategori raporu oluşturulamadı.');
  }
});

/**
 * Kategori raporu callback
 */
bot.action(/^cat_report_(\d+)$/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const report = generateCategoryReport(userId);
    
    if (Object.keys(report).length === 0) {
      await ctx.answerCbQuery();
      return ctx.reply('❌ Bu firma için kategori verisi yok.');
    }

    let message = `📁 <b>Kategori Raporu - Firma ${userId}</b>\n\n`;

    Object.entries(report).forEach(([category, stats]) => {
      message += `<b>${category}</b>\n`;
      message += `✅ Onaylı: ${stats.approved}\n`;
      message += `❌ Reddedilen: ${stats.rejected}\n`;
      message += `⏳ Bekleyen: ${stats.pending}\n`;
      message += `📊 Onay Oranı: %${stats.approval_rate}\n\n`;
    });

    await ctx.answerCbQuery();
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Kategori raporu hatası:', error.message);
    await ctx.answerCbQuery('❌ Rapor oluşturulamadı');
  }
});

/**
 * /rapor komutu - Günlük özet
 */
bot.command('rapor', async (ctx) => {
  try {
    await ctx.reply('📊 Günlük rapor hazırlanıyor...');

    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;

    const stats = {
      total: documents.length,
      approved: documents.filter(d => d.status === 1).length,
      rejected: documents.filter(d => d.status === 0).length,
      pending: documents.filter(d => d.status === 2).length
    };

    const firms = listAllFirms();
    const systemStats = getSystemStats();

    const message = `
📊 <b>Günlük Sistem Raporu</b>

📄 <b>Dokümanlar:</b>
- Toplam: ${stats.total}
- ✅ Onaylı: ${stats.approved}
- ❌ Reddedilen: ${stats.rejected}
- ⏳ Bekleyen: ${stats.pending}

🏢 <b>Firmalar:</b>
- Toplam Firma: ${firms.length}
- Aktif Hafıza: ${firms.length}

🤖 <b>AI İstatistikleri:</b>
- Analiz Edilen: ${systemStats.total_analyzed}
- Ortalama Benzerlik: ${(systemStats.average_similarity * 100).toFixed(1)}%
- Yaygın Riskler: ${systemStats.common_risks.slice(0, 5).join(', ')}

🕐 <b>Rapor Tarihi:</b> ${new Date().toLocaleString('tr-TR')}
    `;

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Rapor hatası:', error.message);
    await ctx.reply('❌ Rapor oluşturulamadı.');
  }
});

/**
 * /ai_durum komutu
 */
bot.command('ai_durum', async (ctx) => {
  try {
    const stats = getSystemStats();
    const firms = listAllFirms();

    const message = `
🤖 <b>AI Sistem Durumu</b>

📊 <b>Genel İstatistikler:</b>
- Toplam Analiz: ${stats.total_analyzed}
- Toplam Onay: ${stats.total_approved}
- Toplam Red: ${stats.total_rejected}
- Bekleyen: ${stats.total_pending}

🧠 <b>Öğrenme:</b>
- Ortalama Benzerlik: ${(stats.average_similarity * 100).toFixed(1)}%
- Aktif Firma Hafızası: ${firms.length}

⚠️ <b>Yaygın Riskler:</b>
${stats.common_risks.slice(0, 10).map((risk, i) => `${i + 1}. ${risk}`).join('\n') || 'Henüz risk kaydı yok'}

🕐 <b>Son Güncelleme:</b> ${stats.last_updated || 'Hiç güncellenmedi'}
    `;

    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('AI durum hatası:', error.message);
    await ctx.reply('❌ AI durumu alınamadı.');
  }
});

/**
 * /analiz_hepsi komutu - Tüm bekleyen dosyaları ARKA PLANDA analiz et
 */
bot.command('analiz_hepsi', async (ctx) => {
  try {
    await ctx.reply('🔍 Bekleyen dosyalar taranıyor...');

    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const pendingDocs = response.data.filter(d => 
      parseInt(d.status) === 2 || d.status === '2'
    );

    if (pendingDocs.length === 0) {
      return ctx.reply('✅ Bekleyen dosya yok.');
    }

    await ctx.reply(
      `📄 ${pendingDocs.length} bekleyen dosya bulundu.\n\n` +
      `✅ Analiz arka planda başlatıldı!\n` +
      `💡 Bot çalışmaya devam ediyor, diğer komutları kullanabilirsiniz.\n\n` +
      `📊 İlerleme bildirimleri gönderilecek.`
    );

    // ARKA PLANDA ÇALIŞTIR (bot bloke olmaz)
    analyzeInBackground(ctx.chat.id, pendingDocs);

  } catch (error) {
    console.error('Analiz hatası:', error.message);
    await ctx.reply('❌ Analiz başlatılamadı.');
  }
});

/**
 * Onay callback
 */
bot.action(/^approve_(\d+)_(\d+)$/, async (ctx) => {
  try {
    const docId = parseInt(ctx.match[1]);
    const userId = parseInt(ctx.match[2]);
    const key = `${docId}_${userId}`;

    const stored = pendingActions.get(key);
    if (!stored) {
      return ctx.answerCbQuery('❌ Doküman bilgisi bulunamadı');
    }

    // Laravel API'ye feedback gönder
    await axios.post(`${LARAVEL_API}/bot/feedback`, {
      file_path: stored.document.file_path,
      approved: true
    });

    // Hafızayı güncelle
    processApprovalFeedback(stored.document, stored.extractedText);

    // Cleanup
    pendingActions.delete(key);

    await ctx.answerCbQuery('✅ Dosya onaylandı');
    await ctx.editMessageReplyMarkup({ inline_keyboard: [] });
    await ctx.replyWithHTML(`✅ <b>Onaylandı:</b> ${stored.document.document_name || path.basename(stored.document.file_path)}`);

  } catch (error) {
    console.error('Onay hatası:', error.message);
    await ctx.answerCbQuery('❌ Onay işlemi başarısız');
  }
});

/**
 * Red callback
 */
bot.action(/^reject_(\d+)_(\d+)$/, async (ctx) => {
  try {
    const docId = parseInt(ctx.match[1]);
    const userId = parseInt(ctx.match[2]);
    const key = `${docId}_${userId}`;

    const stored = pendingActions.get(key);
    if (!stored) {
      return ctx.answerCbQuery('❌ Doküman bilgisi bulunamadı');
    }

    await ctx.answerCbQuery();
    await ctx.reply('❌ Dosya reddedildi. Red nedeni yazmak isterseniz mesaj gönderin (veya /skip yazın):');

    // Red nedeni bekle
    bot.once('text', async (textCtx) => {
      let rejectionNote = textCtx.message.text;
      
      if (rejectionNote === '/skip') {
        rejectionNote = 'Belirtilmemiş';
      }

      // Laravel API'ye feedback gönder
      await axios.post(`${LARAVEL_API}/bot/feedback`, {
        file_path: stored.document.file_path,
        approved: false,
        note: rejectionNote
      });

      // Hafızayı güncelle
      processRejectionFeedback(stored.document, stored.extractedText, rejectionNote);

      // Cleanup
      pendingActions.delete(key);

      await textCtx.replyWithHTML(`❌ <b>Reddedildi:</b> ${stored.document.document_name || path.basename(stored.document.file_path)}\n<b>Neden:</b> ${rejectionNote}`);
    });

  } catch (error) {
    console.error('Red hatası:', error.message);
    await ctx.answerCbQuery('❌ Red işlemi başarısız');
  }
});

/**
 * RAPOR KOMUTLARI
 */

/**
 * /dashboard - Genel sistem durumu
 */
bot.command('dashboard', async (ctx) => {
  try {
    await ctx.reply('📊 Dashboard hazırlanıyor...');
    
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    const totalApproved = firms.reduce((sum, f) => sum + f.total.approved, 0);
    const totalRejected = firms.reduce((sum, f) => sum + f.total.rejected, 0);
    const totalPending = firms.reduce((sum, f) => sum + f.total.pending, 0);
    const totalDocs = totalApproved + totalRejected + totalPending;
    
    // Riskli firmaları bul (red oranı > 20%)
    const riskyFirms = firms.filter(f => {
      const total = f.total.approved + f.total.rejected;
      return total > 0 && (f.total.rejected / total) > 0.2;
    });
    
    // Bekleyen dosya fazla olan firmalar
    const pendingFirms = firms.filter(f => f.total.pending >= 20);
    
    const message = `
📊 <b>SİSTEM DASHBOARD</b>

🏢 <b>Firmalar:</b> ${firms.length} aktif
📄 <b>Toplam Dosya:</b> ${totalDocs}

📈 <b>Durum Dağılımı:</b>
✅ Onaylı: ${totalApproved} (%${((totalApproved / totalDocs) * 100).toFixed(1)})
❌ Reddedilmiş: ${totalRejected} (%${((totalRejected / totalDocs) * 100).toFixed(1)})
⏳ Bekleyen: ${totalPending} (%${((totalPending / totalDocs) * 100).toFixed(1)})

⚠️ <b>Dikkat Gereken:</b>
- ${riskyFirms.length} firma: %20+ red oranı
- ${pendingFirms.length} firma: 20+ bekleyen dosya

💡 <b>Komutlar:</b>
• /firmalar_durum - Tüm firmalar listesi
• /bekleyen_firmalar - Bekleyen dosya olan firmalar
• /riskli_firmalar - Yüksek red oranı olan firmalar
• /kategori_performance - Kategori başarı raporu
    `;
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Dashboard hatası:', error.message);
    await ctx.reply('❌ Dashboard oluşturulamadı.');
  }
});

/**
 * /firma_stats [id] - Firma detaylı istatistik
 */
bot.command(/firma_stats\s+(\d+)/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const firmStats = await getAllFirmsStats();
    const firm = firmStats[userId];
    
    if (!firm) {
      return ctx.reply('❌ Firma bulunamadı.');
    }
    
    const total = firm.total.approved + firm.total.rejected + firm.total.pending;
    const approvalRate = (firm.total.approved + firm.total.rejected) > 0
      ? ((firm.total.approved / (firm.total.approved + firm.total.rejected)) * 100).toFixed(1)
      : 0;
    
    let message = `
📊 <b>${firm.company || firm.name} - Detaylı İstatistikler</b>

👤 <b>Firma ID:</b> ${userId}
📄 <b>Toplam Dosya:</b> ${total}
✅ <b>Onaylı:</b> ${firm.total.approved} (%${approvalRate})
❌ <b>Reddedilmiş:</b> ${firm.total.rejected}
⏳ <b>Bekleyen:</b> ${firm.total.pending}

📅 <b>Yıl Bazlı Dağılım:</b>
${Object.entries(firm.years).sort((a, b) => b[0].localeCompare(a[0])).map(([year, stats]) => {
  const yTotal = stats.approved + stats.rejected + stats.pending;
  const yRate = (stats.approved + stats.rejected) > 0 
    ? ((stats.approved / (stats.approved + stats.rejected)) * 100).toFixed(0)
    : 0;
  return `- <b>${year}:</b> ${yTotal} dosya | ✅${stats.approved} ❌${stats.rejected} ⏳${stats.pending} | %${yRate} onay`;
}).join('\n') || 'Veri yok'}

📁 <b>Kategori Bazlı:</b>
${Object.entries(firm.categories).slice(0, 10).map(([cat, stats]) => {
  const cTotal = stats.approved + stats.rejected;
  const cRate = cTotal > 0 ? ((stats.approved / cTotal) * 100).toFixed(0) : 0;
  return `- ${cat}: ✅${stats.approved} ❌${stats.rejected} ⏳${stats.pending} (%${cRate})`;
}).join('\n') || 'Veri yok'}

${Object.keys(firm.categories).length > 10 ? `\n... ve ${Object.keys(firm.categories).length - 10} kategori daha` : ''}
    `;
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Firma stats hatası:', error.message);
    await ctx.reply('❌ İstatistikler alınamadı.');
  }
});

/**
 * /firmalar_durum - Tüm firmalar özet (Pagination ile)
 */
bot.command('firmalar_durum', async (ctx) => {
  try {
    await ctx.reply('📊 Firma durumları hazırlanıyor...');
    
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    if (firms.length === 0) {
      return ctx.reply('❌ Firma bulunamadı.');
    }
    
    // Onay oranına göre sırala
    firms.sort((a, b) => {
      const aTotal = a.total.approved + a.total.rejected;
      const bTotal = b.total.approved + b.total.rejected;
      const aRate = aTotal > 0 ? (a.total.approved / aTotal) : 0;
      const bRate = bTotal > 0 ? (b.total.approved / bTotal) : 0;
      return bRate - aRate;
    });
    
    // Cache'e kaydet (pagination için)
    const cacheKey = `firmalar_durum_${ctx.chat.id}`;
    paginationCache.set(cacheKey, { data: firms, timestamp: Date.now() });
    
    // İlk sayfayı göster
    await showFirmalarDurumPage(ctx, firms, 1);
    
  } catch (error) {
    console.error('Firmalar durum hatası:', error.message);
    await ctx.reply('❌ Firma durumları alınamadı.');
  }
});

/**
 * Firmalar durum sayfası göster
 */
async function showFirmalarDurumPage(ctx, firms, page) {
  const PAGE_SIZE = 20;
  const totalPages = Math.ceil(firms.length / PAGE_SIZE);
  const startIndex = (page - 1) * PAGE_SIZE;
  const endIndex = startIndex + PAGE_SIZE;
  const pageFirms = firms.slice(startIndex, endIndex);
  
  let message = `📊 <b>Firmalar Durum (Sayfa ${page}/${totalPages})</b>\n`;
  message += `Toplam: ${firms.length} firma\n\n`;
  
  pageFirms.forEach((firm, index) => {
    const total = firm.total.approved + firm.total.rejected;
    const approvalRate = total > 0 ? ((firm.total.approved / total) * 100).toFixed(0) : 0;
    
    let emoji = '✅';
    if (approvalRate < 60) emoji = '🚨';
    else if (approvalRate < 80) emoji = '⚠️';
    
    const name = (firm.company || firm.name).substring(0, 22);
    message += `${startIndex + index + 1}. ${emoji} ${name} (${firm.id})\n`;
    message += `   %${approvalRate} | ✅${firm.total.approved} ❌${firm.total.rejected} ⏳${firm.total.pending}\n`;
  });
  
  message += `\n💡 <code>/firma_stats [id]</code>`;
  
  const keyboard = createPaginationButtons(page, totalPages, 'firmalar_durum');
  
  if (ctx.callbackQuery) {
    await ctx.editMessageText(message, { parse_mode: 'HTML', ...keyboard });
  } else {
    await ctx.replyWithHTML(message, keyboard);
  }
}

/**
 * /bekleyen_firmalar - Bekleyen dosyası olan firmalar (Pagination ile)
 */
bot.command('bekleyen_firmalar', async (ctx) => {
  try {
    await ctx.reply('⏳ Bekleyen dosyalar taranıyor...');
    
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    // Bekleyen dosyası olan firmalar
    const pendingFirms = firms
      .filter(f => f.total.pending > 0)
      .sort((a, b) => b.total.pending - a.total.pending);
    
    if (pendingFirms.length === 0) {
      return ctx.reply('✅ Hiçbir firmada bekleyen dosya yok!');
    }
    
    // Cache'e kaydet
    const cacheKey = `bekleyen_firmalar_${ctx.chat.id}`;
    paginationCache.set(cacheKey, { data: pendingFirms, timestamp: Date.now() });
    
    // İlk sayfayı göster
    await showBekleyenFirmalarPage(ctx, pendingFirms, 1);
    
  } catch (error) {
    console.error('Bekleyen firmalar hatası:', error.message);
    await ctx.reply('❌ Liste alınamadı.');
  }
});

/**
 * Bekleyen firmalar sayfası göster
 */
async function showBekleyenFirmalarPage(ctx, firms, page) {
  const PAGE_SIZE = 20;
  const totalPages = Math.ceil(firms.length / PAGE_SIZE);
  const startIndex = (page - 1) * PAGE_SIZE;
  const endIndex = startIndex + PAGE_SIZE;
  const pageFirms = firms.slice(startIndex, endIndex);
  
  let message = `⏳ <b>Bekleyen Firmalar (Sayfa ${page}/${totalPages})</b>\n`;
  message += `Toplam: ${firms.length} firma\n\n`;
  
  pageFirms.forEach((firm, index) => {
    const latestYear = Object.keys(firm.years).filter(y => y !== 'unknown').sort().reverse()[0];
    
    const name = (firm.company || firm.name).substring(0, 22);
    message += `${startIndex + index + 1}. <b>${name}</b> (${firm.id})\n`;
    message += `   ${firm.total.pending} bekleyen`;
    
    if (latestYear) {
      message += ` | ${latestYear}: ${firm.years[latestYear].pending}`;
    }
    message += `\n`;
  });
  
  message += `\n💡 <code>/firma_stats [id]</code>`;
  
  const keyboard = createPaginationButtons(page, totalPages, 'bekleyen_firmalar');
  
  if (ctx.callbackQuery) {
    await ctx.editMessageText(message, { parse_mode: 'HTML', ...keyboard });
  } else {
    await ctx.replyWithHTML(message, keyboard);
  }
}

/**
 * /riskli_firmalar - Yüksek red oranı olan firmalar
 */
bot.command('riskli_firmalar', async (ctx) => {
  try {
    await ctx.reply('🚨 Riskli firmalar taranıyor...');
    
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    // Red oranı > %15 olan firmalar
    const riskyFirms = firms
      .filter(f => {
        const total = f.total.approved + f.total.rejected;
        return total >= 5 && (f.total.rejected / total) > 0.15;
      })
      .sort((a, b) => {
        const aRate = a.total.rejected / (a.total.approved + a.total.rejected);
        const bRate = b.total.rejected / (b.total.approved + b.total.rejected);
        return bRate - aRate;
      });
    
    if (riskyFirms.length === 0) {
      return ctx.reply('✅ Yüksek red oranına sahip firma yok!');
    }
    
    let message = `🚨 <b>Dikkat Gereken Firmalar (${riskyFirms.length})</b>\n\n`;
    message += `Red oranı %15'in üzerinde olan firmalar:\n\n`;
    
    riskyFirms.slice(0, 30).forEach((firm, index) => {
      const total = firm.total.approved + firm.total.rejected;
      const rejectRate = ((firm.total.rejected / total) * 100).toFixed(1);
      
      message += `${index + 1}. 🚨 <b>${firm.company || firm.name}</b>\n`;
      message += `   Red Oranı: %${rejectRate} | ❌${firm.total.rejected}/${total} dosya\n`;
      message += `   <code>/firma_stats ${firm.id}</code>\n\n`;
    });
    
    if (riskyFirms.length > 30) {
      message += `\n💡 Toplam ${riskyFirms.length} riskli firma var. İlk 30 gösteriliyor.`;
    }
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Riskli firmalar hatası:', error.message);
    await ctx.reply('❌ Liste alınamadı.');
  }
});

/**
 * /yil_karsilastir [id] - Firma yıl bazlı karşılaştırma
 */
bot.command(/yil_karsilastir\s+(\d+)/, async (ctx) => {
  try {
    const userId = parseInt(ctx.match[1]);
    const firmStats = await getAllFirmsStats();
    const firm = firmStats[userId];
    
    if (!firm) {
      return ctx.reply('❌ Firma bulunamadı.');
    }
    
    const years = Object.keys(firm.years).filter(y => y !== 'unknown').sort().reverse();
    
    if (years.length === 0) {
      return ctx.reply('❌ Yıl verisi bulunamadı.');
    }
    
    let message = `📅 <b>${firm.company || firm.name} - Yıl Bazlı Karşılaştırma</b>\n\n`;
    
    years.forEach(year => {
      const stats = firm.years[year];
      const total = stats.approved + stats.rejected + stats.pending;
      const rate = (stats.approved + stats.rejected) > 0
        ? ((stats.approved / (stats.approved + stats.rejected)) * 100).toFixed(0)
        : 0;
      
      message += `<b>${year}:</b> ✅${stats.approved} ❌${stats.rejected} ⏳${stats.pending} | Toplam: ${total} | Onay: %${rate}\n`;
    });
    
    // Trend analizi
    if (years.length >= 2) {
      const latest = firm.years[years[0]];
      const previous = firm.years[years[1]];
      
      const latestRate = (latest.approved + latest.rejected) > 0
        ? (latest.approved / (latest.approved + latest.rejected))
        : 0;
      const prevRate = (previous.approved + previous.rejected) > 0
        ? (previous.approved / (previous.approved + previous.rejected))
        : 0;
      
      message += `\n📈 <b>Trend:</b>\n`;
      
      if (latestRate > prevRate) {
        message += `✅ Onay oranı artıyor (+%${((latestRate - prevRate) * 100).toFixed(1)})\n`;
      } else if (latestRate < prevRate) {
        message += `⚠️ Onay oranı düşüyor (-%${((prevRate - latestRate) * 100).toFixed(1)})\n`;
      } else {
        message += `➡️ Onay oranı stabil\n`;
      }
    }
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Yıl karşılaştırma hatası:', error.message);
    await ctx.reply('❌ Karşılaştırma yapılamadı.');
  }
});

/**
 * /kategori_performance - Kategori başarı oranları
 */
bot.command('kategori_performance', async (ctx) => {
  try {
    await ctx.reply('📁 Kategori performansı hazırlanıyor...');
    
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;
    
    const categoryStats = {};
    
    documents.forEach(doc => {
      const catName = doc.category?.name || 'Diğer';
      const statusInt = parseInt(doc.status);
      
      if (!categoryStats[catName]) {
        categoryStats[catName] = { approved: 0, rejected: 0, pending: 0 };
      }
      
      if (statusInt === 1 || doc.status === '1') {
        categoryStats[catName].approved++;
      } else if (statusInt === 0 || doc.status === '0') {
        categoryStats[catName].rejected++;
      } else if (statusInt === 2 || doc.status === '2') {
        categoryStats[catName].pending++;
      }
    });
    
    // Onay oranına göre sırala
    const categories = Object.entries(categoryStats)
      .map(([name, stats]) => ({
        name,
        ...stats,
        total: stats.approved + stats.rejected + stats.pending,
        rate: (stats.approved + stats.rejected) > 0
          ? ((stats.approved / (stats.approved + stats.rejected)) * 100).toFixed(1)
          : 0
      }))
      .sort((a, b) => parseFloat(b.rate) - parseFloat(a.rate));
    
    let message = `📁 <b>Kategori Performans Raporu</b>\n\n`;
    message += `Toplam ${categories.length} kategori\n\n`;
    
    categories.forEach((cat, index) => {
      let emoji = '✅';
      if (cat.rate < 60) emoji = '🚨';
      else if (cat.rate < 80) emoji = '⚠️';
      
      message += `${index + 1}. ${emoji} <b>${cat.name}</b>\n`;
      message += `   ${cat.total} dosya | Onay: %${cat.rate}\n`;
      message += `   ✅${cat.approved} ❌${cat.rejected} ⏳${cat.pending}\n\n`;
    });
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Kategori performance hatası:', error.message);
    await ctx.reply('❌ Rapor oluşturulamadı.');
  }
});

/**
 * /top10_basarili - En başarılı firmalar
 */
bot.command('top10_basarili', async (ctx) => {
  try {
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    const successfulFirms = firms
      .filter(f => (f.total.approved + f.total.rejected) >= 10)
      .map(f => ({
        ...f,
        rate: (f.total.approved / (f.total.approved + f.total.rejected)) * 100
      }))
      .sort((a, b) => b.rate - a.rate)
      .slice(0, 10);
    
    if (successfulFirms.length === 0) {
      return ctx.reply('❌ Yeterli veri yok.');
    }
    
    let message = `🏆 <b>En Başarılı 10 Firma</b>\n\n`;
    
    successfulFirms.forEach((firm, index) => {
      const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `${index + 1}.`;
      
      message += `${medal} <b>${firm.company || firm.name}</b>\n`;
      message += `   Onay Oranı: %${firm.rate.toFixed(1)}\n`;
      message += `   ✅${firm.total.approved} ❌${firm.total.rejected} | <code>/firma_stats ${firm.id}</code>\n\n`;
    });
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Top10 başarılı hatası:', error.message);
    await ctx.reply('❌ Liste alınamadı.');
  }
});

/**
 * /top10_riskli - En riskli firmalar
 */
bot.command('top10_riskli', async (ctx) => {
  try {
    const firmStats = await getAllFirmsStats();
    const firms = Object.values(firmStats);
    
    const riskyFirms = firms
      .filter(f => (f.total.approved + f.total.rejected) >= 5)
      .map(f => ({
        ...f,
        rejectRate: (f.total.rejected / (f.total.approved + f.total.rejected)) * 100
      }))
      .sort((a, b) => b.rejectRate - a.rejectRate)
      .slice(0, 10);
    
    if (riskyFirms.length === 0) {
      return ctx.reply('❌ Yeterli veri yok.');
    }
    
    let message = `🚨 <b>Dikkat Gereken 10 Firma</b>\n\n`;
    
    riskyFirms.forEach((firm, index) => {
      message += `${index + 1}. 🚨 <b>${firm.company || firm.name}</b>\n`;
      message += `   Red Oranı: %${firm.rejectRate.toFixed(1)}\n`;
      message += `   ✅${firm.total.approved} ❌${firm.total.rejected} ⏳${firm.total.pending}\n`;
      message += `   <code>/firma_stats ${firm.id}</code>\n\n`;
    });
    
    await ctx.replyWithHTML(message);
  } catch (error) {
    console.error('Top10 riskli hatası:', error.message);
    await ctx.reply('❌ Liste alınamadı.');
  }
});

/**
 * PAGINATION CALLBACK HANDLER
 */
bot.action(/^page_firmalar_durum_(\d+)$/, async (ctx) => {
  try {
    const page = parseInt(ctx.match[1]);
    const cacheKey = `firmalar_durum_${ctx.chat.id}`;
    const cached = paginationCache.get(cacheKey);
    
    if (!cached) {
      await ctx.answerCbQuery('❌ Veri bulunamadı. Lütfen komutu yeniden çalıştırın.');
      return;
    }
    
    await ctx.answerCbQuery();
    await showFirmalarDurumPage(ctx, cached.data, page);
    
  } catch (error) {
    console.error('Pagination hatası:', error.message);
    await ctx.answerCbQuery('❌ Sayfa yüklenemedi');
  }
});

bot.action(/^page_bekleyen_firmalar_(\d+)$/, async (ctx) => {
  try {
    const page = parseInt(ctx.match[1]);
    const cacheKey = `bekleyen_firmalar_${ctx.chat.id}`;
    const cached = paginationCache.get(cacheKey);
    
    if (!cached) {
      await ctx.answerCbQuery('❌ Veri bulunamadı. Lütfen komutu yeniden çalıştırın.');
      return;
    }
    
    await ctx.answerCbQuery();
    await showBekleyenFirmalarPage(ctx, cached.data, page);
    
  } catch (error) {
    console.error('Pagination hatası:', error.message);
    await ctx.answerCbQuery('❌ Sayfa yüklenemedi');
  }
});

/**
 * INLINE QUERY - Firma Arama (Düzeltilmiş)
 * Kullanım: @bot_name firma_adi
 */
bot.on('inline_query', async (ctx) => {
  try {
    const query = ctx.inlineQuery.query.toLowerCase().trim();
    
    // Minimum 2 karakter
    if (query.length < 2) {
      return ctx.answerInlineQuery([{
        type: 'article',
        id: 'help',
        title: '🔍 Firma aramak için en az 2 karakter yazın',
        description: 'Örnek: ABC veya Ahmet',
        input_message_content: {
          message_text: '💡 Firma adını yazarak arama yapabilirsiniz.'
        }
      }]);
    }
    
    // API'den firmaları al
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;
    const users = [...new Set(documents.map(d => d.user))].filter(u => u);
    
    // Arama yap
    const matches = users.filter(u => 
      (u.company?.toLowerCase().includes(query)) ||
      (u.name?.toLowerCase().includes(query)) ||
      (u.id?.toString().includes(query))
    );
    
    if (matches.length === 0) {
      return ctx.answerInlineQuery([{
        type: 'article',
        id: 'no_result',
        title: '❌ Firma bulunamadı',
        description: `"${query}" için sonuç yok`,
        input_message_content: {
          message_text: `❌ "${query}" araması için firma bulunamadı.`
        }
      }]);
    }
    
    // Telegram inline query limiti: max 50 sonuç
    // Unique ID için firma başına tek sonuç (en detaylı: firma_stats)
    const results = matches.slice(0, 50).map(user => ({
      type: 'article',
      id: `firm_${user.id}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`, // Unique ID
      title: `📊 ${user.company || user.name}`,
      description: `ID: ${user.id} | Detaylı rapor`,
      input_message_content: {
        message_text: `/firma_stats ${user.id}`
      }
    }));
    
    await ctx.answerInlineQuery(results, {
      cache_time: 30, // 30 saniye cache
      is_personal: true
    });
    
  } catch (error) {
    console.error('Inline query hatası:', error.message);
    
    // Hata durumunda boş sonuç döndür
    await ctx.answerInlineQuery([{
      type: 'article',
      id: 'error',
      title: '❌ Bir hata oluştu',
      description: 'Lütfen tekrar deneyin',
      input_message_content: {
        message_text: '❌ Arama sırasında bir hata oluştu.'
      }
    }]);
  }
});

/**
 * YARDIMCI FONKSİYONLAR - Rapor Sistemi
 */

/**
 * Pagination helper - Sayfa butonları oluştur
 */
function createPaginationButtons(currentPage, totalPages, command) {
  const buttons = [];
  const row1 = [];
  const row2 = [];
  
  // Önceki butonu
  if (currentPage > 1) {
    row1.push(Markup.button.callback('⬅️ Önceki', `page_${command}_${currentPage - 1}`));
  }
  
  // Sayfa numaraları (max 5 sayfa göster)
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);
  
  if (endPage - startPage < 4) {
    startPage = Math.max(1, endPage - 4);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const label = i === currentPage ? `• ${i} •` : `${i}`;
    row2.push(Markup.button.callback(label, `page_${command}_${i}`));
  }
  
  // Sonraki butonu
  if (currentPage < totalPages) {
    row1.push(Markup.button.callback('Sonraki ➡️', `page_${command}_${currentPage + 1}`));
  }
  
  if (row1.length > 0) buttons.push(row1);
  if (row2.length > 0) buttons.push(row2);
  
  return Markup.inlineKeyboard(buttons);
}

/**
 * Tüm firmaların detaylı istatistiklerini al
 */
async function getAllFirmsStats() {
  try {
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;
    
    const firmStats = {};
    
    documents.forEach(doc => {
      if (!doc.user || !doc.user_id) return;
      
      const userId = doc.user_id;
      const statusInt = parseInt(doc.status);
      // Yıl yoksa created_at'tan al, o da yoksa şu anki yıl
      const year = doc.file_year || 
                   (doc.created_at ? new Date(doc.created_at).getFullYear().toString() : null) ||
                   new Date().getFullYear().toString();
      
      if (!firmStats[userId]) {
        firmStats[userId] = {
          id: userId,
          name: doc.user.name,
          company: doc.user.company,
          years: {},
          total: { approved: 0, rejected: 0, pending: 0 },
          categories: {}
        };
      }
      
      // Yıl bazlı
      if (!firmStats[userId].years[year]) {
        firmStats[userId].years[year] = { approved: 0, rejected: 0, pending: 0 };
      }
      
      // Status bazlı sayma
      if (statusInt === 1 || doc.status === '1') {
        firmStats[userId].total.approved++;
        firmStats[userId].years[year].approved++;
      } else if (statusInt === 0 || doc.status === '0') {
        firmStats[userId].total.rejected++;
        firmStats[userId].years[year].rejected++;
      } else if (statusInt === 2 || doc.status === '2') {
        firmStats[userId].total.pending++;
        firmStats[userId].years[year].pending++;
      }
      
      // Kategori bazlı
      const catName = doc.category?.name || 'Diğer';
      if (!firmStats[userId].categories[catName]) {
        firmStats[userId].categories[catName] = { approved: 0, rejected: 0, pending: 0 };
      }
      
      if (statusInt === 1 || doc.status === '1') {
        firmStats[userId].categories[catName].approved++;
      } else if (statusInt === 0 || doc.status === '0') {
        firmStats[userId].categories[catName].rejected++;
      } else if (statusInt === 2 || doc.status === '2') {
        firmStats[userId].categories[catName].pending++;
      }
    });
    
    return firmStats;
  } catch (error) {
    console.error('Firma istatistikleri alma hatası:', error.message);
    return {};
  }
}

/**
 * Hata yakalama
 */
bot.catch((err, ctx) => {
  console.error('❌ Bot hatası:', err);
  ctx.reply('❌ Bir hata oluştu. Lütfen tekrar deneyin.');
});

/**
 * Arka planda analiz yap (bot bloke olmaz)
 */
async function analyzeInBackground(chatId, documents) {
  const BATCH_SIZE = 10; // 10'ar dosya batch
  const totalBatches = Math.ceil(documents.length / BATCH_SIZE);
  
  console.log(`🔄 Arka plan analiz başladı: ${documents.length} dosya, ${totalBatches} batch`);
  
  try {
    let processedCount = 0;
    
    for (let i = 0; i < documents.length; i += BATCH_SIZE) {
      const batch = documents.slice(i, i + BATCH_SIZE);
      const batchNum = Math.floor(i / BATCH_SIZE) + 1;
      
      console.log(`📊 Batch ${batchNum}/${totalBatches} analiz ediliyor...`);
      
      // Batch analiz
      const results = await analyzeBatch(batch, STORAGE_PATH);
      
      // Admin'lere gönder
      for (const result of results) {
        if (!result.success) {
          console.warn(`⚠️ Analiz başarısız: ${result.document.file_path}`);
          continue;
        }
        
        await sendAnalysisToAdmins(result);
        processedCount++;
        
        // Rate limiting (Telegram flood kontrolü)
        await new Promise(r => setTimeout(r, 600));
      }
      
      // Progress bildirimi (her 5 batch'te bir veya son batch)
      if (batchNum % 5 === 0 || batchNum === totalBatches) {
        try {
          const percentage = ((processedCount / documents.length) * 100).toFixed(0);
          await bot.telegram.sendMessage(chatId, 
            `📊 <b>İlerleme:</b> ${batchNum}/${totalBatches} batch tamamlandı\n` +
            `📄 ${processedCount}/${documents.length} dosya işlendi (%${percentage})`,
            { parse_mode: 'HTML' }
          );
        } catch (e) {
          console.error('Progress mesajı gönderilemedi:', e.message);
        }
      }
      
      // Event loop'a dön (bot responsive kalsın)
      await new Promise(resolve => setImmediate(resolve));
      
      // CPU'ya mola (her batch sonrası)
      await new Promise(r => setTimeout(r, 200));
    }
    
    // Tamamlama mesajı
    try {
      await bot.telegram.sendMessage(chatId, 
        `✅ <b>Analiz Tamamlandı!</b>\n\n` +
        `📄 ${processedCount}/${documents.length} dosya başarıyla işlendi.\n` +
        `⏱️ Toplam süre: ~${Math.ceil((Date.now() - documents[0]?.startTime || 0) / 1000 / 60)} dakika\n\n` +
        `💡 Sonuçları yukarıda görebilirsiniz.`,
        { parse_mode: 'HTML' }
      );
    } catch (e) {
      console.error('Tamamlama mesajı gönderilemedi:', e.message);
    }
    
    console.log(`✅ Arka plan analiz tamamlandı: ${processedCount} dosya`);
    
  } catch (error) {
    console.error('❌ Arka plan analiz hatası:', error.message);
    
    try {
      await bot.telegram.sendMessage(chatId, 
        `❌ Analiz sırasında hata oluştu:\n${error.message}`,
        { parse_mode: 'HTML' }
      );
    } catch (e) {
      console.error('Hata mesajı gönderilemedi:', e.message);
    }
  }
}

/**
 * Analiz sonucunu admin'lere gönder
 */
async function sendAnalysisToAdmins(result) {
  const doc = result.document;
  const analysis = result.analysis;
  
  const message = `
${analysis.risk_emoji} <b>YENİ DOSYA ANALİZİ</b>

🏢 <b>Firma:</b> ${doc.user?.company || doc.user?.name} (ID: ${doc.user_id})
📁 <b>Kategori:</b> ${doc.category?.name || 'Belirtilmemiş'}
📄 <b>Dosya:</b> ${doc.document_name || path.basename(doc.file_path)}
📅 <b>Yıl:</b> ${doc.file_year || 'Belirtilmemiş'}

📊 <b>Benzerlik:</b> %${analysis.similarity_percentage} ${analysis.risk_emoji}
⚠️ <b>Risk Seviyesi:</b> ${analysis.risk_level}

💬 <b>AI Yorumu:</b>
${analysis.comments.join('\n')}

📌 <b>Öneriler:</b>
${analysis.recommendations.join('\n')}

${analysis.risk_patterns_found.length > 0 ? `🚨 <b>Risk İfadeleri:</b> ${analysis.risk_patterns_found.join(', ')}` : ''}
  `;

  const keyboard = Markup.inlineKeyboard([
    [
      Markup.button.callback('✅ Onayla', `approve_${doc.id}_${doc.user_id}`),
      Markup.button.callback('❌ Reddet', `reject_${doc.id}_${doc.user_id}`)
    ]
  ]);

  // Store for feedback
  pendingActions.set(`${doc.id}_${doc.user_id}`, {
    document: doc,
    extractedText: result.extractedText
  });

  // Tüm admin'lere gönder
  for (const adminId of ALLOWED_IDS) {
    try {
      await bot.telegram.sendMessage(adminId, message, {
        parse_mode: 'HTML',
        ...keyboard
      });
      console.log(`📤 Bildirim gönderildi: Admin ${adminId}`);
    } catch (error) {
      console.error(`Admin ${adminId}'ye mesaj gönderilemedi:`, error.message);
    }
  }
}

/**
 * Periyodik olarak yeni bekleyen dosyaları kontrol et ve analiz et
 */
async function checkForNewDocuments() {
  try {
    console.log('\n🔍 Yeni bekleyen dosyalar kontrol ediliyor...');
    
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const pendingDocs = response.data.filter(d => 
      parseInt(d.status) === 2 || d.status === '2'
    );
    
    if (pendingDocs.length === 0) {
      console.log('✅ Yeni bekleyen dosya yok.\n');
      return;
    }
    
    console.log(`📄 ${pendingDocs.length} bekleyen dosya bulundu.`);
    
    // Analiz edilmemiş dosyaları filtrele (pendingActions'da olmayanlar)
    const newDocs = pendingDocs.filter(doc => 
      !pendingActions.has(`${doc.id}_${doc.user_id}`)
    );
    
    if (newDocs.length === 0) {
      console.log('ℹ️ Tüm bekleyen dosyalar zaten analiz edilmiş.\n');
      return;
    }
    
    console.log(`🆕 ${newDocs.length} yeni dosya analiz ediliyor...`);
    
    // Analiz et
    const results = await analyzeBatch(newDocs, STORAGE_PATH);
    
    // Admin'lere bildirim gönder
    for (const result of results) {
      if (!result.success) {
        console.warn(`⚠️ Analiz başarısız: ${result.document.file_path}`);
        continue;
      }
      
      await sendAnalysisToAdmins(result);
      
      // Rate limiting
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
    
    console.log(`✅ ${newDocs.length} yeni dosya analiz edildi ve bildirildi.\n`);
    
  } catch (error) {
    console.error('❌ Periyodik kontrol hatası:', error.message);
  }
}

/**
 * Başlangıçta tüm mevcut dosyaları tara ve hafızayı doldur
 * SADECE ilk çalıştırmada veya --init parametresiyle
 */
async function initializeMemory() {
  
  // Hafıza dosyaları var mı kontrol et
  const globalMemory = loadGlobalMemory();
  
  // Eğer daha önce tarama yapılmışsa ve --init parametresi yoksa atla
  if (globalMemory.last_updated && !process.argv.includes('--init')) {
    const lastUpdate = new Date(globalMemory.last_updated);
    const now = new Date();
    const hoursDiff = (now - lastUpdate) / (1000 * 60 * 60);
    
    console.log(`ℹ️  Hafıza zaten mevcut (Son güncelleme: ${lastUpdate.toLocaleString('tr-TR')} - ${hoursDiff.toFixed(1)} saat önce)`);
    console.log(`📊 İstatistikler: ✅ ${globalMemory.total_approved} | ❌ ${globalMemory.total_rejected} | ⏳ ${globalMemory.total_pending}`);
    console.log('💡 Tüm dosyaları yeniden taramak için: node bot.js --init');
    console.log('');
    return;
  }
  
  console.log('🔍 Mevcut dosyalar taranıyor ve hafıza dolduruluyor...');
  console.log('⏳ Bu işlem 5-10 dakika sürebilir, lütfen bekleyin...\n');
  
  try {
    const response = await axios.get(`${LARAVEL_API}/documents/all`);
    const documents = response.data;
    
    if (!documents || documents.length === 0) {
      console.log('⚠️ Sistemde henüz doküman yok.');
      return;
    }
    
    console.log(`📄 Toplam ${documents.length} doküman bulundu.`);
    
    let approvedCount = 0;
    let rejectedCount = 0;
    let pendingCount = 0;
    let skippedCount = 0;
    
    // Her dokümanı işle
    for (const doc of documents) {
      if (!doc.user_id) {
        skippedCount++;
        continue;
      }
      
      try {
        const filePath = path.join(STORAGE_PATH, doc.file_path);
        
        // Dosya var mı kontrol et
        if (!fs.existsSync(filePath)) {
          console.warn(`⚠️ Dosya bulunamadı: ${doc.file_path}`);
          skippedCount++;
          continue;
        }
        
        // Metin çıkar
        const extractedText = await extractText(filePath);
        
        if (!extractedText || extractedText.length < 10) {
          console.warn(`⚠️ Metin çıkarılamadı: ${doc.file_path}`);
          skippedCount++;
          continue;
        }
        
        // Status'e göre işle (string veya integer olabilir)
        const statusInt = parseInt(doc.status);
        
        if (statusInt === 1 || doc.status === '1') {
          // ONAYLANMIŞ - Hafızaya kaydet
          processApprovalFeedback(doc, extractedText);
          approvedCount++;
          console.log(`✅ Onaylı: ${doc.document_name || path.basename(doc.file_path)} (User ${doc.user_id})`);
          
        } else if (statusInt === 0 || doc.status === '0') {
          // REDDEDİLMİŞ - Hafızaya kaydet
          processRejectionFeedback(doc, extractedText, doc.rejection_note || 'Belirtilmemiş');
          rejectedCount++;
          console.log(`❌ Reddedilmiş: ${doc.document_name || path.basename(doc.file_path)} (User ${doc.user_id})`);
          
        } else if (statusInt === 2 || doc.status === '2') {
          // BEKLEMEDE - Sadece say
          pendingCount++;
        }
        
        // CPU'ya mola ver (her 5 dosyada bir)
        if ((approvedCount + rejectedCount) % 5 === 0) {
          await new Promise(resolve => setTimeout(resolve, 50));
        }
        
      } catch (error) {
        console.error(`Dosya işleme hatası [${doc.id}]:`, error.message);
        skippedCount++;
      }
    }
    
    console.log('\n📊 Hafıza Başlatma Tamamlandı:');
    console.log(`   ✅ Onaylı: ${approvedCount}`);
    console.log(`   ❌ Reddedilmiş: ${rejectedCount}`);
    console.log(`   ⏳ Bekleyen: ${pendingCount}`);
    console.log(`   ⚠️ Atlanan: ${skippedCount}`);
    console.log('');
    
    // Global hafızayı güncelle
    const globalMemory = loadGlobalMemory();
    globalMemory.total_approved = approvedCount;
    globalMemory.total_rejected = rejectedCount;
    globalMemory.total_pending = pendingCount;
    globalMemory.total_documents_analyzed = approvedCount + rejectedCount;
    saveGlobalMemory(globalMemory);
    
  } catch (error) {
    console.error('❌ Hafıza başlatma hatası:', error.message);
    console.log('⚠️ Hafıza boş olarak başlatılacak.');
  }
}

/**
 * Bot başlat
 */
console.log('🤖 IdeaDocs AI Bot başlatılıyor...');

// Önce hafızayı doldur, sonra bot'u başlat
(async () => {
  try {
    // 1. Hafıza başlatma (mevcut dosyaları öğren)
    await initializeMemory();
    
    // 2. Bot'u başlat
    await bot.launch();
    console.log('✅ Bot başarıyla başlatıldı!');
    console.log(`📡 Dinleniyor: @${bot.botInfo.username}`);
    console.log('');
    console.log('💡 Kullanım: Telegram\'dan /start komutu ile başlayın');
    console.log('🔍 Inline arama: @' + bot.botInfo.username + ' firma_adi');
    console.log('');
    
    // 3. Periyodik kontrol başlat (her 5 dakika)
    const CHECK_INTERVAL = parseInt(process.env.CHECK_INTERVAL_MINUTES || '5') * 60 * 1000;
    console.log(`⏰ Otomatik kontrol aktif: Her ${process.env.CHECK_INTERVAL_MINUTES || '5'} dakikada bir`);
    console.log('');
    
    // İlk kontrolü 30 saniye sonra başlat (bot tamamen hazır olsun)
    setTimeout(async () => {
      await checkForNewDocuments();
      
      // Sonra periyodik kontrol başlat
      setInterval(checkForNewDocuments, CHECK_INTERVAL);
    }, 30000);
    
  } catch (err) {
    console.error('❌ Bot başlatma hatası:', err);
    process.exit(1);
  }
})();

// Graceful stop
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));

