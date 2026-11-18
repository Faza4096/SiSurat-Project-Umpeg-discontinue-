@props(['class' => 'h-11 w-11'])

<img src="{{ asset('img/lambang-kabupaten-bogor.png') }}"
     {{ $attributes->merge(['class' => 'object-contain ' . $class]) }}
     alt="Lambang Pemerintah Kabupaten Bogor"
     width="800" height="1013"
     role="img">
