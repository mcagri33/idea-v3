<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $user->name }} - {{ $year }} Yılı Belgeleri</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url({{ storage_path('fonts/dejavu-sans.ttf') }}) format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        * {
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            direction: ltr;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        th.text-center, td.text-center {
            text-align: center;
        }
        .note {
            font-style: italic;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
        .summary p {
            white-space: pre-line;
            margin: 0;
        }
    </style>
</head>
<body>
    <h1>{{ $user->name }} - {{ $year }} Yılı Belgeleri</h1>
    
    @if($note && $note->note)
    <div class="summary">
        <h3>Yönetici Özeti:</h3>
        <p>{{ strip_tags($note->note) }}</p>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-center">Onaylı</th>
                <th class="text-center">Reddedilen</th>
                <th class="text-center">Bekleyen</th>
                <th>Açıklama</th>
            </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
          @php
            $adminNote = $adminNotes[$category->id]['note'] ?? null;
          @endphp
          <tr>
            <td><strong>{{ $category->name }}</strong></td>
            <td class="text-center">{{ $category->approved_count }}</td>
            <td class="text-center">{{ $category->rejected_count }}</td>
            <td class="text-center">{{ $category->pending_count }}</td>
            <td class="note">{{ $adminNote ? strip_tags($adminNote) : '-' }}</td>
          </tr>
        @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center; color: #666;">
        <p>Rapor Tarihi: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
</body>
</html>