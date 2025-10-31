@extends('layouts.layoutMaster')

@section('title', 'Evrak Yükle')

@section('vendor-style')
  <!-- Dropzone CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" />
@endsection

@section('vendor-script')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
@endsection

@section('page-script')
  <script>
    Dropzone.autoDiscover = false;

    let myDropzone = new Dropzone("div#document-dropzone", {
      url: '{{ route('file.upload') }}',
      autoProcessQueue: true,
      uploadMultiple: true,
      addRemoveLinks: true,
      parallelUploads: 10,
      maxFilesize: 400,
      acceptedFiles: '.pdf,.xls,.doc,.docx,.zip,.xlsx,.xml,.rar,.jpeg,.png,.jpg,.tif,.udf',
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      dictDefaultMessage: "Buraya dosyalarınızı bırakın veya tıklayarak yükleyin. (Max: 10 Dosya, Max: 400 MB)",
      dictFallbackMessage: "Tarayıcınız sürükleyip bırakmayı desteklemiyor.",
      dictInvalidFileType: "Bu tür dosyayı yüklemeye izin verilmiyor.",
      dictFileTooBig: "Dosya çok büyük.",
      dictResponseError: "Sunucu yanıt hatası.",
      dictCancelUpload: "Yüklemeyi iptal et",
      dictUploadCanceled: "Yükleme iptal edildi.",
      dictCancelUploadConfirmation: "Yüklemeyi iptal etmek istediğinizden emin misiniz?",
      dictRemoveFile: "Dosyayı kaldır",
      dictMaxFilesExceeded: "Daha fazla dosya yükleyemezsiniz.",
      successmultiple: function (files, response) {
        $.each(response.name, function (key, val) {
          $('form').append('<input type="hidden" name="images[]" value="' + val + '">');
        });
      },
      removedfile: function (file) {
        let name = file.name;
        $('form').find('input[name="images[]"][value="' + name + '"]').remove();
        file.previewElement.remove();
      }
    });
  </script>
@endsection

@section('content')
  <h4 class="py-3 mb-4">
    Kullanıcıya Evrak Yükle
  </h4>

  @if (session('success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
      <i class="ti ti-check-circle me-2"></i>
      <div>
        {{ session('success') }}
      </div>
    </div>
  @elseif (session('error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="ti ti-x-circle me-2"></i>
      <div>
        {{ session('error') }}
      </div>
    </div>
  @endif

  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <h5 class="card-header">Dosya Yükle</h5>
        <div class="card-body">
          <form id="dropzone-form" action="{{ route('castle.user.document.upload.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="form-group mt-3">
              <label for="user_id">Kullanıcı Seçin:</label>
              <select name="user_id" class="form-control" required>
                @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->company }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mt-3">
              <label for="document_name">Dosya Adı:</label>
              <input type="text" name="document_name" class="form-control" placeholder="Dosya Adı" required>
            </div>
            <div class="form-group mt-3">
              <label for="description">Açıklama:</label>
              <textarea name="description" class="form-control" placeholder="Açıklama" required></textarea>
            </div>

            <div class="form-group mt-3">
              <div class="col-md-12">
                <div class="position-relative form-group">
                  <div class="needsclick dropzone" id="document-dropzone"></div>
                </div>
              </div>
            </div>

            <button class="btn btn-primary mt-3" type="submit">Yükle</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
