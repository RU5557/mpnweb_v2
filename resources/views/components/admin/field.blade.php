@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => null, 'step' => null, 'class' => ''])

<div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($required) required @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($step) step="{{ $step }}" @endif
        {{ $attributes->merge(['class' => 'w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-medium ' . $class]) }}
    >
</div>
