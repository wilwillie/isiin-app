@extends('layouts.app')

@section('title', 'Komplain dan Sengketa')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3 sm:mb-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Administrasi</p>
            <h1 class="page-heading mt-2">Komplain dan Sengketa</h1>
            <p class="page-subtitle">Tinjau laporan, bukti, dan tanggapan dari kedua pihak.</p>
        </div>
        <span class="badge bg-amber-100 px-3 py-1.5 text-sm text-amber-800">{{ $openCount }} kasus terbuka</span>
    </div>

    <div class="space-y-3">
        @forelse ($disputes as $dispute)
            <a href="{{ route('admin.disputes.show', $dispute) }}" class="card flex flex-col gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold">Pesanan #{{ $dispute->order->code }}</p>
                        <span class="badge {{ $dispute->status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $dispute->status === 'open' ? 'Terbuka' : 'Selesai' }}
                        </span>
                    </div>
                    <p class="mt-1 break-words text-sm text-ink/70">{{ $dispute->category_label }} · Dilaporkan {{ $dispute->openedBy->name ?? 'Akun dihapus' }}</p>
                    <p class="mt-1 text-xs text-ink/50">
                        Pesanan: {{ $dispute->order->status_label }} · Pembayaran: {{ $dispute->order->paid_at ? 'sudah dibayar' : 'belum tercatat' }}
                    </p>
                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-ink/55">{{ $dispute->description }}</p>
                    <p class="mt-1 text-xs text-ink/45">{{ $dispute->messages_count }} tanggapan · {{ $dispute->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</p>
                </div>
                <span class="shrink-0 text-sm font-semibold text-amber-800">Tinjau kasus <span aria-hidden="true">→</span></span>
            </a>
        @empty
            <div class="card px-5 py-10 text-center text-ink/60 sm:py-14">Belum ada laporan komplain.</div>
        @endforelse
    </div>

    @if ($disputes->hasPages())
        <div class="mt-5">{{ $disputes->links() }}</div>
    @endif
@endsection
