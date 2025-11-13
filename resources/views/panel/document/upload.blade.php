@extends('layouts/layoutMaster')

@section('title', 'Evrak Yükle')

@section('vendor-style')
  <!-- Dropzone CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" />

  <style>
    .nav-tabs .nav-link {
      color: #555;
      font-weight: bold;
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      margin-right: 5px;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
      color: #fff;
      background-color: #007bff;
      border-color: #007bff;
    }

    .nav-tabs .nav-link:hover {
      background-color: #e9ecef;
      border-color: #ddd;
      color: #007bff;
    }
  </style>

@endsection

@section('vendor-script')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
@endsection

@section('page-script')
  <script>
    function setYear(year) {
      // Tüm tab'ları gizle
      $('.tab-pane').removeClass('show active');

      // Seçilen tab'ı göster
      $('#tab' + year).addClass('show active');

      // Tüm tab bağlantılarını aktif olmaktan çıkar
      $('.nav-link').removeClass('active');

      // Seçilen tab bağlantısını aktif yap
      $('.nav-link').filter(function() {
        return $(this).text() === year;
      }).addClass('active');

      // Dropzone'u yeniden başlat
      initializeDropzone('#tab' + year + ' #document-dropzone');
    }

    function initializeDropzone(selector) {
      let dropzoneElement = document.querySelector(selector);

      if (dropzoneElement.dropzone) {
        dropzoneElement.dropzone.destroy(); // Varolan Dropzone'u yok et
      }

      let myDropzone = new Dropzone(selector, {
        url: '{{ route('file.upload') }}',
        autoProcessQueue: true,
        uploadMultiple: true,
        addRemoveLinks: true,
        parallelUploads: 20,
        maxFilesize: 400,
        acceptedFiles: '.pdf,.xls,.doc,.docx,.zip,.xlsx,.xml,.rar,.jpeg,.png,.jpg,.tif,.udf',
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        dictDefaultMessage: "Buraya dosyalarınızı bırakın veya tıklayarak yükleyin. (Max: 20 Dosya, Max: 400 MB)",
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
    }

    // Sayfa yüklendiğinde başlangıç sekmesini başlat
    $(document).ready(function() {
      initializeDropzone('#tab{{ date('Y') }} #document-dropzone');
    });

  </script>
@endsection

@section('content')

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

  <div class="mb-4 border rounded p-3 bg-light">
    <h5>Not:</h5>
    {{$desc}}
  </div>

  @if($category->template_file)
    <div class="mb-4">
      <a href="{{ route('category.template.download', $category->uuid) }}" class="btn btn-primary">
        <i class="ti ti-download"></i> Şablonu İndir
      </a>
    </div>
  @endif


  <div class="d-flex justify-content-between mb-3">

  </div>


  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <h5 class="card-header">Dosya Yükle</h5>
        <div class="card-body">
          <div class="mb-3">
            <ul class="nav nav-tabs">
              @for ($year = 2021; $year <= date('Y'); $year++)
                <li class="nav-item">
                  <a class="nav-link {{ $year == date('Y') ? 'active' : '' }}" href="#" onclick="setYear('{{ $year }}')">{{ $year }}</a>
                </li>
              @endfor
            </ul>
          </div>

          <div class="tab-content">
            @for ($year = 2021; $year <= date('Y'); $year++)
              <div class="tab-pane fade {{ $year == date('Y') ? 'show active' : '' }}" id="tab{{ $year }}">
                <form id="dropzone-form" action="{{ route('category.upload',$category->slug) }}" method="post" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="file_year" value="{{ $year }}">
                  <input type="hidden" name="assignment_id" value="{{ request('assignment_id') }}">

                  <div class="form-row">
                    <div class="col-md-12">
                      <div class="position-relative form-group">
                        <div class="needsclick dropzone" id="document-dropzone"></div>
                      </div>
                    </div>
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
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="no_document" id="no_document_{{ $year }}" value="1">
                      <label class="form-check-label" for="no_document_{{ $year }}">
                        Bu kategori için belgemiz yok
                      </label>
                    </div>
                    <small class="form-text text-muted">Eğer bu kategori için belgeniz yoksa lütfen bu seçeneği işaretleyin.</small>
                  </div>

                  <button class="btn btn-primary mt-3" type="submit">Yükle</button>
                </form>
              </div>
            @endfor
          </div>
        </div>
      </div>
    </div>
  </div>

    <hr>

    <h3>Yüklenen Evraklar</h3>
    <div class="card">
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
          <tr>
            <th>#</th>
            <th>Dosya Yılı</th>
            <th>Belge Adı</th>
            <th>Status</th>
            <th>Admin Notu</th>
            <th>İndir</th>
            <th>Yüklenme Tarihi</th>
          </tr>
          </thead>
          <tbody class="table-border-bottom-0">
          <?php $count = 1; ?>
          @foreach($documents as $document)
            <tr>
              <td>{{ $documents->perPage() * ($documents->currentPage() - 1) + $count }}</td>
                <?php $count++; ?>
              <td>{{$document->file_year }}</td>
              <td>{{ $document->document_name }}</td>
              <td>
                @if ($document->status == 2)
                  <span class="badge bg-warning text-dark">Beklemede</span>
                @elseif ($document->status == 1)
                  <span class="badge bg-success">Onaylandı</span>
                @elseif ($document->status == 0)
                  <span class="badge bg-danger">Reddedildi</span>
                @elseif ($document->status == 3)
                  <span class="badge bg-info">Belge Yok</span>
                @endif
              </td>

              <td>{{ $document->rejection_note ?? 'Yok' }}</td>
              <td>
                <a href="{{ route('document.download', $document->uuid) }}" class="btn btn-success" target="_blank">
                  İndir
                </a>
              </td>
              <td>{{ $document->created_at->format('d/m/Y') }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
        <div class="d-flex justify-content-center">
          {!! $documents->links() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
