<x-layouts::auth title="إنشاء حساب">
    <div class="flex flex-col gap-6">
        <x-auth-header title="إنشاء حساب جديد" description="أدخل بياناتك للبدء في استخدام LuminaRAG" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="name"
                label="الاسم الكامل"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="الاسم الكامل"
            />

            <flux:input
                name="email"
                label="البريد الإلكتروني"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="name@example.com"
            />

            <flux:input
                name="password"
                label="كلمة المرور"
                type="password"
                required
                autocomplete="new-password"
                placeholder="كلمة المرور"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                name="password_confirmation"
                label="تأكيد كلمة المرور"
                type="password"
                required
                autocomplete="new-password"
                placeholder="تأكيد كلمة المرور"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full shadow-lg shadow-[#00e5ff]/20" data-test="register-user-button">
                    إنشاء حساب
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-[#bac9cc]">
            <span>لديك حساب بالفعل؟</span>
            <flux:link class="text-[#00e5ff] hover:text-[#9cf0ff]" :href="route('login')" wire:navigate>تسجيل الدخول</flux:link>
        </div>
    </div>
</x-layouts::auth>
