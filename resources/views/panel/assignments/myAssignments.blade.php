@extends('layouts.layoutMaster')

@section('title', 'Denetim İlave Belge İstekleri')

@section('content')
<div class="container py-4">
  <h4 class="mb-4">📄 Denetim İlave Belge İstekleri</h4>

  @if($assignments->count())
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Başlık</th>
            <th>Kategori</th>
            <th>Açıklama</th>
            <th>Son Tarih</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assignments as $assignment)
            <tr>
              <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
              <td>{{ $assignment->title }}</td>
              <td>{{ $assignment->category->name ?? '-' }}</td>
              <td>{{ $assignment->description ?? '-' }}</td>
              <td>{{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('d.m.Y') : '-' }}</td>
              <td>
                @switch($assignment->status)
                  @case('pending') <span class="badge bg-warning">Bekliyor</span> @break
                  @case('uploaded') <span class="badge bg-info">Yüklendi</span> @break
                  @case('approved') <span class="badge bg-success">Onaylandı</span> @break
                  @case('rejected') <span class="badge bg-danger">Reddedildi</span> @break
                  @default <span class="badge bg-secondary">Bilinmiyor</span>
                @endswitch
              </td>
               <td>
                @if(!$assignment->document)
                  <a href="{{ route('category.show', [$assignment->category, 'assignment_id' => $assignment->id]) }}" class="btn btn-sm btn-primary">
                    Yükle
                  </a>       
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="mt-3">
        {!! $assignments->links() !!}
      </div>
    </div>
  @else
    <div class="alert alert-info">Henüz size atanmış bir istek bulunmamaktadır.</div>
  @endif

</div>
@endsection
