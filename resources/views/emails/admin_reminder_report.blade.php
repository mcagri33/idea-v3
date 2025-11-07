<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
        }
        .header {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 26px;
        }
        .content {
            padding: 30px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 20px;
        }
        .stat-box {
            flex: 1 1 180px;
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }
        .stat-box.orange {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        }
        .stat-number {
            font-size: 40px;
            font-weight: bold;
            margin: 8px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.95;
        }
        .user-list {
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .user-item {
            background: #ffffff;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .user-item:nth-child(even) {
            background: #fafafa;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            background: #ff9800;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .info-banner {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 18px;
            border-radius: 6px;
            margin: 25px 0 0 0;
        }
        .category-list {
            padding-left: 20px;
            margin: 10px 0 0 0;
            color: #555555;
            font-size: 14px;
        }
        .category-list li {
            padding: 5px 0;
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
            <h2>📊 Belge Hatırlatma Raporu</h2>
            <p style="margin: 10px 0; font-size: 18px;"><strong>{{ $year }}</strong> Yılı Eksik Belgeler</p>
            <p style="font-size: 14px; opacity: 0.95;">{{ now()->format('d.m.Y H:i') }}</p>
            <p style="font-size: 13px; opacity: 0.9; margin-top: 5px;">({{ now()->year }} yılında {{ $year }} yılı belgeleri kontrol edilmiştir)</p>
        </div>

        <div class="content">
            <h3 style="color: #333333; border-bottom: 2px solid #2196f3; padding-bottom: 10px;">📈 Özet Bilgiler</h3>

            <div class="stats">
                <div class="stat-box">
                    <div class="stat-label">Gönderilen Hatırlatma</div>
                    <div class="stat-number">{{ count($remindersSent) }}</div>
                </div>
                <div class="stat-box orange">
                    <div class="stat-label">Toplam Eksik Kategori</div>
                    <div class="stat-number">{{ collect($remindersSent)->sum('missing_count') }}</div>
                </div>
            </div>

            <h3 style="color: #333333; border-bottom: 2px solid #2196f3; padding-bottom: 10px; margin-top: 40px;">👥 Hatırlatma Gönderilen Kullanıcılar</h3>

            <div class="user-list">
                @foreach($remindersSent as $index => $reminder)
                    <div class="user-item">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div>
                                <strong style="font-size: 16px; color: #333333;">{{ $index + 1 }}. {{ $reminder['user']->name }}</strong>
                                @if($reminder['user']->company)
                                    <span style="color: #666666;"> - {{ $reminder['user']->company }}</span>
                                @endif
                            </div>
                            <span class="badge">{{ $reminder['missing_count'] }} eksik</span>
                        </div>

                        <div style="margin: 5px 0;">
                            <small style="color: #666666;">📧 {{ $reminder['user']->email }}</small>
                        </div>

                        <div style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                            <strong style="color: #555555; font-size: 13px;">Eksik Kategoriler:</strong>
                            <ul class="category-list">
                                @foreach($reminder['categories'] as $category)
                                    <li>{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="info-banner">
                <strong>ℹ️ Bilgilendirme:</strong>
                Bu mail otomatik belge hatırlatma sistemi tarafından oluşturulmuştur. Tüm kullanıcılara eksik belge hatırlatması başarıyla gönderilmiştir.
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Otomatik Belge Hatırlatma Sistemi</p>
            <p style="margin-top: 10px; opacity: 0.8;">Bu rapor sistem tarafından otomatik olarak oluşturulmuştur.</p>
        </div>
    </div>
</body>
</html>

