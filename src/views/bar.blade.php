@if(auth()->user())
<div id="actinite-bar" style="background-color: #444;color:#fff;padding:8px;">
    <strong>Actinite</strong>

    |

    {{--@if($node ?? false)--}}
    <a href="/actinite/editor" style="color:#fff;">Editor</a>
    {{--@endif--}}


    <div class="btn-group justify-content-end" style="margin-top:-3px;margin-bottom:-3px;float:right">
        <a
            href="/actinite/switch-mode/published?return_to={{ request()->fullUrl() }}"
            class="btn {{ session('actinite:draft') ? 'ac-toggle-inactive' : 'ac-toggle-active' }}"
        >Published</a>

        <a
            href="/actinite/switch-mode/draft?return_to={{ request()->fullUrl() }}"
            class="btn {{ session('actinite:draft') ? 'ac-toggle-active' : 'ac-toggle-inactive' }}"
        >Draft</a>

    </div>

    {{--
    |

    <a
        href="/actinite/publish-all"
        style="color:#fff;"
    >Publish all changes</a>

    --}}
</div>
@endif
