@extends('layouts/layoutMaster')

@section('title', 'Kullanıcılar')

@section('content')
  <div class="container">
    <h2 class="mb-4">Kullanıcılar</h2>

    <div class="d-flex justify-content-between mb-3">
      <form action="{{ route('user-doc') }}" method="GET" class="d-flex">
        <!-- @csrf is optional for GET requests -->
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Arama yap..." value="{{ request('q') }}">
          <button type="submit" class="btn btn-outline-primary">
            <i class="ti ti-search"></i>
          </button>
        </div>
      </form>
    </div>

    @if($users->count() > 0)
      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
            <tr>
              <th>#</th>
              <th>Ad Soyad</th>
              <th>Email</th>
              <th>Oluşturulma Tarihi</th>
              <th>İşlem</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
              <tr>
                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                <td>
                  <a href="{{ route('castle.userDoc.show', $user->uuid) }}" class="btn btn-primary btn-sm">
                    Evrakları Görüntüle
                  </a>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            {!! $users->links() !!}
          </div>
        </div>
      </div>
    @else
      <div class="alert alert-info">
        Sistemde henüz kayıtlı kullanıcı bulunmamaktadır.
      </div>
    @endif
  </div>
@endsection
