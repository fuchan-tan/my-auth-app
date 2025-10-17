@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Two Factor Challenge Page')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js',
  {{-- Removed 'resources/assets/js/pages-auth-two-steps.js' to prevent conflicts --}}
])
<script>
  // Get references to input elements outside the functions for cleaner access
  const codeInput = document.getElementById('code');
  const recoveryInput = document.getElementById('recovery_code');

  function showRecoveryForm() {
    document.getElementById('code-input-group').style.display = 'none';
    document.getElementById('recovery-input-group').style.display = 'flex'; // Use flex to match row layout
    document.getElementById('recovery-code-link').style.display = 'none';
    document.getElementById('authenticator-code-link').style.display = 'inline-block';
    
    // CRITICAL FIX: Disable required for the hidden input and enable for the visible one
    codeInput.required = false;
    recoveryInput.required = true;
    
    // Clear the code input when switching
    codeInput.value = '';
    // Focus on the new input field
    recoveryInput.focus();
  }

  function showCodeForm() {
    document.getElementById('code-input-group').style.display = 'flex'; // Use flex to match row layout
    document.getElementById('recovery-input-group').style.display = 'none';
    document.getElementById('recovery-code-link').style.display = 'inline-block';
    document.getElementById('authenticator-code-link').style.display = 'none';
    
    // CRITICAL FIX: Enable required for the visible input and disable for the hidden one
    codeInput.required = true;
    recoveryInput.required = false;

    // Clear the recovery code input when switching
    recoveryInput.value = '';
    // Focus on the new input field
    codeInput.focus();
  }

  // Initial setup: ensure the code form is visible and recovery form is hidden
  document.addEventListener('DOMContentLoaded', showCodeForm);
</script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">

    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-two-step-illustration-'.$configData['style'].'.png') }}" alt="auth-two-steps-cover" class="img-fluid my-5 auth-illustration" data-app-light-img="illustrations/auth-two-step-illustration-light.png" data-app-dark-img="illustrations/auth-two-step-illustration-dark.png">
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}" alt="auth-two-steps-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Two Steps Verification -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-4 p-sm-5">
      <div class="w-px-400 mx-auto">
        <h3 class="mb-1">Two Factor Authentication</h3>
        <p class="text-muted mb-4">
          Enter the code from your authenticator app or use one of your emergency recovery codes.
        </p>

        <form method="POST" action="{{ url('auth/two-factor-challenge') }}">
          @csrf

          {{-- --- Authenticator Code Input Group (Default) --- --}}
          <div class="row mb-3" id="code-input-group">
            <label for="code" class="col-md-6 col-form-label text-md-end">Authenticator Code</label>
            <div class="col-md-6">
                <input id="code" class="form-control @error('code') is-invalid @enderror" type="text" name="code" required autofocus autocomplete="one-time-code" />
                @error('code')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
          </div>

          {{-- --- Recovery Code Input Group (Hidden by default) --- --}}
          <div class="row mb-3" id="recovery-input-group" style="display: none;">
            <label for="recovery_code" class="col-md-6 col-form-label text-md-end">Recovery Code</label>
            <div class="col-md-6">
                {{-- NOTE: Removed static 'required' attribute, now managed dynamically by JS --}}
                <input id="recovery_code" class="form-control @error('recovery_code') is-invalid @enderror" type="text" name="recovery_code" autocomplete="off" />
                @error('recovery_code')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
          </div>

          <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Verify</button>

          <!-- Links to switch forms -->
          <div class="text-center">
            <a href="javascript:void(0)" onclick="showRecoveryForm()" id="recovery-code-link" class="text-muted">
              Use a Recovery Code
            </a>
            <a href="javascript:void(0)" onclick="showCodeForm()" id="authenticator-code-link" class="text-muted" style="display: none;">
              Use Authenticator Code
            </a>
          </div>

        </form>
      </div>
    </div>
    <!-- /Two Steps Verification -->
  </div>
</div>
@endsection