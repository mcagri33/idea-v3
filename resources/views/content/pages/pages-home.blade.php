@extends('layouts.layoutMaster')

@section('title', 'Denetim İlerleme Durumu')

@section('content')
  @role('Customer')

  <h4 class="fw-bold py-3 mb-4">@yield('title')</h4>

  {{-- Yıl seçimi --}}
  <div class="d-flex justify-content-between mb-3">
    <form action="{{ url()->current() }}" method="GET" class="d-flex">
      <div class="input-group">
        <label class="me-2" for="year">Yıl:</label>
        <select name="year" id="year" class="form-select" onchange="this.form.submit()">
          @foreach (range(now()->year - 1, now()->year - 11) as $yr)
            <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>

@if($note)
  <div class="alert alert-info">
    <strong>{{ $year }} Yılı Yönetici Notu:</strong><br>
    {!! $note->note !!}
  </div>
@endif



{{-- 
  @foreach ($categories as $category)
    @php
      $total = $category->approved_count + $category->rejected_count + $category->pending_count;
      $isIncomplete = $total > 0 && ($category->rejected_count > 0 || $category->pending_count > 0);
      $isMissing = $total === 0;
    @endphp

    @if ($isIncomplete)
      <div class="alert alert-warning">
        <strong>
          {{ $year }} yılı için <b>{{ $category->name }}</b> kategorisindeki belgelerin tamamı onaylı değil.
          Lütfen eksik veya bekleyen belgelerinizi yükleyin.
        </strong>
      </div>
    @elseif ($isMissing)
      <div class="alert alert-danger">
        <strong>
          {{ $year }} yılı için <b>{{ $category->name }}</b> kategorisine henüz hiç belge yüklenmemiş.
          Lütfen belgelerinizi yükleyiniz.
        </strong>
      </div>
    @endif
  @endforeach
--}}
  {{-- Kategori Tablosu --}}
  <div class="card mt-4">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
        <tr>
          <th>@lang('Yüklenen Denetim Evrakları')</th>
          <th>@lang('Approved')</th>
          <th>@lang('Red Edildi (Güncelleyiniz)')</th>
          <th>@lang('Yüklenen (Beklemede)')</th>
          <th>@lang('Action')</th>
        </tr>
        </thead>
        <tbody class="table-border-bottom-0">
        @foreach($categories as $category)
          <tr>
            <td>
              <a href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>
            </td>
            <td><span class="badge bg-label-success">{{ $category->approved_count }}</span></td>
            <td><span class="badge bg-label-danger">{{ $category->rejected_count }}</span></td>
            <td><span class="badge bg-label-warning">{{ $category->pending_count }}</span></td>
            <td>
              <div class="dropdown">
                <a class="btn p-0 dropdown-toggle hide-arrow" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="ti ti-dots-vertical"></i>
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item" href="{{ route('category.show', $category->slug) }}">
                      <i class="ti ti-eye me-1"></i> @lang('View_Details')
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  @endrole
@endsection
