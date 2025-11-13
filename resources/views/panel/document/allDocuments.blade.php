@extends('layouts/layoutMaster')
@section('title', __('Tum Evraklari Izleme'))

@section('content')
<style>
  .table-fixed{table-layout:fixed}
  .col-idx{width:40px}.col-year{width:70px}.col-status{width:120px}.col-download{width:90px}.col-created{width:130px}.col-delete{width:80px}
  td form{display:flex;align-items:center;gap:.25rem;flex-wrap:nowrap}
  .note-input{max-width:180px}.year-input{width:65px}.nowrap{white-space:nowrap}
  .text-truncate{max-width:160px;overflow:hidden;text-overflow:ellipsis}
  .form-select.status-1{background:#d1e7dd;color:#0f5132;border-color:#badbcc}
  .form-select.status-0{background:#f8d7da;color:#842029;border-color:#f5c2c7}
  .form-select.status-2{background:#fff3cd;color:#664d03;border-color:#ffecb5}
</style>

<div class="container">
  <h2 class="mb-4">@lang('Tum Evraklari Izleme')</h2>

  {{-- FİLTRELER: user + yıl + kategori + durum + isim --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('document.check') }}">
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label">@lang('User')</label>
            <select name="user_id" class="form-select">
              <option value="">@lang('Select User')</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">@lang('File_Year')</label>
            <input class="form-control" name="file_year" value="{{ request('file_year') }}" placeholder="YYYY">
          </div>

          <div class="col-md-2">
            <label class="form-label">@lang('Category')</label>
            <select name="category_id" class="form-select">
              <option value="">@lang('Select Category')</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">@lang('Status')</label>
            <select name="status" class="form-select">
              <option value="">@lang('Select Status')</option>
              <option value="2" @selected(request('status')==='2')>@lang('Pending')</option>
              <option value="1" @selected(request('status')==='1')>@lang('Approved')</option>
              <option value="0" @selected(request('status')==='0')>@lang('Rejected')</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">@lang('Document_Name')</label>
            <input class="form-control" name="document_name" value="{{ request('document_name') }}" placeholder="@lang('Document_Name')">
          </div>

          <div class="col-12 text-end mt-2">
            <button class="btn btn-primary">@lang('Filter')</button>
            <a class="btn btn-outline-secondary" href="{{ route('document.check') }}">@lang('Clear')</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if($documents->count())
    <div class="card">
      <div class="table-responsive text-nowrap">
        <table class="table table-sm align-middle table-fixed">
          <thead>
            <tr>
              <th class="col-idx">#</th>
              <th>@lang('User')</th>
              <th class="d-none d-sm-table-cell">@lang('File_Year')</th>
              <th class="d-none d-lg-table-cell">@lang('Document_Name')</th>
              <th class="d-none d-md-table-cell">@lang('Category')</th>
              <th class="col-status">@lang('Status')</th>
              <th class="d-none d-lg-table-cell">@lang('Note')</th>
              <th class="col-download">@lang('Download')</th>
              <th class="d-none d-md-table-cell">@lang('Downloaded_By')</th>
              <th class="d-none d-md-table-cell col-created">@lang('Created_At')</th>
              <th class="col-delete">@lang('Delete')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($documents as $document)
              <tr>
                <td class="nowrap">{{ $loop->iteration + ($documents->currentPage()-1)*$documents->perPage() }}</td>

                {{-- User --}}
                <td class="text-truncate" title="{{ $document->user?->name ?? '-' }}">
                  {{ $document->user?->name ?? '-' }}
                </td>

                {{-- Year --}}
                <td class="d-none d-sm-table-cell nowrap">
                  <form action="{{ route('document.updateYear', $document->uuid) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="text" name="file_year" value="{{ $document->file_year }}"
                           class="form-control form-control-sm year-input" onchange="this.form.submit()">
                  </form>
                </td>

                {{-- Document name --}}
                <td class="d-none d-lg-table-cell text-truncate" title="{{ $document->document_name }}">
                  {{ $document->document_name }}
                </td>

                {{-- Category --}}
                <td class="d-none d-md-table-cell text-truncate" title="{{ $document->category?->name ?? '-' }}">
                  {{ $document->category?->name ?? '-' }}
                </td>

                {{-- Status --}}
                <td class="nowrap">
                  <form action="{{ route('document.updateStatus', $document->uuid) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm status-{{ $document->status }}" onchange="this.form.submit()">
                      <option value="2" @selected($document->status==2)>@lang('Pending')</option>
                      <option value="1" @selected($document->status==1)>@lang('Approved')</option>
                      <option value="0" @selected($document->status==0)>@lang('Rejected')</option>
                      <option value="3" @selected($document->status==3)>Belge Yok</option>
                    </select>
                  </form>
                </td>

                {{-- Note --}}
                <td class="d-none d-lg-table-cell">
                  <form action="{{ route('document.updateNote', $document->uuid) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="text" name="rejection_note" class="form-control form-control-sm note-input"
                           value="{{ $document->rejection_note }}" placeholder="@lang('Add note')"
                           onchange="this.form.submit()">
                  </form>
                </td>

                {{-- Download --}}
                <td class="nowrap">
                  @if($document->status == 3)
                    <span class="text-muted small">-</span>
                  @else
                    <a href="{{ route('document.download', $document->uuid) }}" class="btn btn-success btn-sm" target="_blank">
                      @lang('Download')
                    </a>
                  @endif
                </td>

                {{-- Downloaded By --}}
                <td class="d-none d-md-table-cell nowrap">
                  @if($document->lastDownloadLog && $document->lastDownloadLog->performedBy)
                    <span class="text-muted small">
                      {{ $document->lastDownloadLog->performedBy->name }}
                      <br>
                      <small>{{ $document->lastDownloadLog->created_at->format('d/m/Y H:i') }}</small>
                    </span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>

                {{-- Created at --}}
                <td class="d-none d-md-table-cell nowrap">
                  {{ $document->created_at->format('d/m/Y H:i') }}
                </td>

                {{-- Delete --}}
                <td class="nowrap">
                  <form action="{{ route('document.file.destroy', $document->uuid) }}" method="POST"
                        onsubmit="return confirm('@lang('Evrak silinsin mi?')')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="ti ti-trash"></i> @lang('Delete')
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
          {!! $documents->links() !!}
        </div>
      </div>
    </div>
  @else
    <div class="alert alert-info mb-0">@lang('No documents found.')</div>
  @endif
</div>
@endsection
