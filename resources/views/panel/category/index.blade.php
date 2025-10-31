@extends('layouts/layoutMaster')

@section('title', 'Kategoriler')

@section('content')
  <h4 class="fw-bold py-3 mb-4">
    @yield('title')
  </h4>

  <div class="d-flex justify-content-between mb-3">
    <form action="#" method="GET" class="d-flex">
      @csrf
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Arama yap...">
        <button type="submit" class="btn btn-outline-primary"><i class="ti ti-search"></i></button>
      </div>
    </form>
  </div>


  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
        <tr>
          <th>#</th>
          <th>@lang('Name')</th>
          <th>@lang('Action')</th>
        </tr>
        </thead>
        <tbody class="table-border-bottom-0">
        <?php $count = 1; ?>
        @foreach($categories as $category)
          <tr>
            <td>{{ $categories->perPage() * ($categories->currentPage() - 1) + $count }}</td>
            <?php $count++; ?>
            <td>{{ $category->name }}</td>
            <td>
              <div class="dropdown">
                <a class="btn p-0 dropdown-toggle hide-arrow" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ti ti-dots-vertical"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                  <li><a class="dropdown-item" href="{{ route('castle.category.edit', $category->uuid) }}"><i class="ti ti-pencil me-1"></i> @lang('Edit')</a></li>
                  <li><a class="dropdown-item" href="{{ route('castle.category.delete', $category->uuid) }}"><i class="ti ti-trash me-1"></i> @lang('Delete')</a></li>
                </ul>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
      <div class="d-flex justify-content-center">
        {!! $categories->links() !!}
      </div>
    </div>
  </div>


@endsection
