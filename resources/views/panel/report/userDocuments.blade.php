@extends('layouts.layoutMaster')

@section('title', 'Bağımsız Denetçi Raporları')

@section('content')
  <h4 class="fw-bold py-3 mb-4">
    @yield('title')
  </h4>

  @if (session('success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
      <i class="ti ti-check-circle me-2"></i>
      <div>
        {{ session('success') }}
      </div>
    </div>
  @elseif (session('error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="ti ti-x-circle me-2"></i>
      <div>
        {{ session('error') }}
      </div>
    </div>
  @endif

  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
        <tr>
          <th>#</th>
          <th>Belge Adı</th>
          <th>Açıklama</th>
          <th>Oluşturulma Tarihi</th>
          <th>İndir</th>
        </tr>
        </thead>
        <tbody class="table-border-bottom-0">
        <?php $count = 1; ?>
        @foreach($documents as $document)
          <tr>
            <td>{{ $documents->perPage() * ($documents->currentPage() - 1) + $count }}</td>
              <?php $count++; ?>
            <td>{{ $document->document_name }}</td>
            <td>{{ $document->description }}</td>
            <td>{{ $document->created_at }}</td>
            <td>
              <a href="{{ route('castle.user.document.download', ['id' => $document->id]) }}" class="btn btn-primary">Dosyayı İndir</a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
      {!! $documents->links() !!}
    </div>
  </div>

@endsection
