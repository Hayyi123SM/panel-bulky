<div style="display: flex; flex-direction: column; align-items: center;">
    <video controls style="max-width: 100%; max-height: 600px;">
        <source src="{{ asset('storage/'.$getState()) }}" type="{{ $getRecord()->type }}">
        Your browser does not support the video tag.
    </video>
    <div style="width: 100%; max-width: 250px; text-align: center; margin-top: 10px;">
        <h3>{{ $getRecord()->title }}</h3>
        <p class="text-sm mt-3">{{ $getRecord()->description }}</p>
    </div>
</div>
