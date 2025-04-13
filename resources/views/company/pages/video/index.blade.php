{{-- resources/views/admin/pages/videos/index.blade.php --}}
@extends('company/layouts/base')

@section('title', 'Videos')
@section('content-area')
    @include('company.pages.video.form')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Lista de Videos</h6>
                                {{-- <p class="text-sm mb-0">Lista de Videos Registrados</p> --}}
                            </div>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#uploadVideoModal">
                                <i class="fas fa-plus me-2"></i>Nuevo Video
                            </button>
                        </div>

                        {{-- Barra de búsqueda --}}
                        <div class="mt-3">
                            <form id="searchForm" method="GET" action="{{ route('company.video.index') }}">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request('search') }}" placeholder="Buscar por título" id="searchInput">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Video
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Título
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Link
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Vistas
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Me gusta
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Fecha
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($videos ?? [] as $video)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div
                                                        style="width: 80px; height: 45px; overflow: hidden; border-radius: 5px; background-color: #000;">
                                                        <a href="">
                                                            <img src="{{ $video->thumbnailUrl }}"
                                                                class="w-100 h-100 object-fit-cover"
                                                                alt="{{ $video->title }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ Str::limit($video->title, 40) }}
                                                </p>
                                                <small class="text-muted">{{ Str::limit($video->description, 60) }}</small>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $video->link }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">
                                                    <i class="fas fa-eye text-info me-1"></i> {{ $video->views }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">
                                                    <i class="fas fa-heart text-danger me-1"></i> {{ $video->likes }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $video->created_at->format('d/m/Y') }}</p>
                                                <p class="text-xxs text-muted mb-0">{{ $video->timeAgo }}</p>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="" class="btn btn-link text-primary btn-sm p-1">
                                                        <i class="fas fa-edit text-primary"></i>
                                                    </a>
                                                    <a href="" class="btn btn-link text-info btn-sm p-1">
                                                        <i class="fas fa-play-circle text-info"></i>
                                                    </a>
                                                    <form action="" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger btn-sm p-1"
                                                            onclick="return confirm('¿Estás seguro de eliminar este video?')">
                                                            <i class="fas fa-trash text-danger"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="text-sm mb-0">No hay videos disponibles</p>
                                                <p class="text-xs text-secondary">Comienza subiendo un nuevo video</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        <div class="px-3 mt-4">
                            {{ $videos->links() ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
