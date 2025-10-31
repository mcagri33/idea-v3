@extends('layouts/layoutMaster')

@section('title', 'Kategori Düzenle')

@section('page-script')
  <script src="{{asset('assets/js/form-basic-inputs.js')}}"></script>
@endsection

@section('content')
  <div class="row">
    <div class="col-xl-12">
      <!-- HTML5 Inputs -->
      <div class="card mb-4">
        <h5 class="card-header"> @yield('title')</h5>
        <div class="card-body">
          <form action="{{route('castle.category.update',$category->uuid)}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3 row">
              <label for="html5-name-input" class="col-md-2 col-form-label">@lang('Name')</label>
              <div class="col-md-10">
                <input type="text" class="form-control"  name="name" value="{{$category->name}}" placeholder="Pasaport" />
                @error("name")
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>
            </div>

            <div class="mb-3 row">
              <label for="html5-name-input" class="col-md-2 col-form-label">@lang('Description')</label>
              <div class="col-md-10">
                <textarea class="form-control" name="description" id="exampleFormControlTextarea1" rows="3">{{$category->description}}</textarea>
                @error("description")
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>
            </div>

            <div class="mb-3 row">
              <label for="html5-name-input" class="col-md-2 col-form-label">@lang('File_Path')</label>
              <div class="col-md-10">
                <input type="file" class="form-control"  name="template_file"/>
                {{$category->template_file}}
                @error("template_file")
                <span class="text-danger">{{$message}}</span>
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
@endsection
