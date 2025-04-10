{{-- resources/views/admin/pages/children/index.blade.php --}}
@extends('admin/layouts/base')

@section('title', 'Videos')
@section('content-area')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Lista de Videos</h6>
                                {{-- <p class="text-sm mb-0">Lista de Niños Registrados</p> --}}
                            </div>
                            <a href="" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i>Nuevos
                            </a>
                        </div>

                        {{-- Barra de búsqueda --}}
                        <div class="mt-3">
                            <form id="searchForm" method="GET" action="">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search"
                                        value="" placeholder="名前か電話番号で検索" id="searchInput">
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
                                            お子様名
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            年齢
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            保護者名
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            連絡先1
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            連絡先2
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            メールアドレス
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            アクション
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            代理予約
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        <div class="px-3 mt-4">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

