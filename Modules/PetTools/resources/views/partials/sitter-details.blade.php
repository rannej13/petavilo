@php
    $money = static fn ($value): string => ($pet->user?->profile?->country === 'United States' ? '$' : '£').number_format((float) $value, 0);
@endphp
<section class="stack stack--tight">
    <h2 class="card__title">Care instructions</h2>
    <ul class="welfare-list">
        @foreach(['feeding_instructions' => 'Feeding', 'medication_method' => 'Medicine given as', 'medication_instructions' => 'Medicines', 'hiding_spots' => 'Hiding spots', 'handling_tips' => 'Handling', 'routine_notes' => 'Routine'] as $field => $label)
            @if(filled($pet->getAttribute($field)))
                <li class="welfare-list__item"><span>{{ $label }}</span><strong>{!! nl2br(e((string) $pet->getAttribute($field))) !!}</strong></li>
            @endif
        @endforeach
        @if($pet->getAttribute('vet_name'))
            <li class="welfare-list__item"><span>Vet</span><strong>{{ $pet->getAttribute('vet_name') }} {{ $pet->getAttribute('vet_phone') }}@if($pet->getAttribute('vet_account_number')) · Account {{ $pet->getAttribute('vet_account_number') }}@endif</strong></li>
        @endif
        @if($pet->getAttribute('vet_spending_cap') !== null)
            <li class="welfare-list__item"><span>Vet bills</span><strong>I authorise treatment up to {{ $money($pet->getAttribute('vet_spending_cap')) }}. Call me before going over.</strong></li>
        @endif
    </ul>
</section>
