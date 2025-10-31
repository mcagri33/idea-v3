@extends('layouts/layoutMaster')

@section('title', 'Kategoriler Sıralama')

@section('vendor-style')
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <style>
    #sortable-categories li {
      cursor: move;
      padding: 10px;
      background-color: #f9f9f9;
      margin-bottom: 5px;
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: background-color 0.3s ease;
    }
    #sortable-categories li:hover {
      background-color: #f1f1f1;
    }
    .ui-state-highlight {
      background-color: #f0f0f0 !important;
      border: 2px dashed #ddd !important;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    toastr.options = {
      "closeButton": true,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "timeOut": "3000",
      "extendedTimeOut": "1000",
    };

    $(function () {
      $('#sortable-categories').sortable({
        update: function (event, ui) {
          let orderedIds = [];
          $('#sortable-categories li').each(function () {
            orderedIds.push($(this).data('id'));
          });

          $.ajax({
            url: '{{ route('castle.categories.updateOrder') }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              orderedIds: orderedIds
            },
            success: function (response) {
              toastr.success(response.message);
            },
            error: function () {
              toastr.error('Bir hata oluştu. Lütfen tekrar deneyin.');
            }
          });
        }
      });
    });
  </script>
@endsection

@section('content')
  <h4 class="fw-bold py-3 mb-4">
    @yield('title')
  </h4>

  <div class="card">
    <div class="table-responsive">
      <ul id="sortable-categories" class="list-group">
        @foreach($categories as $category)
          <li class="list-group-item" data-id="{{ $category->id }}">
            <i class="ti ti-drag-handle me-2"></i> {{ $category->name }}
          </li>
        @endforeach
      </ul>
    </div>
  </div>
@endsection
