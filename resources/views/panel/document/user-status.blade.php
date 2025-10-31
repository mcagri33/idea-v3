@extends('layouts/layoutMaster')
@section('title', 'Evrak Durumum')

@section('content')
<div class="container">
    <h2 class="mb-4">Evrak Durumunuz</h2>

    <form method="GET" action="{{ route('user.documents.status') }}" class="row g-3 mb-4">
        <div class="col-md-2">
            <label for="year" class="form-label">Yıl</label>
            <select name="year" id="year" class="form-control">
                @php
                    $currentYear = now()->year;
                    $startYear = $currentYear - 10;
                @endphp
                @for ($y = $currentYear; $y >= $startYear; $y--)
                    <option value="{{ $y }}" @if ($y == $year) selected @endif>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filtrele</button>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Belge Kategorisi</th>
                <th>Durum</th>
				<th>Aciklama </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $i => $category)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $category->name }}</strong><br>
                    </td>
                    <td>
                        <strong>{{ $category->total_uploaded }}</strong> dosya<br>
                        ✅ Onay: {{ $category->approved_count }}<br>
                        ❌ Red: {{ $category->rejected_count }}<br>
                        ⏳ Beklemede: {{ $category->pending_count }}
                    </td>
					<td> {{ optional($category->notes->first())->note ?? 'Not belirtilmemiş' }} </>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
