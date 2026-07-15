@extends('layouts.app')

@section('content')
    @include('pages.inventory.warehouses.form', [
        'heading' => 'New Warehouse',
        'action' => route('inventory.warehouses.store'),
        'method' => 'POST',
    ])
@endsection
