@props(['color' => 'currentColor'])

<svg xmlns="http://www.w3.org/2000/svg" class="{{ $attributes->merge(['class' => 'shrink-0 size-4 pointer'])->get('class') }}"
     width="{{ $attributes->merge(['width' => '18'])->get('width') }}"
     height="{{ $attributes->merge(['height' => '18'])->get('height') }}"
     stroke-width="5"
     viewBox="0 0 20 20" fill="currentColor">
    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path>
</svg>

