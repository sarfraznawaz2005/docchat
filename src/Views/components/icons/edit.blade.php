@props(['color' => 'currentColor'])

<svg xmlns="http://www.w3.org/2000/svg" class="{{ $attributes->merge(['class' => 'shrink-0 size-5'])->get('class') }}"
     width="{{ $attributes->merge(['width' => '18'])->get('width') }}" height="{{ $attributes->merge(['height' => '18'])->get('height') }}"
     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
</svg>
