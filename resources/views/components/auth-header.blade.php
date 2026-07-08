@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="text-[#d4e4fa]">{{ $title }}</flux:heading>
    <flux:subheading class="text-[#bac9cc]">{{ $description }}</flux:subheading>
</div>
