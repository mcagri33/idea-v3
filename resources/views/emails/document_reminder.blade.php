<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #4caf50;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .category-list {
            background: #ffffff;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .category-list h4 {
            margin-top: 0;
            color: #ff9800;
        }
        .category-item {
            padding: 10px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .category-item:last-child {
            border-bottom: none;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background: #4caf50;
            color: #ffffff;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background: #333333;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📋 Belge Hatırlatması</h2>
            <p style="margin: 10px 0 0 0; font-size: 16px;">{{ config('app.name') }}</p>
        </div>

        <div class="content">
            <p style="font-size: 16px;">Sayın <strong>{{ $customer->name }}</strong>,</p>

            <p>
                <strong>{{ $year }}</strong> yılına ait mali belgelerinizin durumunu kontrol ettik.
                Aşağıdaki kategorilerde henüz belge yüklemediniz:
            </p>

            <div class="category-list">
                <h4>⚠️ Eksik Belgeler ({{ $missingCategories->count() }} adet)</h4>
                @foreach($missingCategories as $loopIndex => $category)
                    <div class="category-item">
                        <strong>{{ $loopIndex + 1 }}.</strong> {{ $category->name }}
                        @if($category->description)
                            <br>
                            <small style="color: #666666; padding-left: 20px;">{{ $category->description }}</small>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="warning">
                <strong>⏰ Önemli:</strong> Lütfen eksik belgelerinizi en kısa sürede sisteme yükleyiniz.
            </div>

            <div class="info-box">
                <strong>📅 Bilgilendirme:</strong>
                {{ now()->year }} yılındayız ve {{ $year }} yılının belgelerini tamamlamanız gerekmektedir.
                Mali mevzuat gereği belgelerin zamanında teslim edilmesi önemlidir.
            </div>

            <center>
                <a href="{{ url('/panel') }}" class="button" target="_blank" rel="noopener">
                    🚀 Sisteme Giriş Yap ve Belgeleri Yükle
                </a>
            </center>

            <p style="margin-top: 30px; color: #666666; font-size: 13px;">
                <strong>İletişim:</strong><br>
                Bu mail otomatik olarak gönderilmiştir. Sorularınız için bizimle iletişime geçebilirsiniz.
            </p>
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p style="margin-top: 10px; opacity: 0.8;">{{ date('Y') }} © Tüm hakları saklıdır.</p>
        </div>
    </div>
</body>
</html>

