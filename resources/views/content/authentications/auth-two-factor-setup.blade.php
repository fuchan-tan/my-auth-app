@php
// CRITICAL FIX: Aggressively retrieve the user model directly from the
// Guard's User Provider using the user's ID to bypass ALL session caching
// that causes the 'PENDING' state after a fresh login.
$userId = auth()->id();
$user = auth()->getProvider()->retrieveById($userId);

// Fallback just in case retrievalById fails (though highly unlikely in a standard setup)
if (!$user) {
    $user = auth()->user();
    $user->refresh();
}

$configData = Helper::appClasses();
$pageConfigs = ['layout' => 'contentNavbarLayout'];

// Define status messages for a clean look
$status = session('status');

// FIX: Safely determine if validation errors exist for display.
// This prevents the 'getBag() on null' error when no validation has occurred.
$localErrors = null;
if (isset($errors) && $errors->any()) {
    $localErrors = $errors;
}
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Two Factor Setup Page')

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
  'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-lg-10 mx-auto">
      <div class="card p-4">
        <h4 class="card-header">Two-Factor Authentication (2FA) Setup</h4>
        <div class="card-body">

          {{-- 1. PENDING CONFIRMATION (Secret Exists, but not confirmed) --}}
          {{-- CHECKING THE RAW DATABASE COLUMN HERE FOR GUARANTEED ACCURACY --}}
          @if ($user->two_factor_secret && is_null($user->two_factor_confirmed_at))

            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <span class="alert-icon text-warning me-2">
                <i class="ti ti-alert-circle ti-sm"></i>
              </span>
              Two-Factor Authentication is currently **PENDING CONFIRMATION**. Please scan the code and enter the verification token below to finalize setup.
            </div>

            {{-- NEW: Display success status message from 'Enable 2FA' action --}}
            @if ($status === 'two-factor-authentication-enabled')
              <div class="alert alert-success mb-4 mx-auto col-md-6" role="alert">
                2FA setup initiated successfully. Scan the QR code below to complete confirmation.
              </div>
            @endif

            {{-- Display generic error status messages (excluding successful ones) --}}
            @if ($status && $status !== 'two-factor-authentication-confirmed' && $status !== 'two-factor-authentication-enabled')
                <div class="alert alert-danger mb-4 mx-auto col-md-6" role="alert">
                  {{ $status }}
                </div>
            @endif
            
            {{-- Use localErrors for display --}}
            @if ($localErrors)
              <div class="alert alert-danger mb-4 mx-auto col-md-6" role="alert">
                <ul class="mb-0 ps-3">
                  @foreach ($localErrors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="text-center mb-4">
              <h5 class="mb-2">Scan the QR Code to Confirm</h5>
              <p class="text-muted">
                Scan the image below with your authenticator app (e.g., Google Authenticator, Authy).
              </p>
            </div>

            <div class="d-flex justify-content-center mb-4">
              <div class="p-3 border border-gray-300 rounded-lg bg-white">
                {!! $user->twoFactorQrCodeSvg() !!}
              </div>
            </div>

            <p class="text-center mb-4">
                <span class="text-muted">Secret Key:</span>
                <code class="font-mono text-primary">{{ decrypt($user->two_factor_secret) }}</code>
            </p>

            <h5 class="mb-3 text-center">Confirmation</h5>
            <p class="text-center text-muted">Enter the 6-digit code from your authenticator app to complete setup.</p>

            {{-- Confirmation Form --}}
            <form method="POST" action="{{ url('auth/user/confirmed-two-factor-authentication') }}" class="mt-4 col-md-6 mx-auto">
              @csrf
              <div class="mb-3">
                <label for="code" class="form-label">Confirmation Code</label>
                <input id="code" type="text" name="code" required
                  class="form-control text-center"
                  placeholder="e.g., 123456" autofocus/>
                @error('code')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>

              <button type="submit" class="btn btn-success d-grid w-100">
                Confirm & Activate 2FA
              </button>
            </form>

          {{-- 2. FULLY ENABLED (Disable Button & Recovery Codes Visible) --}}
          {{-- CHECKING THE RAW DATABASE COLUMN HERE FOR GUARANTEED ACCURACY --}}
          @elseif (!is_null($user->two_factor_confirmed_at))

            {{-- Success Message Display --}}
            @if ($status === 'two-factor-authentication-confirmed' || $status === 'recovery-codes-generated')
              <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <span class="alert-icon text-success me-2">
                  <i class="ti ti-check ti-sm"></i>
                </span>
                Two-Factor Authentication Setup Complete! Please save your recovery codes below.
              </div>
            @else
              <div class="alert alert-success d-flex align-items-center" role="alert">
                <span class="alert-icon text-success me-2">
                  <i class="ti ti-check ti-sm"></i>
                </span>
                Two-Factor Authentication is **ACTIVELY ENABLED**.
              </div>
            @endif

            <div class="row">
              <div class="col-md-6 mb-4">
                <h5 class="mb-3">Recovery Codes</h5>
                <p class="text-muted">
                  Use these codes to log in if you lose access to your authenticator device. Store them safely!
                </p>
                @if ($user->two_factor_recovery_codes)
                  {{-- Display Codes if they EXIST --}}
                  <div class="p-3 bg-light border rounded mb-3">
                    @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                      <code class="d-block font-mono text-sm text-dark">{{ $code }}</code>
                    @endforeach
                  </div>
                @else
                  {{-- Display error message if codes are MISSING --}}
                  <div class="alert alert-danger p-2" role="alert">
                    **CRITICAL ERROR:** Recovery codes are missing from your account data.
                  </div>
                @endif

                {{-- Recovery Code Regeneration Form (Always available when confirmed) --}}
                <form method="POST" action="{{ url('auth/user/two-factor-recovery-codes') }}" class="mt-3">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary btn-sm">
                    Regenerate New Recovery Codes
                  </button>
                </form>
              </div>

              <div class="col-md-6">
                <h5 class="mb-3">Disable 2FA</h5>
                <p class="text-muted">
                  Disabling 2FA will remove this extra layer of security from your account. **You will be securely prompted for your password to confirm this action.**
                </p>
                {{-- Form to DISABLE 2FA (Reverting to the default Fortify redirect flow) --}}
                <form method="POST" action="{{ url('auth/user/two-factor-authentication') }}" class="mt-3">
                  @csrf
                  @method('DELETE')
                  
                  {{-- NOTE: Password confirmation is now handled via a secure redirect page --}}

                  <button type="submit" class="btn btn-danger d-grid w-100">
                    Disable 2FA
                  </button>
                </form>
              </div>
            </div>

          {{-- 3. NOT ENABLED YET (Initial Enable Button) --}}
          @else
            <div class="alert alert-info d-flex align-items-center" role="alert">
              <span class="alert-icon text-info me-2">
                <i class="ti ti-lock ti-sm"></i>
              </span>
              Two-Factor Authentication is currently **DISABLED**. Click below to start the setup process.
            </div>

            <p class="text-muted mb-4">
              When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication.
            </p>

            {{-- Form to START the setup process (GENERATES secret key) --}}
            <form method="POST" action="{{ url('auth/user/two-factor-authentication') }}">
              @csrf
              <button type="submit" class="btn btn-primary d-grid w-100">
                Enable 2FA
              </button>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
