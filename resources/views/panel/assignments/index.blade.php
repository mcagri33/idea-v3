@extends('layouts.layoutMaster')

@section('title', 'Atanmış Görevler')

@section('content')
<div class="container py-4">

  {{-- Başlık ve Yeni Görev Butonu --}}
  <div class="row justify-content-between mb-3">
    <div class="col">
      <h4 class="mb-0">📋 Atanmış Görevler</h4>
    </div>
    <div class="col-auto">
      <a href="{{ route('assignments.create') }}" class="btn btn-success">
        <i class="ti ti-plus"></i> Yeni Görev Ata
      </a>
    </div>
  </div>

  {{-- Filtre Formu --}}
  <form method="GET" class="mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">👤 Kullanıcı</label>
        <select name="user_id" class="form-select">
          <option value="">Tümü</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
              {{ $user->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">📂 Kategori</label>
        <select name="category_id" class="form-select">
          <option value="">Tümü</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
              {{ $category->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">🔍 Başlık Ara</label>
        <input type="text" name="search" class="form-control" placeholder="Başlık..." value="{{ request('search') }}">
      </div>

      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-filter"></i> Filtrele
        </button>
      </div>
    </div>
  </form>

  {{-- Görev Tablosu --}}
  <div class="card shadow-sm">
    <div class="card-body">
      @if($assignments->count())
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead>
              <tr>
                <th>Kullanıcı</th>
                <th>Kategori</th>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Son Tarih</th>
                <th>Atanma</th>
				<th>Durum</th>
				<th>Sil</th>
              </tr>
            </thead>
            <tbody>
              @foreach($assignments as $assignment)
                <tr>
                 <td>{{ optional($assignment->user)->name ?? '-' }}</td>
                 <td>{{ optional($assignment->category)->name ?? '-' }}</td>
                  <td>{{ $assignment->title }}</td>
                  <td>{{ $assignment->description }}</td>
                  <td>
                    {{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('d.m.Y') : '-' }}
                  </td>
                  <td>{{ $assignment->created_at->format('d.m.Y H:i') }}</td>
				<td>
  @switch($assignment->status)
    @case('pending')
      <span class="badge bg-warning text-dark">Beklemede</span>
      @break
    @case('uploaded')
      <span class="badge bg-success">Tamamlandı</span>
      @break
    @default
      <span class="badge bg-secondary">{{ $assignment->status }}</span>
  @endswitch
</td>

					<td>
  <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Bu görevi silmek istediğinize emin misiniz?')" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">
      <i class="ti ti-trash"></i>
    </button>
  </form>
</td>

                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Sayfalama --}}
        <div class="mt-3">
          {{ $assignments->appends(request()->query())->links() }}
        </div>
      @else
        <p class="text-muted mb-0">Henüz bir görev atanmadı.</p>
      @endif
    </div>
  </div>
</div>
@endsection
