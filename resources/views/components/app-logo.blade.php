@props([
    'sidebar' => false,
])

<a {{ $attributes->class('flex items-center gap-3') }}>
    <span class="flex aspect-square size-10 items-center justify-center rounded-lg border border-[#a7c5d1]/60 bg-[#dff5fb] text-[#006875] dark:border-[#3b494c]/70 dark:bg-[#273647] dark:text-[#c3f5ff]">
        <x-app-logo-icon class="size-6 fill-current" />
    </span>

    <span class="grid text-start leading-tight">
        <span class="text-2xl font-bold tracking-tight text-[#173044] dark:text-[#c3f5ff]">LuminaRAG</span>
        <span class="text-xs font-semibold text-[#6f8c9b] dark:text-[#bac9cc]">نظام استرجاع ذكي</span>
    </span>
</a>
