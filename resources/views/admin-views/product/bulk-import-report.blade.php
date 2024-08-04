@extends('layouts.back-end.app')

@section('title', translate('product_Bulk_import_report'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-1 text-capitalize d-flex gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/bulk-import.png')}}" alt="">
                {{translate('new_bulk_import_report')}}
            </h2>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr class="bg-primary text-light">
                            <th>Sr</th>
                            <th>Error</th>
                            <th>Product</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($res as $re)
                        <tr>
                        <td>{{ $loop->index+1 }}</td>
                        <td>{{ $re->error }}</td>
                        <td>{{ $re->product_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
