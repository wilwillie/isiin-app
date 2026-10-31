@if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 text-rose-800 px-4 py-3 text-sm">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
