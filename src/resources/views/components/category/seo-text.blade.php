@props([
    'heading' => '',
    'description' => null,
])

@if (!empty($description))
    <section class="seo-text">
        <h2>{{ $heading }}</h2>
        <p>{!! nl2br(e($description)) !!}</p>
    </section>
@endif