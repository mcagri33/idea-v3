@extends('layouts.layoutMaster')

@section('title', 'Kullanıcı Seçimi')

@section('content')
  <h4 class="fw-bold py-3 mb-4">Kullanıcı Seçimi</h4>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('user-statistics') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label for="user_id" class="form-label">Kullanıcı Seçin:</label>
          <select id="user_id" name="user_id" class="form-select" required>
            <option value="">Kullanıcı seçiniz</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-primary">İstatistikleri Görüntüle</button>
      </form>
    </div>
  </div>
@endsection
