@extends('layouts.app')

@section('content')
    @include('pages.inventory.payment-methods.form', [
        'heading' => 'New Payment Method',
        'action' => route('inventory.payment-methods.store'),
        'method_verb' => 'POST',
    ])
@endsection
