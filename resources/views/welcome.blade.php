@php
    $title = 'LuminaRAG - استخلص الحقيقة من بياناتك';

    $primaryHref = auth()->check()
        ? route('dashboard')
        : (Route::has('register') ? route('register') : '#');

    $loginHref = auth()->check()
        ? route('dashboard')
        : (Route::has('login') ? route('login') : '#');

    $navLinks = [
        ['label' => 'الميزات', 'href' => '#features'],
        ['label' => 'كيف يعمل', 'href' => '#how-it-works'],
        ['label' => 'الأسعار', 'href' => '#pricing'],
        ['label' => 'التوثيق', 'href' => '#docs'],
    ];

    $features = [
        [
            'title' => 'استرجاع عميق',
            'description' => 'تقوم خوارزميات البحث المتجه الدلالي بتحديد الأجزاء الأكثر صلة على الفور عبر ملايين المستندات، بغض النظر عن التنسيق.',
            'icon' => 'circle-stack',
            'accent' => '#00e5ff',
        ],
        [
            'title' => 'توليد سياقي',
            'description' => 'ينسج تركيب LLM المتقدم الأجزاء المسترجعة في استجابات متماسكة ودقيقة للغاية ومصممة خصيصا لسياق الاستعلام المحدد.',
            'icon' => 'cpu-chip',
            'accent' => '#c0d0f7',
        ],
        [
            'title' => 'إسناد المصدر',
            'description' => 'تتبع كامل. يتم ربط كل ادعاء تم إنشاؤه مباشرة بمستنده المصدر الأصلي مع درجات ثقة واضحة.',
            'icon' => 'shield-check',
            'accent' => '#d6e3ff',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark scroll-smooth">
    <head>
        @include('partials.head')

        <style>
            .lumina-gradient-text {
                background: linear-gradient(90deg, #d4e4fa 0%, #00e5ff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .lumina-glass {
                background: rgba(17, 34, 64, 0.62);
                border: 1px solid rgba(186, 201, 204, 0.12);
                backdrop-filter: blur(20px);
            }

            .lumina-glow:hover {
                box-shadow: 0 0 18px rgba(0, 229, 255, 0.36);
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#051424] font-sans text-[#d4e4fa] antialiased selection:bg-[#00e5ff]/30 selection:text-[#00e5ff]">
        <div class="flex min-h-screen flex-col overflow-hidden bg-[radial-gradient(circle_at_50%_22%,rgba(0,229,255,0.08),transparent_34%),linear-gradient(180deg,#051424_0%,#051424_52%,#010f1f_100%)]">
            <header class="fixed inset-x-0 top-0 z-50 border-b border-[#3b494c]/20 bg-[#051424]/82 backdrop-blur-md">
                <nav class="mx-auto flex max-w-[1440px] items-center justify-between gap-6 px-4 py-4 md:px-10" aria-label="التنقل الرئيسي">
                    <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                        <flux:icon name="light-bulb" variant="solid" class="size-5 text-[#00e5ff]" />
                        <span class="text-2xl font-bold leading-none text-[#00e5ff]">LuminaRAG</span>
                    </a>

                    <div class="hidden items-center gap-8 md:flex">
                        @foreach ($navLinks as $link)
                            <a
                                href="{{ $link['href'] }}"
                                class="rounded-md px-3 py-2 text-xs font-medium text-[#bac9cc] transition hover:bg-[#273647]/30 hover:text-[#d4e4fa]"
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            <a
                                href="{{ $loginHref }}"
                                class="hidden rounded-md px-3 py-2 text-xs font-medium text-[#bac9cc] transition hover:text-[#d4e4fa] md:inline-flex"
                                wire:navigate
                            >
                                {{ auth()->check() ? 'لوحة التحكم' : 'تسجيل الدخول' }}
                            </a>
                        @endif

                        <a
                            href="{{ $primaryHref }}"
                            class="lumina-glow inline-flex h-10 items-center justify-center rounded-lg bg-[#00e5ff] px-6 text-xs font-semibold text-[#001f24] shadow-sm shadow-[#00e5ff]/20 transition hover:bg-[#00daf3]"
                            wire:navigate
                        >
                            {{ auth()->check() ? 'افتح لوحة التحكم' : 'إنشاء حساب' }}
                        </a>
                    </div>
                </nav>
            </header>

            <main class="flex-1 pt-20">
                <section class="relative flex min-h-[80vh] items-center px-4 py-20 md:px-10">
                    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_70%_45%,rgba(0,229,255,0.08),transparent_30%)]"></div>

                    <div class="mx-auto grid w-full max-w-[1440px] grid-cols-1 items-center gap-12 lg:grid-cols-2">
                        <div class="flex max-w-2xl flex-col gap-6">
                            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-[#3b494c]/60 bg-[#122131] px-3 py-1">
                                <span class="size-2 rounded-full bg-[#00e5ff]"></span>
                                <span class="text-xs font-medium text-[#00e5ff]">الإصدار 2.0 متاح الآن</span>
                            </div>

                            <h1 class="text-4xl font-bold leading-tight text-[#d4e4fa] md:text-5xl">
                                استخلص الحقيقة من
                                <span class="lumina-gradient-text block pt-2">بياناتك</span>
                            </h1>

                            <p class="max-w-xl text-base leading-8 text-[#bac9cc] md:text-lg">
                                يجمع LuminaRAG بين الاسترجاع المتقدم والتوليد الدقيق لتحويل قاعدة معارفك إلى ذكاء قابل للتنفيذ. آمن، قابل للتطوير، ومصمم للدقة.
                            </p>

                            <div class="flex flex-wrap items-center gap-4 pt-2">
                                <a
                                    href="{{ $primaryHref }}"
                                    class="lumina-glow inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#00e5ff] px-8 text-sm font-semibold text-[#001f24] shadow-lg shadow-[#00e5ff]/20 transition hover:bg-[#00daf3]"
                                    wire:navigate
                                >
                                    {{ auth()->check() ? 'افتح لوحة التحكم' : 'إنشاء حساب' }}
                                    <flux:icon name="arrow-left" class="size-4" />
                                </a>

                                <a
                                    href="#how-it-works"
                                    class="inline-flex h-12 items-center justify-center gap-2 rounded-lg border border-[#3b494c]/70 bg-[#122131] px-8 text-sm font-semibold text-[#d4e4fa] transition hover:bg-[#1c2b3c]"
                                >
                                    <flux:icon name="play-circle" class="size-5" />
                                    شاهد العرض التوضيحي
                                </a>
                            </div>
                        </div>

                        <div class="lumina-glass relative aspect-[1.79] w-full overflow-hidden rounded-xl shadow-2xl shadow-[#00e5ff]/10">
                            <img
                                src="{{ asset('images/lumina-rag-hero.png') }}"
                                alt="تصور بصري لنظام RAG يربط مصادر المعرفة بالاسترجاع الدلالي"
                                class="h-full w-full object-cover"
                            >
                            <div class="absolute inset-0 bg-linear-to-tr from-[#051424]/45 to-transparent"></div>
                        </div>
                    </div>
                </section>

                <section id="features" class="bg-[#010f1f] px-4 py-24 md:px-10">
                    <div class="mx-auto max-w-[1440px]">
                        <div class="mx-auto mb-16 max-w-2xl text-center">
                            <h2 class="mb-4 text-2xl font-semibold text-[#d4e4fa] md:text-3xl">مصمم للدقة</h2>
                            <p class="text-base leading-7 text-[#bac9cc]">تضمن بنيتنا أن كل توليد يستند إلى مصدر الحقيقة الخاص بك.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            @foreach ($features as $feature)
                                <article
                                    class="lumina-glass group rounded-xl border-r-4 p-8 transition hover:bg-[#122131]/80"
                                    style="border-right-color: {{ $feature['accent'] }}80"
                                >
                                    <div class="mb-8 flex size-12 items-center justify-center rounded-lg border border-[#3b494c]/70 bg-[#122131] transition group-hover:bg-[#00e5ff]/10">
                                        <flux:icon :name="$feature['icon']" class="size-6" style="color: {{ $feature['accent'] }}" />
                                    </div>

                                    <h3 class="mb-3 text-xl font-semibold leading-7 text-[#d4e4fa]">{{ $feature['title'] }}</h3>
                                    <p class="text-sm leading-7 text-[#bac9cc]">{{ $feature['description'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="how-it-works" class="border-y border-[#3b494c]/10 bg-[#051424] px-4 py-20 md:px-10">
                    <div class="mx-auto grid max-w-[1440px] gap-8 md:grid-cols-3">
                        <div class="rounded-xl border border-[#3b494c]/30 bg-[#0d1c2d]/70 p-6">
                            <span class="mb-4 inline-flex text-sm font-semibold text-[#00e5ff]">01</span>
                            <h3 class="mb-3 text-lg font-semibold">اربط مصادر المعرفة</h3>
                            <p class="text-sm leading-7 text-[#bac9cc]">اجمع الملفات، الوثائق، والمستندات الداخلية في مساحة معرفة واحدة قابلة للبحث.</p>
                        </div>

                        <div class="rounded-xl border border-[#3b494c]/30 bg-[#0d1c2d]/70 p-6">
                            <span class="mb-4 inline-flex text-sm font-semibold text-[#00e5ff]">02</span>
                            <h3 class="mb-3 text-lg font-semibold">استرجع بالسياق</h3>
                            <p class="text-sm leading-7 text-[#bac9cc]">يحلل النظام السؤال ويختار المقاطع الأكثر صلة قبل إرسالها إلى نموذج التوليد.</p>
                        </div>

                        <div class="rounded-xl border border-[#3b494c]/30 bg-[#0d1c2d]/70 p-6">
                            <span class="mb-4 inline-flex text-sm font-semibold text-[#00e5ff]">03</span>
                            <h3 class="mb-3 text-lg font-semibold">احصل على إجابة موثقة</h3>
                            <p class="text-sm leading-7 text-[#bac9cc]">تعود الإجابة مدعومة بالمصادر ودرجات الثقة حتى تعرف من أين جاءت الحقيقة.</p>
                        </div>
                    </div>
                </section>

                <section id="pricing" class="bg-[#010f1f] px-4 py-20 md:px-10">
                    <div class="mx-auto flex max-w-[900px] flex-col items-center gap-6 text-center">
                        <span class="rounded-full border border-[#00e5ff]/30 bg-[#00e5ff]/10 px-4 py-1 text-sm font-medium text-[#00e5ff]">جاهز للتجربة</span>
                        <h2 class="text-2xl font-semibold md:text-3xl">ابدأ ببناء ذاكرة معرفية دقيقة لفريقك</h2>
                        <p class="max-w-2xl text-base leading-8 text-[#bac9cc]">سجّل حسابك واربط مصادر البيانات الأولى، ثم انتقل إلى لوحة التحكم لإدارة المعرفة ومتابعة الاسترجاع.</p>
                        <a
                            href="{{ $primaryHref }}"
                            class="lumina-glow inline-flex h-12 items-center justify-center rounded-lg bg-[#00e5ff] px-8 text-sm font-semibold text-[#001f24] shadow-lg shadow-[#00e5ff]/20 transition hover:bg-[#00daf3]"
                            wire:navigate
                        >
                            {{ auth()->check() ? 'انتقل إلى لوحة التحكم' : 'إنشاء حساب جديد' }}
                        </a>
                    </div>
                </section>
            </main>

            <footer id="docs" class="border-t border-[#3b494c]/10 bg-[#010f1f] px-4 py-12 md:px-10">
                <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-8 md:grid-cols-4">
                    <div>
                        <span class="mb-4 block text-2xl font-black text-[#d4e4fa]">LuminaRAG</span>
                        <p class="text-sm leading-7 text-[#bac9cc]">© 2024 LuminaRAG AI. استخلاص الحقيقة من البيانات.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <span class="mb-2 text-xs font-medium text-[#d4e4fa]/50">قانوني</span>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">سياسة الخصوصية</a>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">شروط الخدمة</a>
                    </div>

                    <div class="flex flex-col gap-3">
                        <span class="mb-2 text-xs font-medium text-[#d4e4fa]/50">الدعم</span>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">حالة واجهة برمجة التطبيقات (API)</a>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">اتصل بالدعم</a>
                    </div>

                    <div class="flex flex-col gap-3">
                        <span class="mb-2 text-xs font-medium text-[#d4e4fa]/50">اجتماعي</span>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">تويتر</a>
                        <a class="w-fit text-sm text-[#bac9cc] transition hover:text-[#00e5ff]" href="#">لينكد إن</a>
                    </div>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
