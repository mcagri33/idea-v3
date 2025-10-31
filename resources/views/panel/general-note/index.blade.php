@extends('layouts.layoutMaster')

@section('title', 'Kullanıcı Listesi')

@section('content')
  <h4 class="fw-bold py-3 mb-4">Kullanıcılar</h4>

  {{-- Arama Formu --}}
  <form method="GET" action="{{ route('general.note') }}" class="mb-3">
    <div class="input-group w-50">
      <input type="text" name="search" class="form-control" placeholder="İsim veya Email" value="{{ request('search') }}">
      <button class="btn btn-outline-secondary" type="submit">Ara</button>
    </div>
  </form>

  {{-- Kullanıcı Tablosu --}}
  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>Ad Soyad</th>
            <th>Email</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                <a href="{{ route('general.note.show', $user->id) }}" class="btn btn-sm btn-primary">Not ve Belgeleri Gör</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3">Kullanıcı bulunamadı.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Sayfalama --}}
    <div class="mt-3 mx-3">
      {{ $users->links() }}
    </div>
  </div>
@endsection
