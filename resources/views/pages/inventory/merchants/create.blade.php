@extends('layouts.app')

@section('content')
    @include('pages.inventory.merchants.form', [
        'heading' => 'New Merchant',
        'action' => route('inventory.merchants.store'),
        'method' => 'POST',
    ])
@endsection
