@extends('layouts/layoutMaster')

@section('title', 'Kullanıcı Evrakları')

@section('content')
<style>
  .table-fixed { table-layout: fixed; }
  .col-idx      { width: 40px; }
  .col-year     { width: 70px; }
  .col-status   { width: 120px; }
  .col-download { width: 90px; }
  .col-created  { width: 130px; }
  .col-delete   { width: 80px; }
  td form { display: flex; align-items: center; gap: .25rem; flex-wrap: nowrap; }
  .note-input { max-width: 180px; }
  .year-input { width: 65px; }
  .nowrap { white-space: nowrap; }
  .text-truncate { max-width: 120px; overflow: hidden; text-overflow: ellipsis; }

  /* Status select renkleri */
  .form-select.status-1 { /* Approved */
    background-color: #d1e7dd;  /* bs-success-subtle */
    color: #0f5132;             /* bs-success */
    border-color: #badbcc;
  }
  .form-select.status-0 { /* Rejected */
    background-color: #f8d7da;  /* bs-danger-subtle */
    color: #842029;             /* bs-danger */
    border-color: #f5c2c7;
  }
  .form-select.status-2 { /* Pending */
    background-color: #fff3cd;  /* bs-warning-subtle */
    color: #664d03;             /* bs-warning */
    border-color: #ffecb5;
  }
</style>

<div class="container">
  <h2 class="mb-4">{{ $user->name }} Kullanıcısının Evrakları</h2>

  {{-- Filtre Formu --}}
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">@lang('Filter Documents')</h5>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('castle.userDoc.show', $user->uuid) }}">
        <div class="row g-3">
          <div class="col-md-4">
            <label for="document_name" class="form-label">@lang('Document_Name')</label>
            <input type="text" name="document_name" id="document_name"
              value="{{ request('document_name') }}" class="form-control"
              placeholder="@lang('Document_Name')">
          </div>

          <div class="col-md-2">
            <label for="file_year" class="form-label">@lang('File_Year')</label>
            <input type="text" name="file_year" id="file_year"
              value="{{ request('file_year') }}" class="form-control" placeholder="YYYY">
          </div>

          <div class="col-md-3">
            <label for="category_id" class="form-label">@lang('Category')</label>
            <select name="category_id" id="category_id" class="form-select">
              <option value="">@lang('Select Category')</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label for="status" class="form-label">@lang('Status')</label>
            <select name="status" id="status" class="form-select">
              <option value="">@lang('Select Status')</option>
              <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>@lang('Pending')</option>
              <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>@lang('Approved')</option>
              <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>@lang('Rejected')</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary me-2">
              <i class="bx bx-filter-alt"></i> @lang('Filter')
            </button>
            <a href="{{ route('castle.userDoc.show', $user->uuid) }}" class="btn btn-outline-secondary">
              <i class="bx bx-reset"></i> @lang('Clear')
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Evrak Listesi --}}
  @if($documents->count() > 0)
    <div class="card">
      <div class="table-responsive text-nowrap">
        <table class="table table-sm align-middle table-fixed">
          <thead>
            <tr>
              <th>#</th>
              <th>@lang('File_Year')</th>
              <th class="d-none d-sm-table-cell">@lang('Document_Name')</th>
              <th class="d-none d-md-table-cell">@lang('Category')</th>
              <th>@lang('Status')</th>
              <th class="d-none d-lg-table-cell">@lang('Note')</th>
              <th>@lang('Download')</th>
              <th class="d-none d-md-table-cell">@lang('Created_At')</th>
              <th>@lang('Delete')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($documents as $document)
              <tr>
                <td class="nowrap">{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>

                {{-- YIL --}}
                <td class="nowrap">
                  <form action="{{ route('document.updateYear', $document->uuid) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="file_year" value="{{ $document->file_year }}"
                      class="form-control form-control-sm year-input" onchange="this.form.submit()">
                  </form>
                </td>

                {{-- DOKÜMAN ADI --}}
                <td class="d-none d-sm-table-cell text-truncate" title="{{ $document->document_name }}">
                  {{ $document->document_name }}
                </td>

                {{-- KATEGORİ --}}
                <td class="d-none d-md-table-cell text-truncate" title="{{ $document->category?->name ?? '-' }}">
                  {{ $document->category?->name ?? '-' }}
                </td>

                {{-- DURUM --}}
                <td class="nowrap">
                  <form action="{{ route('document.updateStatus', $document->uuid) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="status"
                      class="form-select form-select-sm status-{{ $document->status }}"
                      style="min-width: 100px"
                      onchange="this.form.submit()">
                      <option value="2" {{ $document->status == 2 ? 'selected' : '' }}>@lang('Pending')</option>
                      <option value="1" {{ $document->status == 1 ? 'selected' : '' }}>@lang('Approved')</option>
                      <option value="0" {{ $document->status == 0 ? 'selected' : '' }}>@lang('Rejected')</option>
                    </select>
                  </form>
                </td>

                {{-- NOT --}}
                <td class="d-none d-lg-table-cell">
                  <form action="{{ route('document.updateNote', $document->uuid) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="rejection_note"
                      class="form-control form-control-sm note-input"
                      value="{{ $document->rejection_note }}"
                      placeholder="@lang('Add note')" onchange="this.form.submit()">
                  </form>
                </td>

                {{-- İNDİR --}}
                <td class="nowrap">
                  <a href="{{ route('document.download', $document->uuid) }}" class="btn btn-success btn-sm" target="_blank">
                    @lang('Download')
                  </a>
                </td>

                {{-- TARİH --}}
                <td class="d-none d-md-table-cell nowrap">
                  {{ $document->created_at->format('d/m/Y H:i') }}
                </td>

                {{-- SİL --}}
                <td class="nowrap">
                  <form action="{{ route('document.file.destroy', $document->uuid) }}" method="POST"
                    onsubmit="return confirm('@lang('Evrak silinsin mi?')')">
                    @csrf
                    @method('DELETE')
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
    <div class="alert alert-info">
      Bu kullanıcıya ait evrak bulunmamaktadır.
    </div>
  @endif

  <a href="{{ route('user-doc') }}" class="btn btn-secondary mt-3">@lang('Back')</a>
</div>
@endsection
