@extends('layouts.app')

@section('title', 'Sengketa Pesanan ' . $dispute->order->code)

@section('content')
    <a href="{{ route('admin.disputes.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">← Semua komplain</a>

    <div class="mt-5 flex flex-wrap items-end justify-between gap-3 sm:mt-7">
        <div>
            <p class="text-xs text-ink/50">Kasus #{{ $dispute->id }} · Pesanan #{{ $dispute->order->code }}</p>
            <h1 class="page-heading mt-1">Tinjau Komplain</h1>
            <p class="mt-2 text-sm text-ink/60">{{ $dispute->order->customer->name ?? 'Customer dihapus' }} · {{ $dispute->order->vendor->business_name ?? 'Vendor dihapus' }} · {{ $dispute->order->package->name ?? 'Paket dihapus' }}</p>
        </div>
        <span class="badge {{ $dispute->status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
            {{ $dispute->status === 'open' ? 'Menunggu keputusan' : 'Selesai' }}
        </span>
    </div>

    <div class="mt-5 grid items-start gap-4 sm:mt-7 sm:gap-6 lg:grid-cols-12">
        <div class="space-y-4 lg:col-span-8">
            <div class="card space-y-2 p-5 text-sm sm:p-6">
                <h2 class="section-heading">Konteks pesanan</h2>
                <p><span class="text-ink/60">Status pesanan:</span> {{ $dispute->order->status_label }}</p>
                <p>
                    <span class="text-ink/60">Status pembayaran:</span>
                    {{ $dispute->order->paid_at ? 'Sudah dibayar' : 'Belum tercatat dibayar' }}
                    @if ($dispute->order->paid_at)
                        · {{ $dispute->order->paid_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}
                    @endif
                </p>
                @if ($dispute->order->completed_at)
                    <p><span class="text-ink/60">Pengambilan ditandai selesai:</span> {{ $dispute->order->completed_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</p>
                @endif
            </div>
            @include('disputes._order-panel', ['order' => $dispute->order, 'disputes' => collect([$dispute]), 'allowNewDispute' => false, 'isVendor' => false])
        </div>

        <aside class="lg:col-span-4">
            @if ($dispute->status === 'open')
                <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="card space-y-4 p-5 sm:p-6">
                    @csrf
                    @include('partials.errors')
                    <div>
                        <h2 class="section-heading">Catat keputusan</h2>
                        <p class="mt-2 text-sm leading-6 text-ink/55">Keputusan ini dicatat untuk kedua pihak. Pengembalian dana atau perubahan pembayaran belum dilakukan otomatis.</p>
                    </div>
                    <div>
                        <label class="label" for="resolution_outcome">Hasil peninjauan</label>
                        <select class="input" id="resolution_outcome" name="resolution_outcome" required>
                            <option value="">Pilih hasil</option>
                            @foreach (\App\Models\Dispute::OUTCOMES as $value => $label)
                                <option value="{{ $value }}" @selected(old('resolution_outcome') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="resolution_note">Ringkasan keputusan</label>
                        <textarea class="input" id="resolution_note" name="resolution_note" rows="5" maxlength="3000" required>{{ old('resolution_note') }}</textarea>
                    </div>
                    <button class="btn btn-primary w-full" type="submit" onclick="return confirm('Tandai kasus ini selesai dan tutup diskusinya?')">Selesaikan Sengketa</button>
                </form>
            @elseif ($dispute->resolvedBy)
                <div class="card p-5 sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Keputusan dicatat</p>
                    <p class="mt-2 font-semibold">{{ $dispute->resolvedBy->name }} · {{ $dispute->resolved_at?->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</p>
                </div>
            @endif
        </aside>
    </div>
@endsection
