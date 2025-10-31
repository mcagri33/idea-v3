@extends('layouts/layoutMaster')

@section('title', 'Kullanıcı Evrak Durumu')

@section('content')
<div class="container">
    <h2 class="mb-4">Kullanıcı Bazlı Belge Durumu</h2>

    <form method="GET" action="{{ route('documents.status') }}" class="row g-3 mb-4">
     <div class="col-md-4">
    <label for="user_id" class="form-label">Kullanıcı</label>
    <select name="user_id" id="user_id" class="form-control select2">
        <option value="">-- Kullanıcı Seçin --</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @if($user->id == $userId) selected @endif>
                {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
	</div>


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
                <th>Yükleme Durumu</th>
				<th>Aciklama </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $i => $category)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <strong>{{ $category->total_uploaded }}</strong> dosya<br>
                        ✅ Onay: {{ $category->approved_count }}<br>
                        ❌ Red: {{ $category->rejected_count }}<br>
                        ⏳ Beklemede: {{ $category->pending_count }}
                    </td>
					<td>
						<form method="POST" action="{{ route('documents.status.note.update') }}" class="mt-2">
                @csrf
                <input type="hidden" name="user_id" value="{{ $userId }}">
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <textarea name="note" class="form-control mb-2" rows="2" placeholder="Not girin...">{{ $category->notes->first()->note ?? '' }}</textarea>
                <button type="submit" class="btn btn-sm btn-success">Kaydet</button>
            </form>
					</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
