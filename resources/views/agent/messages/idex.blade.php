@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Ma Messagerie</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6 text-center text-gray-500">
            <p>Vous n'avez pas encore de messages.</p>
        </div>
    </div>
</div>
@endsection