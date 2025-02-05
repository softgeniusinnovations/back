@php
    $optionsId = collect(session()->get('bets'))
        ->pluck('option_id')
        ->toArray();
@endphp
@foreach ($options as $option)
    <div class="option-odd-list__item">
        <div>
            <button class="btn btn-sm btn-light text--small border oddBtn @if (in_array($option->id, $optionsId)) active @endif @if ($option->locked) locked @endif" data-option_id="{{ $option->id }}" @disabled($option->question->locked)>{{ rateData($option->odds) }} </button>
            <span class="text--extra-small d-block text-center">{{ $option->name }}</span>
        </div>
    </div>
@endforeach
