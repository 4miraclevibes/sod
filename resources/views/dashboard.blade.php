@extends('layouts.dashboard.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
<h1>Selamat datang {{ Auth::user()->name }}</h1>
</div>

<!-- / Content -->


@endsection