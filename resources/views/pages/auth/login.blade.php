<x-layouts::auth title="تسجيل الدخول">
    <div class="flex flex-col gap-6">
        <x-auth-header title="تسجيل الدخول إلى حسابك" description="أدخل بريدك الإلكتروني وكلمة المرور للمتابعة إلى LuminaRAG" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            label="تسجيل الدخول بمفتاح مرور"
            loading-label="جار التحقق..."
            separator="أو تابع بالبريد الإلكتروني"
        />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                label="البريد الإلكتروني"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="name@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    label="كلمة المرور"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="كلمة المرور"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm text-[#00e5ff] hover:text-[#9cf0ff] end-0" :href="route('password.request')" wire:navigate>
                        نسيت كلمة المرور؟
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" label="تذكرني" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full shadow-lg shadow-[#00e5ff]/20" data-test="login-button">
                    تسجيل الدخول
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-[#bac9cc]">
            <span>ليس لديك حساب؟</span>
            <flux:link class="text-[#00e5ff] hover:text-[#9cf0ff]" :href="route('register')" wire:navigate>إنشاء حساب</flux:link>
        </div>
    </div>
</x-layouts::auth>
