@extends('layouts.layoutMaster')

@section('title', 'Kullanıcı Notu ve Belgeleri')

@section('content')
<style>
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .table-fixed { 
    table-layout: fixed; 
    width: 100%;
  }
  
  .col-idx { width: 45px; }
  .col-category { width: 180px; }
  .col-approved { width: 80px; }
  .col-rejected { width: 100px; }
  .col-pending { width: 90px; }
  .col-status { width: 160px; }
  .col-approver { width: 120px; }
  .col-download { width: 130px; }
  .col-note { width: 140px; }
  .col-auditor-note { width: 140px; }
  .col-actions { width: 130px; }
  
  /* Hizalama - Başlıklar */
  th.col-idx { text-align: center; }
  th.col-category { text-align: left; }
  th.col-approved,
  th.col-rejected,
  th.col-pending {
    text-align: center;
  }
  th.col-status { text-align: left; }
  th.col-approver { text-align: left; }
  th.col-download { text-align: left; }
  th.col-note { text-align: left; }
  th.col-auditor-note { text-align: left; }
  th.col-actions { text-align: center; }
  
  /* Hizalama - İçerik */
  .col-idx { text-align: center; }
  .col-category { text-align: left; }
  .col-approved,
  .col-rejected,
  .col-pending {
    text-align: center;
  }
  .col-status { text-align: left; }
  .col-approver { text-align: left; }
  .col-download { text-align: left; }
  .col-note { text-align: left; }
  .col-auditor-note { text-align: left; }
  .col-actions { text-align: center; }
  
  .col-category {
    word-wrap: break-word;
    word-break: break-word;
  }
  
  .col-actions .d-flex {
    flex-wrap: wrap;
    gap: 0.25rem;
    justify-content: center;
  }
  
  /* Tablo genel hizalama */
  table thead th {
    vertical-align: middle;
  }
  
  table tbody td {
    vertical-align: middle;
  }
  
  /* Not sütunları için üstten hizalama (çok satırlı metinler için) */
  .col-note,
  .col-auditor-note {
    vertical-align: top;
  }
  
  .auditor-note-text {
    display: block;
    max-width: 100%;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.4;
  }
  
  .auditor-note-input {
    max-width: 100%;
    font-size: 0.875rem;
  }
  
  .text-truncate { 
    max-width: 100%;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
  }
  
  .nowrap { 
    white-space: nowrap; 
  }
  
  .note-text {
    display: block;
    max-width: 100%;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.4;
  }
  
  .note-input {
    max-width: 100%;
    font-size: 0.875rem;
  }
  
  .col-note,
  .col-auditor-note {
    vertical-align: top;
  }
  
  @media (max-width: 1400px) {
    .col-category { width: 160px; }
    .col-status { width: 150px; }
    .col-note { width: 130px; }
    .col-auditor-note { width: 130px; }
  }
  
  @media (max-width: 1200px) {
    .col-category { width: 150px; }
    .col-download { width: 115px; }
    .col-approver { width: 110px; }
    .col-status { width: 140px; }
    .col-note { width: 120px; }
    .col-auditor-note { width: 120px; }
  }
  
  @media (max-width: 992px) {
    .table-responsive {
      font-size: 0.875rem;
    }
    .col-category { width: 130px; }
    .col-status { width: 130px; }
    .col-note { width: 110px; }
    .col-auditor-note { width: 110px; }
    .col-actions { width: 115px; }
  }
  
  @media (max-width: 768px) {
    .col-category { width: 110px; }
    .col-status { width: 120px; }
    .col-note { width: 95px; }
    .col-auditor-note { width: 95px; }
  }
</style>

  <h4 class="fw-bold py-3 mb-4">{{ $user->name }} - {{ $year }} Yılı Belgeleri</h4>

  {{-- Action Buttons --}}
  <div class="d-flex gap-2 mb-4">
  <a href="{{ route('general.note.exportPdf', $user->id) }}?year={{ $year }}" 
   class="btn btn-success">
  <i class="ti ti-file-type-pdf me-2"></i>PDF İndir
</a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendMailModal">
      <i class="ti ti-mail me-2"></i>Mail Gönder
    </button>
  </div>

  {{-- Not Formu --}}
  <form method="POST" action="{{ route('general.note.saveNote', $user->id) }}">
    @csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <div class="mb-3">
      <label for="note" class="form-label">{{ $year }} Yılı Yönetici Özeti</label>
      <textarea class="form-control" id="content" name="note" rows="4">{{ old('note', $note?->note) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Kaydet</button>
  </form>

  {{-- Yıl Seçimi --}}
  <form action="{{ url()->current() }}" method="GET" class="d-flex my-4">
    <label for="year" class="me-2">Yıl:</label>
    <select name="year" id="year" class="form-select w-auto" onchange="this.form.submit()">
    @foreach (range(now()->year - 1, now()->year - 11) as $yr)
    <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
      @endforeach
    </select>
  </form>

  {{-- Kategori Tablosu --}}
  <div class="card mt-4">
    <div class="table-responsive">
      <table class="table table-sm table-fixed align-middle" id="categoriesTable">
        <thead>
          <tr>
            <th class="col-idx">#</th>  
            <th class="col-category">Kategori</th>
            <th class="col-approved">Onaylı</th>
            <th class="col-rejected">Reddedilen</th>
            <th class="col-pending">Bekleyen</th>
            <th class="col-status">Durum</th>
            <th class="col-approver d-none d-md-table-cell">Onaylayan</th>
            <th class="col-download d-none d-lg-table-cell">İndirme Durumu</th>
            <th class="col-note">Açıklama</th>
            <th class="col-auditor-note">Denetçi Notu</th>
            <th class="col-actions">İşlemler</th>
          </tr>
        </thead>
        <tbody>
       
        @foreach($categories as $category)
  @php
    $adminNote = $adminNotes[$category->id]['note'] ?? null;
  @endphp
  <tr>
            <td class="col-idx nowrap">{{ $loop->iteration }}</td> 
            <td class="col-category">{{ $category->name }}</td>
            <td class="col-approved"><span class="badge bg-label-success">{{ $category->approved_count }}</span></td>
            <td class="col-rejected"><span class="badge bg-label-danger">{{ $category->rejected_count }}</span></td>
            <td class="col-pending"><span class="badge bg-label-warning">{{ $category->pending_count }}</span></td>
            {{-- Durum --}}
            <td class="col-status">
              @php
                $totalCount = $category->approved_count + $category->rejected_count + $category->pending_count;
              @endphp
              @if($totalCount > 0)
                @if($category->approved_count > 0)
                  <span class="badge bg-label-success">Yüklenmiş ve Onaylanmış</span>
                @elseif($category->pending_count > 0)
                  <span class="badge bg-label-warning">Yüklenmiş ve İncelemede</span>
                @elseif($category->rejected_count > 0)
                  <span class="badge bg-label-danger">Yüklenmiş ve Reddedildi</span>
                @endif
              @else
                <span class="badge bg-label-secondary">Yüklenmemiş</span>
              @endif
            </td>
            {{-- Onaylayan --}}
            <td class="col-approver d-none d-md-table-cell nowrap">
              @if($category->approved_count > 0 && $category->has_approved && $category->approve_log && $category->approve_log->performedBy)
                <span class="text-muted small">
                  {{ $category->approve_log->performedBy->name }}
                  <br>
                  <small>{{ $category->approve_log->created_at->format('d/m/Y H:i') }}</small>
                </span>
              @elseif($category->approved_count > 0)
                <span class="text-muted">-</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            {{-- İndirme Durumu --}}
            <td class="col-download d-none d-lg-table-cell nowrap">
              @if($category->has_download && $category->last_download_log && $category->last_download_log->performedBy)
                <span class="badge bg-label-success">İndirilmiş</span>
                <br>
                <small class="text-muted">
                  {{ $category->last_download_log->performedBy->name }}
                  <br>
                  {{ $category->last_download_log->created_at->format('d/m/Y H:i') }}
                </small>
              @else
                <span class="badge bg-label-secondary">İndirilmemiş</span>
              @endif
            </td>
            <td class="col-note">
  <span id="note-text-{{ $category->id }}" class="note-text" title="{{ $adminNote ?? '-' }}">
    {{ $adminNote ?? '-' }}
  </span>
  <textarea 
    id="note-input-{{ $category->id }}" 
    class="form-control d-none note-input" 
    rows="2"
  >{{ $adminNote ?? '' }}</textarea>
</td>
            {{-- Denetçi Notu --}}
            <td class="col-auditor-note">
              <span id="auditor-note-text-{{ $category->id }}" class="auditor-note-text" title="{{ $category->auditor_note ?? '-' }}">
                {{ $category->auditor_note ?? '-' }}
              </span>
              <textarea 
                id="auditor-note-input-{{ $category->id }}" 
                class="form-control d-none auditor-note-input" 
                rows="2"
              >{{ $category->auditor_note ?? '' }}</textarea>
            </td>
            <td class="col-actions nowrap">
              <div class="d-flex gap-1">
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-primary edit-note-btn"
                  data-category-id="{{ $category->id }}"
                  data-user-id="{{ $user->id }}"
                  onclick="toggleNoteEdit({{ $category->id }})"
                  title="Açıklama Düzenle"
                >
                  <i class="ti ti-edit"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-success save-note-btn d-none"
                  data-category-id="{{ $category->id }}"
                  data-user-id="{{ $user->id }}"
                  onclick="saveCategoryNote({{ $category->id }}, {{ $user->id }})"
                >
                  <i class="ti ti-check"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-danger cancel-note-btn d-none"
                  data-category-id="{{ $category->id }}"
                  onclick="cancelNoteEdit({{ $category->id }})"
                >
                  <i class="ti ti-x"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-info edit-auditor-note-btn"
                  data-category-id="{{ $category->id }}"
                  data-user-id="{{ $user->id }}"
                  onclick="toggleAuditorNoteEdit({{ $category->id }})"
                  title="Denetçi Notu Düzenle"
                >
                  <i class="ti ti-file-text"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-success save-auditor-note-btn d-none"
                  data-category-id="{{ $category->id }}"
                  data-user-id="{{ $user->id }}"
                  onclick="saveAuditorNote({{ $category->id }}, {{ $user->id }})"
                >
                  <i class="ti ti-check"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-icon btn-outline-danger cancel-auditor-note-btn d-none"
                  data-category-id="{{ $category->id }}"
                  onclick="cancelAuditorNoteEdit({{ $category->id }})"
                >
                  <i class="ti ti-x"></i>
                </button>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Mail Gönderme Modal --}}
  <div class="modal fade" id="sendMailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Mail Hatırlatıcı Gönder</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('general.note.sendMail', ['user' => $user->id, 'year' => $year]) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="mail_subject" class="form-label">Konu</label>
              <input 
                type="text" 
                class="form-control" 
                id="mail_subject" 
                name="subject" 
                value="{{ $year }} Yılı Belgeler Hatırlatıcı - {{ $user->company ?? $user->name }}"
                required
              >
            </div>
            <div class="mb-3">
              <label for="mail_message" class="form-label">Mesaj</label>
              <textarea 
                class="form-control" 
                id="mail_message" 
                name="message" 
                rows="5"
                required
              >Merhaba {{ $user->name }},

{{ $year }} yılı belgelerinizin durumu:

@foreach($categories as $category)
- {{ $category->name }}: 
  ✅ Onaylı: {{ $category->approved_count }}
  ❌ Reddedilen: {{ $category->rejected_count }}
  ⏳ Bekleyen: {{ $category->pending_count }}
@endforeach

Lütfen eksik belgeleri tamamlayınız.

İyi çalışmalar.</textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
            <button type="submit" class="btn btn-primary">Mail Gönder</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
  ClassicEditor.create(document.querySelector('#content'))
    .catch(error => {
      console.error(error);
    });

  // Kategori notu düzenleme fonksiyonları
  function toggleNoteEdit(categoryId) {
    const textElement = document.getElementById(`note-text-${categoryId}`);
    const inputElement = document.getElementById(`note-input-${categoryId}`);
    const editBtn = document.querySelector(`[data-category-id="${categoryId}"].edit-note-btn`);
    const saveBtn = document.querySelector(`[data-category-id="${categoryId}"].save-note-btn`);
    const cancelBtn = document.querySelector(`[data-category-id="${categoryId}"].cancel-note-btn`);

    textElement.classList.add('d-none');
    inputElement.classList.remove('d-none');
    editBtn.classList.add('d-none');
    saveBtn.classList.remove('d-none');
    cancelBtn.classList.remove('d-none');
  }

  function cancelNoteEdit(categoryId) {
    const textElement = document.getElementById(`note-text-${categoryId}`);
    const inputElement = document.getElementById(`note-input-${categoryId}`);
    const editBtn = document.querySelector(`[data-category-id="${categoryId}"].edit-note-btn`);
    const saveBtn = document.querySelector(`[data-category-id="${categoryId}"].save-note-btn`);
    const cancelBtn = document.querySelector(`[data-category-id="${categoryId}"].cancel-note-btn`);

    inputElement.value = textElement.textContent.trim() === '-' ? '' : textElement.textContent.trim();
    textElement.classList.remove('d-none');
    inputElement.classList.add('d-none');
    editBtn.classList.remove('d-none');
    saveBtn.classList.add('d-none');
    cancelBtn.classList.add('d-none');
  }

  async function saveCategoryNote(categoryId, userId) {
  const inputElement = document.getElementById(`note-input-${categoryId}`);
  const noteText = inputElement.value;
  const year = {{ $year }};

  try {
    const response = await fetch('{{ route("general.note.saveCategoryNote", ["user" => $user->id, "year" => $year]) }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        category_id: categoryId,
        year: year,
        note: noteText
      })
    });

    const data = await response.json();

    if (data.success || response.ok) {
      const textElement = document.getElementById(`note-text-${categoryId}`);
      textElement.textContent = noteText || '-';
      cancelNoteEdit(categoryId);
      
      // Başarı mesajı göster
      const toast = document.createElement('div');
      toast.className = 'toast align-items-center text-white bg-success border-0';
      toast.setAttribute('role', 'alert');
      toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">Kategori notu kaydedildi.</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toast);
      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
    } else {
      throw new Error(data.message || 'Kaydetme başarısız');
    }
  } catch (error) {
    console.error('Hata:', error);
    alert('Not kaydedilirken bir hata oluştu: ' + error.message);
  }
}

  // Denetçi notu düzenleme fonksiyonları
  function toggleAuditorNoteEdit(categoryId) {
    const textElement = document.getElementById(`auditor-note-text-${categoryId}`);
    const inputElement = document.getElementById(`auditor-note-input-${categoryId}`);
    const editBtn = document.querySelector(`[data-category-id="${categoryId}"].edit-auditor-note-btn`);
    const saveBtn = document.querySelector(`[data-category-id="${categoryId}"].save-auditor-note-btn`);
    const cancelBtn = document.querySelector(`[data-category-id="${categoryId}"].cancel-auditor-note-btn`);

    textElement.classList.add('d-none');
    inputElement.classList.remove('d-none');
    editBtn.classList.add('d-none');
    saveBtn.classList.remove('d-none');
    cancelBtn.classList.remove('d-none');
  }

  function cancelAuditorNoteEdit(categoryId) {
    const textElement = document.getElementById(`auditor-note-text-${categoryId}`);
    const inputElement = document.getElementById(`auditor-note-input-${categoryId}`);
    const editBtn = document.querySelector(`[data-category-id="${categoryId}"].edit-auditor-note-btn`);
    const saveBtn = document.querySelector(`[data-category-id="${categoryId}"].save-auditor-note-btn`);
    const cancelBtn = document.querySelector(`[data-category-id="${categoryId}"].cancel-auditor-note-btn`);

    inputElement.value = textElement.textContent.trim() === '-' ? '' : textElement.textContent.trim();
    textElement.classList.remove('d-none');
    inputElement.classList.add('d-none');
    editBtn.classList.remove('d-none');
    saveBtn.classList.add('d-none');
    cancelBtn.classList.add('d-none');
  }

  async function saveAuditorNote(categoryId, userId) {
    const inputElement = document.getElementById(`auditor-note-input-${categoryId}`);
    const auditorNoteText = inputElement.value;
    const year = {{ $year }};

    try {
      const response = await fetch('{{ route("general.note.saveAuditorNote", ["user" => $user->id, "year" => $year]) }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          category_id: categoryId,
          year: year,
          auditor_note: auditorNoteText
        })
      });

      const data = await response.json();

      if (data.success || response.ok) {
        const textElement = document.getElementById(`auditor-note-text-${categoryId}`);
        textElement.textContent = auditorNoteText || '-';
        textElement.setAttribute('title', auditorNoteText || '-');
        cancelAuditorNoteEdit(categoryId);
        
        // Başarı mesajı göster
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">Denetçi notu kaydedildi.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
      } else {
        throw new Error(data.message || 'Kaydetme başarısız');
      }
    } catch (error) {
      console.error('Hata:', error);
      alert('Denetçi notu kaydedilirken bir hata oluştu: ' + error.message);
    }
  }
    
  
</script>

@endsection
