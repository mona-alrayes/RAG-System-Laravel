<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh overflow-x-hidden bg-[#eef7fb] text-[#173044] antialiased dark:bg-[#010f1f] dark:text-[#d4e4fa]">
        <flux:sidebar sticky collapsible="mobile" class="lumina-sidebar w-72 max-w-[86vw] border-e border-[#c9dce4]/80 bg-[#f5fbfe]/95 backdrop-blur-xl dark:border-[#273647]/70 dark:bg-[#0d1c2d] lg:w-64">
            <flux:sidebar.header class="px-4 pt-5 sm:px-6 sm:pt-6">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden text-[#5d7888] hover:text-[#006875] dark:text-[#bac9cc] dark:hover:text-[#c3f5ff]" />
            </flux:sidebar.header>

            <div class="px-4 pt-5 sm:pt-7">
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="flex items-center justify-center gap-2 rounded-lg bg-[#00d9f3] px-4 py-3 text-sm font-semibold text-[#00363d] shadow-[0_0_22px_rgba(0,218,243,0.22)] transition hover:bg-[#9cf0ff] hover:shadow-[0_0_28px_rgba(0,218,243,0.35)]"
                >
                    <flux:icon.plus class="size-5" />
                    محادثة جديدة
                </a>
            </div>

            <flux:sidebar.nav class="px-2 pt-5 sm:pt-6">
                <flux:sidebar.group class="grid">
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="rounded-lg text-[#315065] data-current:bg-[#d7e7f0] data-current:text-[#173044] hover:bg-[#e5f2f7] dark:text-[#bac9cc] dark:data-current:bg-[#3c4962] dark:data-current:text-[#d6e3ff] dark:hover:bg-[#273647]/70">
                        المحادثات
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="folder-open" href="#" class="rounded-lg text-[#4c697c] hover:bg-[#e5f2f7] hover:text-[#173044] dark:text-[#bac9cc] dark:hover:bg-[#273647]/70 dark:hover:text-[#d4e4fa]">
                        الملفات
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="px-2">
                <div class="px-4 pb-2 text-xs font-semibold text-[#6f8c9b] dark:text-[#849396]">
                    إدارة
                </div>

                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate class="rounded-lg text-[#4c697c] hover:bg-[#e5f2f7] hover:text-[#173044] dark:text-[#bac9cc] dark:hover:bg-[#273647]/70 dark:hover:text-[#d4e4fa]">
                    الإعدادات
                </flux:sidebar.item>

                <flux:sidebar.item icon="question-mark-circle" href="#" class="rounded-lg text-[#4c697c] hover:bg-[#e5f2f7] hover:text-[#173044] dark:text-[#bac9cc] dark:hover:bg-[#273647]/70 dark:hover:text-[#d4e4fa]">
                    المساعدة
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="mt-4 border-t border-[#c9dce4]/70 px-2 py-3 dark:border-[#273647]/70 sm:py-4">
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            </div>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-[#c9dce4]/80 bg-[#f5fbfe]/90 backdrop-blur-xl lg:hidden dark:border-[#273647]/70 dark:bg-[#051424]/90">
            <flux:sidebar.toggle class="lg:hidden text-[#315065] dark:text-[#d4e4fa]" icon="bars-2" inset="right" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu class="lumina-menu min-w-64 border border-[#c9dce4]/80 bg-[#f5fbfe]/95 text-[#173044] shadow-[0_18px_60px_rgba(13,28,45,0.16)] backdrop-blur-xl dark:border-[#3b494c]/70 dark:bg-[#0d1c2d]/95 dark:text-[#d4e4fa]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

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
                    </flux:menu.radio.group>

                    <flux:menu.separator />

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
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
