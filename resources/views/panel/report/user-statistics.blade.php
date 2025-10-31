@php

    $allApproved = true;
       $anyUploaded = false;

       foreach ($documentStatusCounts as $counts) {
           if ($counts['Onay Bekliyor'] > 0 || $counts['Onaylandı'] > 0 || $counts['Reddedildi'] > 0) {
               $anyUploaded = true;
           }

           if ($counts['Onay Bekliyor'] > 0 || $counts['Reddedildi'] > 0) {
               $allApproved = false;
           }
       }

@endphp




@extends('layouts.layoutMaster')

@section('title', 'Müşteri Denetim İlerleme Durum Raporu')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        Müşteri Denetim İlerleme Durum Raporu - {{ $user->name }}
    </h4>
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri
        </a>
    </div>
  <div class="card">
    <div class="card-body">
        @if ($anyUploaded && $allApproved)
            <div class="alert alert-success" role="alert">
                Yüklediğiniz dokümanlar kontrol aşamasındadır. Kontrol sonrası bağımsız denetçi raporu yazılacaktır.            </div>
        @elseif (!$anyUploaded)
            <div class="alert alert-danger" role="alert">
                Bağımsız denetçi raporu yazım aşamasına geçilebilmesi için tüm dokümanların yüklenmesini rica ediyoruz.            </div>
        @else
            <div class="alert alert-danger" role="alert">
                Bağımsız denetçi raporu yazım aşamasına geçilebilmesi için tüm dokümanların yüklenmesini rica ediyoruz.            </div>
        @endif
      <ul class="list-group">
        @foreach ($documentTypes as $type)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ $type }}:</span>
            <div>
              @php
                $counts = $documentStatusCounts[$type];
              @endphp
              @if ($counts['Onay Bekliyor'] > 0)
                <span class="badge bg-warning text-dark">{{ $counts['Onay Bekliyor'] }} Onay Bekliyor</span>
              @endif
              @if ($counts['Onaylandı'] > 0)
                <span class="badge bg-success">{{ $counts['Onaylandı'] }} Onaylandı</span>
              @endif
              @if ($counts['Reddedildi'] > 0)
                <span class="badge bg-danger">{{ $counts['Reddedildi'] }} Reddedildi</span>
              @endif
              @if ($counts['Onay Bekliyor'] == 0 && $counts['Onaylandı'] == 0 && $counts['Reddedildi'] == 0)
                <span class="badge bg-secondary">Yüklenmedi</span>
              @endif
            </div>
          </li>
        @endforeach
      </ul>

      <div class="mt-3">
        <h5>Toplam Yüklenen Belge Sayısı: {{ $uploadedDocumentCount }}</h5>
        <h5>Yükleme Yüzdesi: {{ $uploadPercentage }}%</h5>
      </div>
    </div>
  </div>
@endsection
