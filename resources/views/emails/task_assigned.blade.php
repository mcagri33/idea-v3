<h2>Merhaba {{ $user->name }},</h2>

<p>Size yeni bir evrak görevi atandı:</p>

<ul>
  <li><strong>Başlık:</strong> {{ $assignment->title }}</li>
  <li><strong>Açıklama:</strong> {{ $assignment->description }}</li>
  <li><strong>Son Tarih:</strong> {{ $assignment->due_date ?? 'Belirtilmemiş' }}</li>
</ul>

<p>Lütfen evrakınızı sistem üzerinden zamanında yükleyiniz.</p>

<p>Teşekkürler,<br>IdeaDocs Ekibi</p>
