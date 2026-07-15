@extends('layouts.app')

@section('content')
    @include('pages.inventory.products.form', [
        'heading' => 'New Product',
        'action' => route('inventory.products.store'),
        'method' => 'POST',
    ])
@endsection
