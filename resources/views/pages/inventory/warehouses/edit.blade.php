@extends('layouts.app')

@section('content')
    @include('pages.inventory.warehouses.form', [
        'heading' => 'Edit Warehouse',
        'action' => route('inventory.warehouses.update', $warehouse),
        'method' => 'PUT',
    ])
@endsection
