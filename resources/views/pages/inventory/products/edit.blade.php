@extends('layouts.app')

@section('content')
    @include('pages.inventory.products.form', [
        'heading' => 'Edit Product',
        'action' => route('inventory.products.update', $product),
        'method' => 'PUT',
    ])
@endsection
