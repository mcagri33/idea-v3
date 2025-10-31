@php use Illuminate\Support\Str; @endphp



@extends('layouts/layoutMaster')

@section('title', 'Kullanıcı Hareketleri')

@section('content')
@can('admin-log')
  <h4 class="fw-bold py-3 mb-4">
    @yield('title')
  </h4>

  <div class="col-12">
    <div class="card mb-4">
      <h5 class="card-header">@yield('title')</h5>

      <div class="card-body">

        {{-- Export & Filter Buttons --}}
        <div class="d-flex justify-content-between mb-3">
          <button class="btn btn-success" onclick="window.location='{{ route('castle.admin.log.export.excel') }}'">
            Excel Olarak İndir
          </button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            Filtrele
          </button>
        </div>

        {{-- Log Table --}}
        <div class="card">
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Firma</th>
                  <th>Aksiyon</th>
                  <th>Tür</th>
                  <th>Açıklama</th>
                  <th>Eklenme</th>
                  <th>Onaylanma</th>
                </tr>
              </thead>
              <tbody>
              @php $count = 1; @endphp
              @foreach($logs as $log)
                <tr>
                  <td>{{ $logs->perPage() * ($logs->currentPage() - 1) + $count++ }}</td>
                  <td>{{ Str::limit($log->company_name, 20) }}</td>
                  <td>
                    <span class="fw-bold text-{{ in_array($log->action_type, ['Eklendi', 'Onaylandı']) ? 'success' : 'danger' }}">
                      {{ $log->action_type }}
                    </span>
                  </td>
                  <td>{{ $log->model_type }}</td>
                  <td class="text-wrap" style="max-width: 250px;">
                    {{ Str::limit($log->description, 1000) }}
                  </td>
                  <td>{{ $log->file_created_at ? \Carbon\Carbon::parse($log->file_created_at)->format('d/m/Y') : '-' }}</td>
                  <td>{{ $log->approved_at ? \Carbon\Carbon::parse($log->approved_at)->format('d/m/Y') : '-' }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer d-flex justify-content-center">
            {!! $logs->links() !!}
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Filter Modal --}}
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Filtrele</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="GET" action="{{ route('admin-log') }}" class="row g-3">
            <div class="col-md-3">
              <label for="start_date" class="form-label">Başlangıç</label>
              <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
            </div>

            <div class="col-md-3">
              <label for="end_date" class="form-label">Bitiş</label>
              <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-3">
              <label for="model_type" class="form-label">Dosya Türü</label>
              <select class="form-select" id="model_type" name="model_type">
                <option value="">Tümü</option>
                @foreach($uniqueFileTypes as $type)
                  <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                    {{ $type }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label for="action_type" class="form-label">Aksiyon</label>
              <select class="form-select" id="action_type" name="action_type">
                <option value="">Tümü</option>
                @foreach($uniqueActionTypes as $actionType)
                  <option value="{{ $actionType }}" {{ request('action_type') == $actionType ? 'selected' : '' }}>
                    {{ $actionType }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="company_name" class="form-label">Firma Adı</label>
              <input type="text" class="form-control" id="company_name" name="company_name" value="{{ request('company_name') }}">
            </div>

            <div class="col-md-6 d-flex align-items-end justify-content-end">
              <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Optional Custom CSS --}}
  <style>
    .modal .form-label {
      font-weight: 500;
    }
    .table td, .table th {
      white-space: nowrap;
    }
  </style>
@endcan
@endsection
