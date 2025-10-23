@props([
  'title' => '',
  'value' => '',
  'subtitle' => null,
  'prefix' => null,
  'extra' => null,
])

<div class="bg-white rounded-2xl p-4 shadow">
  <div class="text-sm text-gray-500">{{ $title }}</div>
  <div class="text-2xl font-bold">{{ $prefix }}{{ $value }}</div>
  @if($subtitle)
    <div class="text-xs text-gray-400 mt-1">{{ $subtitle }}</div>
  @endif
  @if($extra)
    <div class="text-xs text-blue-600 mt-1">{{ $extra }}</div>
  @endif
</div>
