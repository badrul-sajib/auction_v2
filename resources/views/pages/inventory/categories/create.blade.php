@extends('layouts.app')

@section('content')
    @include('pages.inventory.categories.form', [
        'heading' => 'New Category',
        'action' => route('inventory.categories.store'),
        'method' => 'POST',
    ])
@endsection
