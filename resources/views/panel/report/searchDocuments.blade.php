@extends('layouts.layoutMaster')

@section('title', 'Belge Türüne Göre Arama Sonuçları')

@section('content')

  <style>
    /* Reduce the font size of the form labels, selects, and table content */
    label,
    select,
    table {
      font-size: 14px; /* Adjust as needed */
    }

    table th,
    table td {
      font-size: 13px; /* Adjust as needed */
    }
    th, td {
      white-space: nowrap;
    }

    .table-responsive {
      overflow-x: auto;
    }

    .search-company-form {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 1rem;
    }

    .search-company-form input {
      margin-right: 0.5rem;
    }
  </style>

  <h4 class="py-3 mb-4">Belge Türüne Göre Arama Sonuçları</h4>

  @if (session('success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
      <i class="ti ti-check-circle me-2"></i>
      <div>{{ session('success') }}</div>
    </div>
  @elseif (session('error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="ti ti-x-circle me-2"></i>
      <div>{{ session('error') }}</div>
    </div>
  @endif

  <form action="{{ route('documents.search.by.type') }}" method="GET" class="mb-4">
    @csrf
    <div class="form-group">
      <label for="document_type">Belge Türü Seçin:</label>
      <select name="document_type" class="form-control" required>
        <option value="">Belge Türü Seçin</option>
        <option value="Amortisman Calismasi" {{ old('document_type', $documentType ?? '') == 'Amortisman Calismasi' ? 'selected' : '' }}>Amortisman Çalışması</option>
        <option value="Avukat Yazisi" {{ old('document_type', $documentType ?? '') == 'Avukat Yazisi' ? 'selected' : '' }}>Avukat Yazısı</option>
        <option value="Cari Yil Kurumlar Vergisi Beyannamesi" {{ old('document_type', $documentType ?? '') == 'Cari Yil Kurumlar Vergisi Beyannamesi' ? 'selected' : '' }}>Cari Yıl Kurumlar Vergisi Beyannamesi</option>
        <option value="Cari Yil Mizan" {{ old('document_type', $documentType ?? '') == 'Cari Yil Mizan' ? 'selected' : '' }}>Cari Yıl Mizan</option>
        <option value="Diger" {{ old('document_type', $documentType ?? '') == 'Diger' ? 'selected' : '' }}>Diğer</option>
        <option value="Doviz Degerleme Tablosu" {{ old('document_type', $documentType ?? '') == 'Doviz Degerleme Tablosu' ? 'selected' : '' }}>Döviz Değerleme Tablosu</option>
        <option value="Finansal Tablo ve Faaliyet" {{ old('document_type', $documentType ?? '') == 'Finansal Tablo ve Faaliyet' ? 'selected' : '' }}>Finansal Tablo ve Faaliyet</option>
        <option value="Interaktiften Alinan Arac Listesi" {{ old('document_type', $documentType ?? '') == 'Interaktiften Alinan Arac Listesi' ? 'selected' : '' }}>Interaktiften Alınan Araç Listesi</option>
        <option value="Kasa Sayim Tutanagi" {{ old('document_type', $documentType ?? '') == 'Kasa Sayim Tutanagi' ? 'selected' : '' }}>Kasa Sayım Tutanağı</option>
        <option value="Kredi Odeme Planlari" {{ old('document_type', $documentType ?? '') == 'Kredi Odeme Planlari' ? 'selected' : '' }}>Kredi Ödeme Planları</option>
        <option value="Personel Listesi" {{ old('document_type', $documentType ?? '') == 'Personel Listesi' ? 'selected' : '' }}>Personel Listesi</option>
        <option value="Portfoydeki Cek Senet Sayim Tutanagi" {{ old('document_type', $documentType ?? '') == 'Portfoydeki Cek Senet Sayim Tutanagi' ? 'selected' : '' }}>Portföydeki Çek Senet Sayım Tutanağı</option>
        <option value="Satis Fatura Listesi" {{ old('document_type', $documentType ?? '') == 'Satis Fatura Listesi' ? 'selected' : '' }}>Satış Fatura Listesi</option>
        <option value="Sgk Borc Durumunu Gosteren Belge" {{ old('document_type', $documentType ?? '') == 'Sgk Borc Durumunu Gosteren Belge' ? 'selected' : '' }}>SGK Borç Durumunu Gösteren Belge</option>
        <option value="SirketKiraOdedigiYerlerinListesi" {{ old('document_type', $documentType ?? '') == 'SirketKiraOdedigiYerlerinListesi' ? 'selected' : '' }}>Şirket Kira Ödediği Yerlerin Listesi</option>
        <option value="Stok Sayim Tutanagi" {{ old('document_type', $documentType ?? '') == 'Stok Sayim Tutanagi' ? 'selected' : '' }}>Stok Sayım Tutanağı</option>
        <option value="Tapu Takyidat Yazisi" {{ old('document_type', $documentType ?? '') == 'Tapu Takyidat Yazisi' ? 'selected' : '' }}>Tapu Takyidat Yazısı</option>
        <option value="Vergi Dairesi Borc Durumunu Gosteren Belge" {{ old('document_type', $documentType ?? '') == 'Vergi Dairesi Borc Durumunu Gosteren Belge' ? 'selected' : '' }}>Vergi Dairesi Borç Durumunu Gösteren Belge</option>
        <option value="Yonetim Aciklama Sorumluluk Mektubu" {{ old('document_type', $documentType ?? '') == 'Yonetim Aciklama Sorumluluk Mektubu' ? 'selected' : '' }}>Yönetim Açıklama Sorumluluk Mektubu</option>
        <option value="Yonetim Kurulu Yıllık Faaliyet Raporu" {{ old('document_type', $documentType ?? '') == 'Yonetim Kurulu Yıllık Faaliyet Raporu' ? 'selected' : '' }}>Yönetim Kurulu Yıllık Faaliyet Raporu</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Ara</button>
  </form>

  <div class="search-company-form">
    <form action="{{ route('documents.search.by.company') }}" method="GET" class="d-flex">
      @csrf
      <input type="hidden" name="document_type" value="{{ request('document_type') }}">
      <input type="text" name="company_name" class="form-control me-2" placeholder="Firma adı girin" value="{{ request('company_name') }}">
      <button type="submit" class="btn btn-primary">Firma Ara</button>
    </form>
  </div>

  @if(isset($users))
    <div class="card mb-4">
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
          <tr>
            <th>#</th>
            <th>Firma Adı</th>
            <th>Kullanıcı Adı</th>
            <th>Belge Durumu</th>
          </tr>
          </thead>
          <tbody class="table-border-bottom-0">
          @foreach($users as $user)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $user->company }}</td>
              <td>{{ $user->name }}</td>
              <td>
                @if($user->documentUploaded)
                  <span class="badge bg-label-primary me-1">Yüklenmiş</span>
                @else
                  <span class="badge bg-label-danger me-1">Yüklenmemiş</span>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-center mt-3">
        {{ $users->appends(request()->query())->links() }}
      </div>
    </div>
  @endif

@endsection
