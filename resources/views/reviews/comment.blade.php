@extends('reviews.layout')
@section('title', 'Te escuchamos')
@section('content')
    <p class="eyebrow">Tu comentario</p>
    <h1>¿Qué faltó?</h1>
    <p class="intro">Cuéntanos qué pasó durante tu visita para que el equipo pueda revisarlo.</p>
    <form class="review-form" method="POST" action="{{ route('reviews.comment.update', $review) }}">
        @csrf
        <label class="comment-label" for="comment">¿Qué te gustaría contarnos?</label>
        <textarea id="comment" name="comment" maxlength="1000" placeholder="Te escuchamos. ¿Hubo algún detalle que faltó?" @error('comment') aria-invalid="true" aria-describedby="comment-error" @enderror>{{ old('comment') }}</textarea>
        <p class="field-note">Opcional · Máximo 1.000 caracteres</p>
        @error('comment')<p class="error" id="comment-error" role="alert">{{ $message }}</p>@enderror
        <button class="submit" type="submit">Enviar comentario</button>
    </form>
    <p class="note">Este comentario es privado.<br>Lo recibe directamente nuestro equipo.</p>
@endsection
