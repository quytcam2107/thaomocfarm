@props(['code' => '', 'desc' => '', 'minOrder' => '', 'exp' => ''])
<article class="cpn">
    <div class="cpn__left"><b>{{ $code }}</b><small>{{ $minOrder }}</small></div>
    <div class="cpn__body">
        <p>{{ $desc }}</p>
        <div class="cpn__foot">
            <span class="cpn__exp">{{ $exp }}</span>
            <button class="btn btn--sm btn--clay copy-btn" data-code="{{ $code }}">Sao chép</button>
        </div>
    </div>
</article>