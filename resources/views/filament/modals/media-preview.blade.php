@props(['isImage' => true, 'url' => '', 'mime' => null])

<div class="p-2">
    @if ($isImage)
        <img src="{{ $url }}" class="max-h-[80vh] max-w-full mx-auto rounded-md" alt="preview">
    @else
        <video
            src="{{ $url }}"
            controls
            autoplay
            playsinline
            class="w-full max-h-[80vh] rounded-md bg-black"
            preload="metadata"
        >
            @if($mime)
                <source src="{{ $url }}" type="{{ $mime }}">
            @endif
            Your browser does not support the video tag.
        </video>
    @endif
</div>
