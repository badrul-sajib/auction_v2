@extends('layouts.app')

@section('content')
    @include('pages.inventory.payment-methods.form', [
        'heading' => 'Edit Payment Method',
        'action' => route('inventory.payment-methods.update', $method),
        'method_verb' => 'PUT',
    ])
@endsection
