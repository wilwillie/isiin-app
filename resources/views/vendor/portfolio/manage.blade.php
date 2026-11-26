@extends('layouts.app')

@section('title', 'Profil Vendor')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Profil bisnis</p>
            <h1 class="page-heading mt-2">Profil {{ $vendor->business_name }}</h1>
            <p class="page-subtitle">Tampilkan hasil kerja dengan detail proyek yang rapi agar pemilik properti mengenal pengalaman Anda.</p>
        </div>
        @if ($vendor->is_verified)
            <a href="{{ route('vendors.profile.show', $vendor) }}" target="_blank" rel="noopener" class="btn btn-outline">Lihat profil publik <span class="ml-2" aria-hidden="true">↗</span></a>
        @else
            <span class="rounded-full bg-amber-50 px-4 py-2 text-xs font-medium text-amber-800">Halaman publik aktif setelah verifikasi</span>
        @endif
    </div>

    @include('partials.errors')

    <section class="card mt-6 p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="section-heading">Tambah proyek</h2>
            <p class="mt-1 text-sm text-ink/55">Isi detail proyek, hubungkan dengan pesanan selesai bila ada, dan tambahkan foto sebelum dan/atau sesudah.</p>
        </div>
        <form method="POST" action="{{ route('vendor.portfolio.projects.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="project-title">Nama proyek</label>
                    <input class="input" id="project-title" name="project[title]" value="{{ old('project.title') }}" maxlength="120" required>
                </div>
                <div>
                    <label class="label" for="project-room-type">Tipe properti</label>
                    <select class="input" id="project-room-type" name="project[room_type]" required>
                        <option value="">Pilih tipe ruang</option>
                        @foreach (config('isiin.room_types') as $value => $label)
                            <option value="{{ $value }}" @selected(old('project.room_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="project-location">Lokasi proyek</label>
                    <input class="input" id="project-location" name="project[location]" value="{{ old('project.location') }}" maxlength="120" placeholder="Contoh: Jakarta Selatan" required>
                </div>
                <div>
                    <label class="label" for="project-year">Tahun selesai</label>
                    <input class="input" id="project-year" type="number" name="project[completion_year]" value="{{ old('project.completion_year', now(config('isiin.display_timezone'))->year) }}" min="1900" max="{{ now(config('isiin.display_timezone'))->year }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="project-description">Deskripsi dan hasil proyek</label>
                    <textarea class="input" id="project-description" name="project[description]" rows="4" maxlength="2000" placeholder="Ceritakan kebutuhan klien, konsep staging, dan hasil yang dicapai." required>{{ old('project.description') }}</textarea>
                </div>
                <div>
                    <label class="label" for="project-before-image">Foto sebelum (opsional)</label>
                    <input class="input" id="project-before-image" type="file" name="project[before_image]" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div>
                    <label class="label" for="project-after-image">Foto sesudah (opsional)</label>
                    <input class="input" id="project-after-image" type="file" name="project[after_image]" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>
            <p class="rounded-xl bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">Format JPG, PNG, atau WebP, maksimal 4 MB per foto. Pastikan Anda mendapat izin pelanggan untuk memublikasikan foto, dan hindari menampilkan wajah, alamat lengkap, atau informasi pribadi.</p>
            <button type="submit" class="btn btn-primary">Simpan proyek <span class="ml-2" aria-hidden="true">+</span></button>
        </form>
    </section>

    <section class="mt-8">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Karya pilihan</p>
                <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Proyek portofolio</h2>
            </div>
            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-ink/60 shadow-sm">{{ $projects->count() }} proyek</span>
        </div>

        <div class="space-y-4">
            @forelse ($projects as $project)
                @php($fieldPrefix = 'projects.' . $project->id)
                <details class="card overflow-hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                        <span class="min-w-0">
                            <span class="block truncate font-semibold">{{ $project->title }}</span>
                            <span class="mt-1 block text-xs text-ink/55">{{ config('isiin.room_types.' . $project->room_type) }} · {{ $project->location }} · {{ $project->completion_year }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-sand px-3 py-1.5 text-xs font-semibold text-ink/65">Edit</span>
                    </summary>
                    <form method="POST" action="{{ route('vendor.portfolio.projects.update', $project) }}" enctype="multipart/form-data" class="space-y-4 border-t border-ink/5 p-4 sm:p-5">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="label" for="project-{{ $project->id }}-title">Nama proyek</label>
                                <input class="input" id="project-{{ $project->id }}-title" name="projects[{{ $project->id }}][title]" value="{{ old($fieldPrefix . '.title', $project->title) }}" maxlength="120" required>
                            </div>
                            <div>
                                <label class="label" for="project-{{ $project->id }}-room-type">Tipe properti</label>
                                <select class="input" id="project-{{ $project->id }}-room-type" name="projects[{{ $project->id }}][room_type]" required>
                                    @foreach (config('isiin.room_types') as $value => $label)
                                        <option value="{{ $value }}" @selected(old($fieldPrefix . '.room_type', $project->room_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label" for="project-{{ $project->id }}-location">Lokasi proyek</label>
                                <input class="input" id="project-{{ $project->id }}-location" name="projects[{{ $project->id }}][location]" value="{{ old($fieldPrefix . '.location', $project->location) }}" maxlength="120" required>
                            </div>
                            <div>
                                <label class="label" for="project-{{ $project->id }}-year">Tahun selesai</label>
                                <input class="input" id="project-{{ $project->id }}-year" type="number" name="projects[{{ $project->id }}][completion_year]" value="{{ old($fieldPrefix . '.completion_year', $project->completion_year) }}" min="1900" max="{{ now(config('isiin.display_timezone'))->year }}" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label" for="project-{{ $project->id }}-description">Deskripsi dan hasil proyek</label>
                                <textarea class="input" id="project-{{ $project->id }}-description" name="projects[{{ $project->id }}][description]" rows="4" maxlength="2000" required>{{ old($fieldPrefix . '.description', $project->description) }}</textarea>
                            </div>
                            <div class="sm:col-span-2">
                                @if ($project->image_url)
                                    <img src="{{ $project->image_url }}" alt="Foto {{ $project->title }}" class="mb-3 aspect-[16/7] w-full rounded-xl object-cover sm:max-h-64">
                                    <label class="label" for="project-{{ $project->id }}-image">Ganti foto proyek (opsional)</label>
                                    <input class="input mb-3" id="project-{{ $project->id }}-image" type="file" name="projects[{{ $project->id }}][image]" accept=".jpg,.jpeg,.png,.webp">
                                @endif
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        @if ($project->before_image_url)
                                            <img src="{{ $project->before_image_url }}" alt="Sebelum: {{ $project->title }}" class="mb-2 aspect-[16/10] w-full rounded-xl object-cover">
                                            <label class="mb-2 inline-flex items-center gap-2 text-sm text-rose-700">
                                                <input type="checkbox" name="projects[{{ $project->id }}][remove_before_image]" value="1" @checked(old($fieldPrefix . '.remove_before_image'))>
                                                Hapus foto sebelum
                                            </label>
                                        @endif
                                        <label class="label" for="project-{{ $project->id }}-before-image">Ganti/tambahkan foto sebelum (opsional)</label>
                                        <input class="input" id="project-{{ $project->id }}-before-image" type="file" name="projects[{{ $project->id }}][before_image]" accept=".jpg,.jpeg,.png,.webp">
                                    </div>
                                    <div>
                                        @if ($project->after_image_url)
                                            <img src="{{ $project->after_image_url }}" alt="Sesudah: {{ $project->title }}" class="mb-2 aspect-[16/10] w-full rounded-xl object-cover">
                                            <label class="mb-2 inline-flex items-center gap-2 text-sm text-rose-700">
                                                <input type="checkbox" name="projects[{{ $project->id }}][remove_after_image]" value="1" @checked(old($fieldPrefix . '.remove_after_image'))>
                                                Hapus foto sesudah
                                            </label>
                                        @endif
                                        <label class="label" for="project-{{ $project->id }}-after-image">Ganti/tambahkan foto sesudah (opsional)</label>
                                        <input class="input" id="project-{{ $project->id }}-after-image" type="file" name="projects[{{ $project->id }}][after_image]" accept=".jpg,.jpeg,.png,.webp">
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-ink/50">Foto saat ini tetap digunakan jika tidak memilih file baru.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                            <button type="submit" form="delete-project-{{ $project->id }}" class="btn btn-danger" onclick="return confirm('Hapus proyek ini dari portofolio?')">Hapus proyek</button>
                        </div>
                    </form>
                    <form id="delete-project-{{ $project->id }}" method="POST" action="{{ route('vendor.portfolio.projects.destroy', $project) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </details>
            @empty
                <div class="card px-5 py-10 text-center">
                    <p class="font-medium">Belum ada proyek portofolio.</p>
                    <p class="mt-1 text-sm text-ink/55">Tambahkan proyek pertama dengan mengisi formulir di atas.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
