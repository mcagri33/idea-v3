@extends('layouts.layoutMaster')

@section('title', 'Evrak Yükleme Görevi Atama')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">📄 Evrak Yükleme Görevi Atama</h5>
        </div>
        <div class="card-body">

          {{-- Başarılı mesaj --}}
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <form action="{{ route('assignments.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf

            <div class="mb-3">
              <label for="user_id" class="form-label">👤 Kullanıcı Seç</label>
              <select name="user_id" id="user_id" class="form-select" required>
                <option value="">Bir kullanıcı seçiniz...</option>
                @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Lütfen bir kullanıcı seçiniz.</div>
            </div>

            <div class="mb-3">
              <label for="category_id" class="form-label">📂 Evrak Kategorisi</label>
              <select name="category_id" id="category_id" class="form-select" required>
                <option value="">Bir kategori seçiniz...</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Lütfen bir kategori seçiniz.</div>
            </div>

            <div class="mb-3">
              <label for="title" class="form-label">📝 Görev Başlığı</label>
              <input type="text" name="title" id="title" class="form-control" required placeholder="Örn: Vakıfbank Mutabakatı - Mayıs 2025">
              <div class="invalid-feedback">Başlık zorunludur.</div>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">📌 Açıklama</label>
              <textarea name="description" id="description" class="form-control" rows="3" placeholder="Detaylı açıklama..."></textarea>
            </div>

            <div class="mb-3">
              <label for="due_date" class="form-label">📅 Son Teslim Tarihi <small class="text-muted">(Opsiyonel)</small></label>
              <input type="date" name="due_date" id="due_date" class="form-control">
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success">
                <i class="ti ti-send"></i> Görevi Ata
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bootstrap validation --}}
<script>
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
@endsection
