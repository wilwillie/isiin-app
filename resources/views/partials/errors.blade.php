@if ($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200/70 bg-rose-50 px-4 py-3.5 text-sm text-rose-800">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
