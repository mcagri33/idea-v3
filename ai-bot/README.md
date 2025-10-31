# 🤖 IdeaDocs AI Denetim Asistanı

**Tamamen local, CPU dostu, firma bazlı öğrenen doküman analiz sistemi**

## 📋 Özellikler

### 🧠 3 Katmanlı Öğrenme Sistemi

1. **🏢 Firma Hafızası** - Her firma için ayrı öğrenme
2. **🗂️ Kategori Hafızası** - Kategori bazlı anahtar kelimeler ve risk patterns
3. **🌍 Global Hafıza** - Tüm firmalardan türetilen genel profil

### ⚡ Temel Özellikler

- ✅ Tamamen **local** çalışır (dış API yok)
- ✅ **CPU dostu** algoritmalar (TF-IDF, Cosine Similarity)
- ✅ **PDF, XLSX, DOCX** dosya desteği
- ✅ **Telegram bot** entegrasyonu
- ✅ **Otomatik risk skoru** hesaplama
- ✅ **Sürekli öğrenme** (onay/red feedback)
- ✅ **Firma bazlı analiz** ve hafıza
- ✅ **Kategori destekli** akıllı analiz

---

## 🚀 Kurulum

### 1. Gereksinimler

- **Node.js** >= 18.0.0
- **Laravel** API erişimi
- **Telegram Bot Token**

### 2. Bağımlılıkları Yükleyin

```bash
cd ai-bot
npm install
```

### 3. Konfigürasyon

`.env` dosyası oluşturun:

```bash
# Telegram Bot Configuration
TG_BOT_TOKEN=123456789:ABCDEF_your_telegram_bot_token_here
ALLOWED_TELEGRAM_IDS=11111111,22222222

# Laravel API Configuration
LARAVEL_API_URL=http://127.0.0.1:8000/api

# Storage Paths
STORAGE_PATH=../public/storage

# Bot Configuration
BOT_WEBHOOK_PORT=3002
NODE_ENV=development
```

### 4. Telegram Bot Oluşturma

1. Telegram'da [@BotFather](https://t.me/BotFather)'a gidin
2. `/newbot` komutu ile bot oluşturun
3. Bot token'ı kopyalayın ve `.env` dosyasına ekleyin
4. `/mybots` → Bot seçin → **Edit Bot** → **Edit Commands**
5. Komutları ekleyin (aşağıdaki listeyi kopyalayın):

```
start - Sistemi başlat
firma - Firma bazlı raporları görüntüle
ai_firma - Firmanın AI hafızasını görüntüle
riskli - Riskli dosyaları listele
kategori - Kategori analiz raporu
rapor - Günlük özet raporu al
ai_durum - Genel AI durumu
analiz_hepsi - Tüm bekleyen dosyaları analiz et
```

### 5. Laravel API Endpoint'lerini Ekleyin

`routes/api.php` dosyasına eklenmiş olmalı:

```php
// AI Bot API Endpoints
Route::get('/documents/all', function () {
    return Document::with(['category:id,name,slug','user:id,name,company,uuid'])
        ->select('id','user_id','category_id','file_path','status','rejection_note','document_name','file_year','created_at')
        ->get();
});

Route::post('/bot/feedback', function (Request $request) {
    $request->validate([
        'file_path' => 'required',
        'approved' => 'required|boolean',
        'note' => 'nullable|string'
    ]);

    $doc = Document::where('file_path', $request->file_path)->first();
    
    if ($doc) {
        $doc->status = $request->approved ? 1 : 0;
        $doc->rejection_note = $request->note ?? null;
        $doc->save();

        return response()->json(['success' => true, 'message' => 'Feedback kaydedildi.']);
    }

    return response()->json(['success' => false, 'message' => 'Doküman bulunamadı.'], 404);
});
```

### 6. Botu Başlatın

```bash
npm start
```

Veya development modunda:

```bash
npm run dev
```

---

## 📱 Telegram Komutları

| Komut | Açıklama |
|-------|----------|
| `/start` | Botu başlatır ve kullanıcıyı tanımlar |
| `/firma` | Firma listesini gösterir, seçilince rapor sunar |
| `/ai_firma` | Firma listesinden seçilerek AI hafızası görüntülenir |
| `/riskli` | Tüm riskli dosyaları listeler |
| `/kategori` | Firma seçerek kategori bazlı analiz görüntüler |
| `/rapor` | Günlük sistem özet raporu |
| `/ai_durum` | Genel AI istatistikleri |
| `/analiz_hepsi` | Tüm bekleyen dosyaları yeniden analiz eder |

---

## 🧠 AI Nasıl Çalışır?

### 1. Öğrenme Süreci

```
[Dosya Yüklendi] → [Status: Beklemede (2)]
         ↓
[AI Analizi] → Firma hafızası + Kategori bilgisi + Global hafıza
         ↓
[Risk Skoru + Yorum] → Telegram'a bildirim
         ↓
[Admin Onay/Red] → Hafıza Güncellendi
         ↓
[Sistem Öğrendi] → Bir sonraki analiz daha iyi
```

### 2. Analiz Kriterleri

#### 📊 Benzerlik Skoru (0-100%)
- Firma onaylı dosyalarıyla **TF-IDF + Cosine Similarity**
- Yüksek benzerlik = Düşük risk
- Düşük benzerlik = Yüksek risk

#### ⚠️ Risk Pattern Kontrolü
- Firma geçmişinde **reddedilen dosya nedenleri**
- Kategori bazlı **risk ifadeleri**
- Örnek: "eksik satır", "tarih farkı", "format hatası"

#### 🔑 Kategori Anahtar Kelime Desteği
- Her kategorinin beklenen **anahtar kelimeleri**
- Örnek: Mizan → "borç", "alacak", "bilanço"
- Eksik anahtar kelime = Risk artışı

### 3. Hafıza Yapısı

#### Firma Hafızası (`memory/firms/{user_id}.json`)

```json
{
  "userId": 17,
  "approved": {
    "Mizan_ABC_2023.xlsx": "borç alacak dengesi bakiye..."
  },
  "rejected": {
    "Mizan_DEF_2022.xlsx": {
      "text": "eksik satır yanlış tarih...",
      "reason": "Tarih farkı ve eksik satırlar"
    }
  },
  "stats": {
    "total_documents": 10,
    "total_approved": 8,
    "total_rejected": 2,
    "avg_similarity": 0.78,
    "risk_keywords": ["tarih farkı", "eksik satır"]
  },
  "categories": {
    "Mizan": {
      "approved": 5,
      "rejected": 1,
      "pending": 0
    }
  }
}
```

#### Kategori Hafızası (`memory/categories.json`)

```json
{
  "Mizan": {
    "keywords": ["borç", "alacak", "bilanço", "denge"],
    "risk_patterns": ["eksik satır", "tarih hatası", "tutarsız toplam"],
    "weight": 1.0
  }
}
```

#### Global Hafıza (`memory/global.json`)

```json
{
  "average_similarity": 0.74,
  "total_documents_analyzed": 150,
  "total_approved": 120,
  "total_rejected": 30,
  "most_common_risks": ["tarih farkı", "eksik satır", "format hatası"]
}
```

---

## 📊 Telegram Bildirim Örneği

```
⚠️ Dosya Analizi

🏢 Firma: ABC Ltd (ID: 17)
📁 Kategori: Mizan
📄 Dosya: Mizan_ABC_2024.xlsx
📅 Yıl: 2024

📊 Benzerlik: %78 ⚠️
⚠️ Risk Seviyesi: Orta

💬 AI Yorumu:
- Bu dosya, firmanın "Mizan_ABC_2023.xlsx" dosyasına %85 benzerlik gösteriyor.
- Firma geçmişinde benzer onaylı dosyalar mevcut.
✅ Kategori anahtar kelimeleri bulundu: borç, alacak, denge

📌 Öneriler:
- Manuel kontrol önerilir.

[✅ Onayla] [❌ Reddet]
```

---

## 🛠️ Teknik Detaylar

### Kullanılan Algoritmalar

1. **TF-IDF (Term Frequency-Inverse Document Frequency)**
   - Doküman içindeki kelimelerin önem ağırlığını hesaplar
   - Nadir kelimeler daha yüksek ağırlık alır

2. **Cosine Similarity**
   - İki doküman vektörü arasındaki açıyı hesaplar
   - 0 = Tamamen farklı, 1 = Tamamen aynı

3. **Jaccard Similarity** (yardımcı)
   - Kelime kümelerinin kesişim/birleşim oranı

4. **Levenshtein Distance** (karakter düzeyi)
   - Özellikle dosya isimleri için kullanılır

### Performans

- **CPU Kullanımı:** Düşük (async processing)
- **Hafıza:** ~50-100MB (firma sayısına bağlı)
- **Analiz Hızı:** ~1-3 saniye/dosya
- **Batch İşlem:** 100 ms mola ile sıralı işleme

---

## 📁 Proje Yapısı

```
ai-bot/
├── bot.js                    # Telegram bot (ana dosya)
├── ai-service.js             # AI analiz motoru
├── feedback.js               # Hafıza güncelleme
├── utils/
│   ├── extractor.js          # Dosya metin çıkarıcı
│   ├── similarity.js         # Benzerlik hesaplamaları
│   └── memory/
│       ├── global.json       # Global hafıza
│       ├── categories.json   # Kategori hafızası
│       ├── feedbacks.json    # Feedback kayıtları
│       └── firms/
│           ├── 1.json        # Firma 1 hafızası
│           ├── 2.json        # Firma 2 hafızası
│           └── ...
├── package.json
├── .env
└── README.md
```

---

## 🔧 Troubleshooting

### Bot çalışmıyor

1. `.env` dosyasını kontrol edin
2. Telegram bot token'ı doğru mu?
3. ALLOWED_TELEGRAM_IDS doğru mu?
4. Laravel API erişilebilir mi?

```bash
curl http://127.0.0.1:8000/api/documents/all
```

### Dosya okunamıyor

- `STORAGE_PATH` doğru mu?
- Dosya yolu Laravel storage'a göre ayarlı mı?
- Dosya formatı destekleniyor mu? (PDF, XLSX, DOCX, TXT)

### Analiz çok yavaş

- `analyzeBatch` fonksiyonundaki timeout'u artırın
- Batch size'ı küçültün
- CPU güçlü değilse dosyaları teker teker işleyin

### Hafıza kaydedilmiyor

- `utils/memory/` dizini yazılabilir mi?
- Dizin izinlerini kontrol edin:

```bash
chmod -R 755 utils/memory/
```

---

## 🔄 Güncelleme ve Bakım

### Hafıza Sıfırlama

```bash
rm -rf utils/memory/firms/*.json
rm utils/memory/feedbacks.json
```

### Kategori Bilgilerini Güncelleme

`utils/memory/categories.json` dosyasını düzenleyin:

```json
{
  "YeniKategori": {
    "keywords": ["kelime1", "kelime2", "kelime3"],
    "risk_patterns": ["hata1", "hata2"],
    "weight": 1.0
  }
}
```

### Log İzleme

Bot console'da çalışırken tüm logları gösterir:

```bash
npm start
```

---

## 📈 Gelecek Özellikler (Roadmap)

- [ ] Web dashboard (React/Vue)
- [ ] Grafik ve istatistikler
- [ ] Excel rapor export
- [ ] Otomatik kategori öğrenme
- [ ] Multi-language support
- [ ] Webhook desteği
- [ ] Email bildirim entegrasyonu

---

## 🤝 Katkıda Bulunma

Bu proje IdeaDocs sistemi için geliştirilmiştir. İyileştirme önerileri için issue açabilirsiniz.

---

## 📄 Lisans

MIT License

---

## 💡 İletişim

**IdeaDocs AI Bot v1.0.0**

Sorularınız için: Telegram bot üzerinden `/start` komutu ile başlayın.

---

## 🎯 Hızlı Başlangıç

```bash
# 1. Bağımlılıkları yükle
npm install

# 2. .env dosyasını oluştur
cp .env.example .env
# Token ve ID'leri düzenle

# 3. Laravel API'yi başlat
cd ../
php artisan serve

# 4. Bot'u başlat
cd ai-bot
npm start

# 5. Telegram'dan /start komutu ile test et
```

**Başarılar! 🚀**

