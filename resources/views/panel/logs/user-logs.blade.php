@extends('layouts/layoutMaster')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Kullanıcı Hareketleri')

@section('content')
    @can('user-log')

        <h4 class="fw-bold py-3 mb-4">
            @yield('title')
        </h4>
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">
                    @yield('title')
                </h5>
                <div class="card-body">

                    <form method="GET" action="{{ route('user-log') }}" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Filtrele</button>
                        </div>
                    </form>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-sm table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Firma Adı</th>
                                    <th>Aksiyon Türü</th>
                                    <th>Dosya Türü</th>
                                    <th>Açıklama</th>
                                    <th>Dosya Eklenme Tarihi</th>
                                    <th>Dosya Onaylanma Tarihi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @php $count = 1; @endphp
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $logs->perPage() * ($logs->currentPage() - 1) + $count++ }}</td>
                                        <td>{{ Str::limit($log->company_name, 20) }}</td>
                                        <td>
                                            <span style="color: {{ in_array($log->action_type, ['Eklendi', 'Onaylandı']) ? 'green' : 'red' }};">
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
                        <div class="d-flex justify-content-center">
                            {!! $logs->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endcan
@endsection

<style>
    /* Metni hücre içinde aşağı indirmek için */
    .table td.text-wrap {
        white-space: normal;
        word-break: break-word;
    }
</style>
