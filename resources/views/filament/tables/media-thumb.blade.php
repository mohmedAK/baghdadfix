@php
    /** @var \App\Models\OrderServiceMedia $rec */
    $rec   = $getRecord();
    $isImg = $rec->type === 'image';
    $url   = \Illuminate\Support\Facades\Storage::disk('public')->url($rec->file_path);
@endphp

<!-- صندوق معاينة ثابت تماماً -->
<div style="
    position: relative;
    width: 128px;
    height: 96px;
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(0,0,0,.05);
">
    @if($isImg)
        <img src="{{ $url }}" alt="thumb"
             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;">
    @else
        <video src="{{ $url }}#t=0.1" preload="metadata" muted playsinline
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;">
        </video>

        <!-- أيقونة تشغيل -->
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 style="height:32px;width:32px;opacity:.95;color:white;filter:drop-shadow(0 1px 3px rgba(0,0,0,.6));"
                 fill="currentColor">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </div>
    @endif
</div>
