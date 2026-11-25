@extends('layouts.app')

@section('body')
    <div class="login-layout">
        <header class="login-brand-panel">
            <x-brand :href="route('home')" />
            <div class="table-plan" aria-hidden="true">
                <span class="table-plan-center"></span>
            </div>
        </header>

        <main id="konten-utama" tabindex="-1" class="login-main">
            <div class="login-content">
                @yield('content')
            </div>
        </main>
    </div>
    @yield('page-scripts')
@endsection
