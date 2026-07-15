@extends('layouts.app')

@section('content')
    @include('pages.inventory.merchants.form', [
        'heading' => 'Edit Merchant',
        'action' => route('inventory.merchants.update', $merchant),
        'method' => 'PUT',
    ])
@endsection
