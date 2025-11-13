@extends('layouts.layoutMaster')

@section('title', 'Kullanıcı Notu ve Belgeleri')

@section('content')
<style>
  .table-fixed { 
    table-layout: fixed; 
    width: 100%;
  }
  .col-idx { width: 40px; }
  .col-category { width: 150px; }
  .col-status { width: 180px; }
  .col-approved { width: 70px; }
  .col-approver { width: 130px; }
  .col-rejected { width: 80px; }
  .col-pending { width: 80px; }
  .col-download { width: 140px; }
  .col-note { width: 200px; min-width: 150px; }
  .col-actions { width: 110px; }
  
  .text-truncate { 
    max-width: 100%;
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap;
  }
  
  .nowrap { 
    white-space: nowrap; 
  }
  
  .note-text {
    display: block;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  
  .note-input {
    max-width: 200px;
  }
  
  @media (max-width: 1200px) {
    .col-download { width: 120px; }
    .col-approver { width: 110px; }
  }
  
  @media (max-width: 992px) {
    .table-responsive {
      font-size: 0.875rem;
    }
    .col-note { width: 150px; }
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
            <th class="col-note">Açıklama</th>
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
            <td class="col-category text-truncate" title="{{ $category->name }}">{{ $category->name }}</td>
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
            <td class="col-actions nowrap">
              <button 
                type="button" 
                class="btn btn-sm btn-icon btn-outline-primary edit-note-btn"
                data-category-id="{{ $category->id }}"
                data-user-id="{{ $user->id }}"
                onclick="toggleNoteEdit({{ $category->id }})"
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
    
  
</script>

@endsection
