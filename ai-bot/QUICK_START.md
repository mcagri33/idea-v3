# 🚀 Hızlı Başlangıç Kılavuzu

## 5 Dakikada AI Bot Kurulumu

### 1️⃣ Telegram Bot Oluşturun (2 dk)

1. Telegram'da **@BotFather**'a gidin
2. `/newbot` yazın
3. Bot adı verin (örn: `IdeaDocs AI Assistant`)
4. Bot kullanıcı adı verin (örn: `ideadocs_ai_bot`)
5. **Token'ı kopyalayın** (örn: `123456789:ABCDEF...`)

**Kendi Telegram ID'nizi öğrenin:**
- **@userinfobot**'a gidin
- `/start` yazın
- ID'nizi kopyalayın

---

### 2️⃣ Bot'u Kurun (2 dk)

```bash
cd C:\xampp\htdocs\ideadocs\ai-bot

# Windows için:
setup.bat

# veya Manuel:
npm install
```

---

### 3️⃣ Konfigürasyon (1 dk)

`.env` dosyasını düzenleyin:

```env
TG_BOT_TOKEN=123456789:ABCDEF_YOUR_ACTUAL_TOKEN
ALLOWED_TELEGRAM_IDS=YOUR_TELEGRAM_ID

LARAVEL_API_URL=http://127.0.0.1:8000/api
STORAGE_PATH=../public/storage
```

**Önemli:** Token ve ID'leri değiştirin!

---

### 4️⃣ Laravel API'yi Başlatın

Yeni bir terminal açın:

```bash
cd C:\xampp\htdocs\ideadocs
php artisan serve
```

API test edin:
```bash
curl http://127.0.0.1:8000/api/documents/all
```

---

### 5️⃣ Bot'u Başlatın

```bash
cd C:\xampp\htdocs\ideadocs\ai-bot
npm start
```

Başarılı mesajı görmelisiniz:
```
✅ Bot başarıyla başlatıldı!
📡 Dinleniyor: @your_bot_username
```

---

## 🎯 İlk Kullanım

### Telegram'dan test edin:

1. Bot'unuzu Telegram'da bulun
2. `/start` yazın
3. Hoşgeldin mesajını görün
4. `/analiz_hepsi` yazın - Tüm bekleyen dosyalar analiz edilecek!

---

## 💡 Örnek Senaryo

```
👤 Siz: /analiz_hepsi

🤖 Bot: 🔍 Tüm bekleyen dosyalar analiz ediliyor...
🤖 Bot: 📄 3 bekleyen dosya bulundu. Analiz başlıyor...

🤖 Bot: 
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

📌 Öneriler:
- Manuel kontrol önerilir.

[✅ Onayla] [❌ Reddet]

👤 Siz: [✅ Onayla] butonuna basın

🤖 Bot: ✅ Dosya onaylandı
         ✅ Onaylandı: Mizan_ABC_2024.xlsx
```

---

## 🔄 Günlük Kullanım

### Sabah Rutini:
```
/analiz_hepsi → Gece yüklenen dosyaları kontrol et
/riskli → Riskli dosyaları listele
/rapor → Günlük özeti gör
```

### Firma Takibi:
```
/firma → Firma seç → Raporu gör
/ai_firma → Firma seç → AI hafızasını incele
/kategori → Firma seç → Kategori analizi
```

### Sistem İzleme:
```
/ai_durum → Genel AI performansı
/rapor → Sistem özeti
```

---

## ⚙️ Özelleştirme

### Kategori Anahtar Kelimeleri Ekleyin

`utils/memory/categories.json` dosyasını düzenleyin:

```json
{
  "Mizan": {
    "keywords": ["borç", "alacak", "bilanço", "denge"],
    "risk_patterns": ["eksik satır", "tarih hatası"],
    "weight": 1.0
  },
  "Cari": {
    "keywords": ["müşteri", "hesap", "ödeme"],
    "risk_patterns": ["boş hesap", "eksik tarih"],
    "weight": 1.0
  }
}
```

Bot'u yeniden başlatın: `Ctrl+C` sonra `npm start`

---

## 🐛 Sorun Giderme

### Bot başlamıyor?

```bash
# Token kontrolü
cat .env | grep TG_BOT_TOKEN

# Laravel API test
curl http://127.0.0.1:8000/api/documents/all

# Logları kontrol et
npm start
```

### "Yetkisiz erişim" hatası?

`.env` dosyasında `ALLOWED_TELEGRAM_IDS` doğru mu?

Telegram ID'nizi öğrenin: [@userinfobot](https://t.me/userinfobot)

### Dosya okunamıyor?

`STORAGE_PATH` doğru mu? Varsayılan: `../public/storage`

---

## 📊 İlk Hafta Beklentileri

### Gün 1-2: Öğrenme Fazı
- Bot tüm onaylı/red dosyaları öğrenir
- Hafızalar oluşturulur
- İlk analizler düşük güvenilirlik gösterebilir

### Gün 3-5: Gelişme
- Firma hafızaları zenginleşir
- Benzerlik skorları daha isabetli olur
- Risk pattern'leri netleşir

### Gün 6-7: Olgunluk
- Bot %70-80 doğrulukla tahmin yapar
- Riskli dosyaları isabetli tespit eder
- Manuel kontrol süresi azalır

---

## 🎓 Daha Fazla Öğrenin

- **README.md** - Detaylı dokümantasyon
- **Telegram Komutları** - Bot'da `/start`
- **Kod İnceleme** - `ai-service.js`, `bot.js`

---

## 🆘 Destek

Sorun yaşıyorsanız:

1. `npm start` çıktısını kontrol edin
2. `.env` dosyasını gözden geçirin
3. Laravel API'nin çalıştığından emin olun
4. README.md'deki Troubleshooting bölümüne bakın

---

## ✅ Checklist

- [ ] Telegram bot oluşturuldu
- [ ] Token `.env`'ye eklendi
- [ ] Telegram ID `.env`'ye eklendi
- [ ] `npm install` çalıştırıldı
- [ ] Laravel API çalışıyor (`php artisan serve`)
- [ ] Bot başlatıldı (`npm start`)
- [ ] Telegram'dan `/start` test edildi
- [ ] `/analiz_hepsi` ile ilk analiz yapıldı

**Hepsi tamam mı? Tebrikler, sisteminiz hazır! 🎉**

---

Başarılar! 🚀

