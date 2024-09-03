@extends('layouts.landing.main')

@section('style')
<style>
    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #e8f5e9;
        margin: 0 auto 15px;
        overflow: hidden;
    }
    .profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-name {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .profile-email {
        color: #888;
    }
    .profile-menu-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    .profile-menu-item i {
        font-size: 1.2rem;
        margin-right: 15px;
        color: #4caf50;
    }
    .profile-menu-item span {
        flex-grow: 1;
    }
    .profile-menu-item::after {
        content: '>';
        color: #888;
    }
</style>
@endsection

@section('content')
<div class="container mt-3">
    <div class="bg-white mb-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('home') }}" class="text-success bg-success bg-opacity-10 rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-left"></i>
            </a>
            <p class="fs-3 m-auto mb-0">Account Center</p>
        </div>
    </div>

    <div class="profile-header">
        <div class="profile-image">
            <img src="{{ asset('assets/landing/images/avatar.png') }}" alt="Profile Picture">
        </div>
        <div class="profile-name">{{ Auth::user()->name }}</div>
        <div class="profile-email">{{ Auth::user()->email }}</div>
    </div>

    <div class="profile-menu">
        <a href="#" class="profile-menu-item text-decoration-none text-dark">
            <i class="bi bi-person"></i>
            <span>Detail Profil</span>
        </a>
        <a href="#" class="profile-menu-item text-decoration-none text-dark">
            <i class="bi bi-geo-alt"></i>
            <span>Alamat Saya</span>
        </a>
        <a href="#" class="profile-menu-item text-decoration-none text-dark">
            <i class="bi bi-shield-lock"></i>
            <span>Privasi dan Keamanan</span>
        </a>
        <a href="#" class="profile-menu-item text-decoration-none text-dark">
            <i class="bi bi-question-circle"></i>
            <span>Bantuan</span>
        </a>
        <a href="#" class="profile-menu-item text-decoration-none text-dark">
            <i class="bi bi-file-text"></i>
            <span>Syarat dan Ketentuan</span>
        </a>
        <a href="#" class="profile-menu-item text-decoration-none text-dark" id="logoutLink">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.getElementById('logoutLink');
    
    logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            // Buat form untuk logout
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("logout") }}';
            
            // Tambahkan CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Tambahkan form ke body dan submit
            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
@endsection