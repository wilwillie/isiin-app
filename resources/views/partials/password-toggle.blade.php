<button
    type="button"
    class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-xl text-ink/50 transition hover:text-ink"
    data-password-toggle="{{ $inputId }}"
    aria-controls="{{ $inputId }}"
    aria-label="Tampilkan password"
    aria-pressed="false"
    title="Tampilkan password"
>
    <svg data-eye-open viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2.1 12s3.6-7 9.9-7 9.9 7 9.9 7-3.6 7-9.9 7-9.9-7-9.9-7Z"></path>
        <circle cx="12" cy="12" r="3"></circle>
    </svg>
    <svg data-eye-closed viewBox="0 0 24 24" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="m3 3 18 18"></path>
        <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
        <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.3 0 9.9 7 9.9 7a15.7 15.7 0 0 1-3.1 3.9"></path>
        <path d="M6.2 6.2C3.6 8 2.1 12 2.1 12s3.6 7 9.9 7c1.2 0 2.3-.3 3.3-.7"></path>
    </svg>
</button>
