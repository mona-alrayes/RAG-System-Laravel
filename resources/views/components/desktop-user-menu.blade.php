@props([
    'name' => auth()->user()->name,
])

<flux:dropdown position="top" align="start" {{ $attributes }}>
    <flux:sidebar.profile
        :name="$name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        class="rounded-lg text-[#315065] hover:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:hover:bg-[#273647]/70"
        data-test="sidebar-menu-button"
    />

    <flux:menu class="lumina-menu min-w-64 border border-[#c9dce4]/80 bg-[#f5fbfe]/95 text-[#173044] shadow-[0_18px_60px_rgba(13,28,45,0.16)] backdrop-blur-xl dark:border-[#3b494c]/70 dark:bg-[#0d1c2d]/95 dark:text-[#d4e4fa]">
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:text class="text-xs text-[#6f8c9b] dark:text-[#849396]">مسجّل الدخول</flux:text>
                <flux:heading class="truncate">{{ $name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">
                الملف الشخصي
            </flux:menu.item>
            <flux:menu.item :href="route('security.edit')" icon="shield-check" wire:navigate class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">
                الأمان
            </flux:menu.item>
            <flux:menu.item :href="route('appearance.edit')" icon="sun" wire:navigate class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">
                المظهر
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]"
                    data-test="logout-button"
                >
                    تسجيل الخروج
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
