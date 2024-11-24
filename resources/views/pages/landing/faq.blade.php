@extends('layouts.landing.main')

@section('style')
<style>
    .faq-section {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .faq-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .faq-item {
        margin-bottom: 20px;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .faq-question {
        padding: 15px 20px;
        background-color: #f8f9fa;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 500;
    }
    
    .faq-answer {
        padding: 15px 20px;
        display: none;
        border-top: 1px solid #eee;
    }
    
    .faq-question.active + .faq-answer {
        display: block;
    }
    
    .faq-icon {
        transition: transform 0.3s ease;
    }
    
    .faq-question.active .faq-icon {
        transform: rotate(180deg);
    }
    
    /* Tambahan style untuk WhatsApp section */
    .whatsapp-section {
        margin-top: 40px;
        padding: 20px 0;
    }
    
    .whatsapp-section .card {
        border-radius: 15px;
        border: none;
    }
    
    .whatsapp-section .btn-success {
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 500;
    }
    
    .whatsapp-section .btn-success:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            <p class="fs-3 m-auto mb-0">FAQ</p>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-header">
            <h2>Pertanyaan yang Sering Diajukan</h2>
            <p class="text-muted">Temukan jawaban untuk pertanyaan umum Anda di sini</p>
        </div>

        @foreach($faqs as $faq)
        <div class="faq-item">
            <div class="faq-question">
                {{ $faq->question }}
                <i class="bi bi-chevron-down faq-icon"></i>
            </div>
            <div class="faq-answer">
                {!! nl2br(e($faq->answer)) !!}
            </div>
        </div>
        @endforeach

        <!-- Tambahan WhatsApp Section -->
        <div class="whatsapp-section text-center mt-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Masih Punya Pertanyaan?</h4>
                    <p class="text-muted mb-3">Hubungi kami melalui WhatsApp untuk bantuan lebih lanjut</p>
                    <a href="https://wa.me/6285171742037" 
                       target="_blank" 
                       class="btn btn-success btn-lg">
                        <i class="bi bi-whatsapp me-2"></i>
                        Hubungi Kami di WhatsApp
                    </a>
                    <p class="mt-2 text-muted">
                        <i class="bi bi-telephone me-1"></i>
                        0851-7174-2037
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            // Toggle active class
            question.classList.toggle('active');
            
            // Close other open FAQs
            faqQuestions.forEach(otherQuestion => {
                if(otherQuestion !== question && otherQuestion.classList.contains('active')) {
                    otherQuestion.classList.remove('active');
                }
            });
        });
    });
});
</script>
@endsection
