@extends('layouts.layoutMaster')

@section('title', 'Rol Düzenle')

@section('page-script')
  <script src="{{ asset('assets/js/form-basic-inputs.js') }}"></script>
@endsection

@section('content')
  <div class="row">
    <div class="col-xl-12">
      <!-- HTML5 Inputs -->
      <div class="card mb-4">
        <h5 class="card-header"> @yield('title')</h5>
        <div class="card-body">
          <form action="{{ route('castle.role.update', $role->id) }}" method="post">
            @csrf
            <div class="mb-3 row">
              <label for="html5-name-input" class="col-md-2 col-form-label">@lang('Role_Name')</label>
              <div class="col-md-10">
                <input type="text" class="form-control" name="name" value="{{ old('name', $role->name) }}" placeholder="Admin" />
                @error("name")
                <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="mb-3 row">
              <label for="html5-name-input" class="col-md-2 col-form-label">@lang('Permission')</label>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="select-all">
                <label class="form-check-label" for="select-all">@lang('All')</label>
              </div>
              <div class="col-md-10">
                @foreach($permissions as $permission)
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input permission-checkbox" name="permission[]" value="{{ $permission->name }}" id="{{ $permission->name }}"
                           @if(in_array($permission->id, $rolePermissions)) checked @endif
                    >
                    <label class="form-check-label" for="{{ $permission->name }}">{{ $permission->name }}</label>
                  </div>
                @endforeach
                @error("permission")
                <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3 row">
              <div class="d-grid">
                <button class="btn btn-primary" type="submit">@lang('Update')</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const selectAllCheckbox = document.getElementById('select-all');
      const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

      selectAllCheckbox.addEventListener('change', function() {
        const isChecked = selectAllCheckbox.checked;
        permissionCheckboxes.forEach(checkbox => {
          checkbox.checked = isChecked;
        });
      });
    });
  </script>
@endsection
