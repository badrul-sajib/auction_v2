@extends('layouts.app')

@section('content')
    @include('pages.inventory.categories.form', [
        'heading' => 'Edit Category',
        'action' => route('inventory.categories.update', $category),
        'method' => 'PUT',
    ])
@endsection
