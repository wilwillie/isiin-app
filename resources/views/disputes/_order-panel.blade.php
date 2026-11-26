@php
    $hasOpenDispute = $disputes->contains('status', 'open');
    $availableCategories = \App\Models\Dispute::categoriesForOrder($order);
    $canCreateDispute = ($allowNewDispute ?? true)
        && $availableCategories !== []
        && ! $hasOpenDispute;
@endphp

@if ($disputes->isNotEmpty() || $canCreateDispute)
    <section class="card space-y-4 p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-amber-700">Pesanan #{{ $order->code }}</p>
                <h2 class="section-heading mt-1">Komplain / Sengketa</h2>
            </div>
            @if ($hasOpenDispute)
                <span class="badge bg-amber-100 text-amber-800">Ada laporan yang sedang ditinjau</span>
            @elseif ($disputes->isNotEmpty())
                <span class="badge bg-emerald-100 text-emerald-800">{{ $disputes->count() }} laporan selesai</span>
            @endif
        </div>

        @foreach ($disputes as $dispute)
            <article class="space-y-4 rounded-xl border border-ink/10 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $dispute->category_label }}</p>
                        <p class="mt-1 text-xs text-ink/55">
                            Laporan #{{ $dispute->id }} · Dilaporkan oleh {{ $dispute->openedBy->name ?? 'Akun dihapus' }} · {{ $dispute->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}
                        </p>
                    </div>
                    <span class="badge {{ $dispute->status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $dispute->status === 'open' ? 'Menunggu penyelesaian admin' : 'Selesai' }}
                    </span>
                </div>

                <div class="rounded-xl bg-[#faf8f3] p-4 text-sm">
                    <p class="whitespace-pre-line leading-6">{{ $dispute->description }}</p>
                    @if ($dispute->evidence_photos)
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($dispute->evidence_photos as $photoIndex => $photo)
                                <a href="{{ route('disputes.evidence', [$dispute, $photoIndex]) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('disputes.evidence', [$dispute, $photoIndex]) }}" alt="Bukti komplain {{ $photoIndex + 1 }}" class="aspect-square w-full rounded-lg object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($dispute->messages->isNotEmpty())
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold">Percakapan laporan #{{ $dispute->id }}</h3>
                        @foreach ($dispute->messages as $message)
                            <article class="rounded-xl border border-ink/5 p-3 text-sm">
                                <div class="flex flex-wrap justify-between gap-2">
                                    <p class="font-semibold">{{ $message->sender->name ?? 'Akun dihapus' }}</p>
                                    <time class="text-xs text-ink/50">{{ $message->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</time>
                                </div>
                                <p class="mt-2 whitespace-pre-line leading-6">{{ $message->body }}</p>
                                @if ($message->evidence_photos)
                                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                        @foreach ($message->evidence_photos as $photoIndex => $photo)
                                            <a href="{{ route('disputes.evidence', ['dispute' => $dispute, 'evidenceIndex' => $photoIndex, 'message' => $message->id]) }}" target="_blank" rel="noopener">
                                                <img src="{{ route('disputes.evidence', ['dispute' => $dispute, 'evidenceIndex' => $photoIndex, 'message' => $message->id]) }}" alt="Bukti tanggapan {{ $photoIndex + 1 }}" class="aspect-square w-full rounded-lg object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif

                @if ($dispute->status === 'open')
                    <form method="POST" enctype="multipart/form-data" action="{{ route('disputes.messages.store', $dispute) }}" class="space-y-3 border-t border-ink/5 pt-4">
                        @csrf
                        @include('partials.errors')
                        <div>
                            <label class="label" for="dispute-message-{{ $dispute->id }}">Tanggapan</label>
                            <textarea class="input" id="dispute-message-{{ $dispute->id }}" name="body" rows="3" maxlength="3000" required>{{ old('body') }}</textarea>
                        </div>
                        <div>
                            <label class="label" for="dispute-message-photos-{{ $dispute->id }}">Bukti tambahan (opsional, maks. 4 foto, JPG/PNG/WEBP, 5 MB per foto)</label>
                            <input class="input" type="file" id="dispute-message-photos-{{ $dispute->id }}" name="evidence_photos[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                        </div>
                        <button class="btn btn-outline w-full sm:w-auto" type="submit">Kirim Tanggapan</button>
                    </form>
                @else
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm">
                        <p class="font-semibold text-emerald-900">Keputusan admin: {{ $dispute->outcome_label }}</p>
                        <p class="mt-2 whitespace-pre-line leading-6 text-emerald-900/80">{{ $dispute->resolution_note }}</p>
                        <p class="mt-2 text-xs text-emerald-900/60">
                            {{ $dispute->resolvedBy->name ?? 'Admin' }} · {{ $dispute->resolved_at?->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </article>
        @endforeach

        @if ($canCreateDispute)
            <form method="POST" enctype="multipart/form-data" action="{{ $isVendor ? route('vendor.orders.disputes.store', $order) : route('orders.disputes.store', $order) }}" class="space-y-4 border-t border-ink/5 pt-4">
                @csrf
                @include('partials.errors')
                <div>
                    <h3 class="text-sm font-semibold">{{ $disputes->isNotEmpty() ? 'Buat laporan baru' : 'Laporkan masalah pada pesanan' }}</h3>
                    <p class="mt-1 text-sm leading-6 text-ink/55">
                        @if ($order->status === 'completed')
                            Komplain setelah pengambilan dapat dilaporkan kapan saja. Sertakan foto atau bukti yang membantu peninjauan.
                        @elseif (in_array($order->status, ['confirmed', 'paid'], true))
                            Pada tahap ini, laporan dibatasi untuk masalah pembayaran, jadwal, atau ketidaksesuaian paket. Masalah kualitas layanan dapat dilaporkan setelah pemasangan.
                        @else
                            Buat laporan agar admin dapat meninjau masalah dan bukti yang terkait.
                        @endif
                    </p>
                </div>
                <div>
                    <label class="label" for="dispute-category-{{ $order->id }}">Kategori</label>
                    <select class="input" id="dispute-category-{{ $order->id }}" name="category" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($availableCategories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="dispute-description-{{ $order->id }}">Jelaskan masalah</label>
                    <textarea class="input" id="dispute-description-{{ $order->id }}" name="description" rows="4" maxlength="3000" required>{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="label" for="dispute-photos-{{ $order->id }}">Bukti foto (opsional, maks. 4 foto, JPG/PNG/WEBP, 5 MB per foto)</label>
                    <input class="input" type="file" id="dispute-photos-{{ $order->id }}" name="evidence_photos[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                    <p class="mt-1 text-xs text-ink/50">Bukti hanya dapat dilihat oleh customer, vendor terkait, dan admin.</p>
                </div>
                <button type="submit" class="btn btn-outline">Kirim Laporan</button>
            </form>
        @elseif ($hasOpenDispute)
            <p class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-900">Tunggu sampai admin menyelesaikan laporan yang sedang ditinjau sebelum membuat laporan baru.</p>
        @endif
    </section>
@endif
