@props(['color' => 'currentColor'])

<svg xmlns="http://www.w3.org/2000/svg" width="{{ $attributes->merge(['width' => '20'])->get('width') }}"
     height="{{ $attributes->merge(['height' => '20'])->get('height') }}"
     class="{{ $attributes->merge(['class' => 'shrink-0 size-5'])->get('class') }}" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
    </path>
</svg>

