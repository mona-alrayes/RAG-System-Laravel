<x-layouts::app :title="__('لوحة التحكم')">
    <section class="relative flex min-h-[calc(100dvh-4rem)] flex-col overflow-x-hidden bg-[#eef7fb] text-[#173044] dark:bg-[#010f1f] dark:text-[#d4e4fa] lg:min-h-dvh">
        <div class="pointer-events-none absolute right-[-6rem] top-24 size-72 rounded-full bg-[#00daf3]/10 blur-[90px] dark:bg-[#00daf3]/5 sm:right-1/4 sm:top-1/4 sm:size-96 sm:blur-[110px]"></div>
        <div class="pointer-events-none absolute bottom-20 left-[-8rem] h-80 w-80 rounded-full bg-[#3c4962]/10 blur-[100px] dark:bg-[#b9c7e4]/5 sm:bottom-1/4 sm:left-1/4 sm:h-[30rem] sm:w-[34rem] sm:blur-[130px]"></div>

        <header class="sticky top-0 z-30 flex min-h-14 items-center justify-between gap-3 border-b border-[#c9dce4]/70 bg-[#f5fbfe]/75 px-3 py-2 backdrop-blur-xl dark:border-[#273647]/60 dark:bg-[#051424]/80 sm:h-16 sm:px-5 md:px-10">
            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                <span class="size-2 shrink-0 rounded-full bg-[#00daf3] shadow-[0_0_10px_rgba(0,218,243,0.85)]"></span>
                <span class="truncate text-[11px] font-semibold tracking-wide text-[#4c697c] dark:text-[#bac9cc] sm:text-xs">LuminaRAG - وضع LLM المحلي</span>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <flux:dropdown x-data align="end">
                    <flux:button variant="subtle" square class="border border-[#c9dce4]/80 bg-white/60 text-[#4c697c] hover:text-[#006875] dark:border-[#273647]/80 dark:bg-[#0d1c2d]/70 dark:text-[#bac9cc] dark:hover:text-[#c3f5ff]" aria-label="وضع المظهر">
                        <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" />
                        <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" />
                        <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                        <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                    </flux:button>

                    <flux:menu class="lumina-menu border border-[#c9dce4]/80 bg-[#f5fbfe]/95 text-[#173044] shadow-[0_18px_60px_rgba(13,28,45,0.16)] backdrop-blur-xl dark:border-[#3b494c]/70 dark:bg-[#0d1c2d]/95 dark:text-[#d4e4fa]">
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'" class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">فاتح</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'" class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">داكن</flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'" class="text-[#315065] data-active:bg-[#e5f2f7] dark:text-[#d4e4fa] dark:data-active:bg-[#273647]">النظام</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                <flux:button variant="subtle" square class="border border-[#c9dce4]/80 bg-white/60 text-[#4c697c] hover:text-[#006875] dark:border-[#273647]/80 dark:bg-[#0d1c2d]/70 dark:text-[#bac9cc] dark:hover:text-[#c3f5ff]" aria-label="إعدادات">
                    <flux:icon.cog-6-tooth variant="mini" />
                </flux:button>
            </div>
        </header>

        <div class="relative flex flex-1 flex-col px-4 pb-40 pt-6 sm:pb-44 sm:pt-8 md:px-8 md:pt-10 lg:pb-36">
            <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center py-4 text-center sm:min-h-[58vh] sm:py-8 lg:min-h-[62vh]">
                <img
                    src="{{ asset('images/lumina-rag-hero.png') }}"
                    alt="تصور نظام LuminaRAG"
                    class="mb-5 size-28 rounded-xl object-cover opacity-90 shadow-[0_0_28px_rgba(0,218,243,0.2)] ring-1 ring-[#00daf3]/20 min-[420px]:size-32 sm:mb-7 sm:size-40 sm:rounded-2xl md:mb-8 md:size-48 md:shadow-[0_0_34px_rgba(0,218,243,0.22)]"
                />

                <h1 class="max-w-2xl text-balance text-2xl font-bold tracking-tight text-[#173044] dark:text-[#d4e4fa] sm:text-3xl md:text-4xl">
                    كيف يمكنني مساعدتك اليوم؟
                </h1>

                <p class="mt-3 max-w-xl text-pretty text-sm leading-6 text-[#4c697c] dark:text-[#bac9cc] sm:text-base sm:leading-7">
                    أنا نظام LuminaRAG المحلي. قم برفع مستنداتك وسأقوم باستخراج المعلومات والإجابة على استفساراتك بدقة.
                </p>

                <div class="mt-7 grid w-full max-w-2xl grid-cols-1 gap-3 sm:mt-9 sm:gap-4 md:grid-cols-2">
                    <button type="button" class="group relative min-h-28 overflow-hidden rounded-lg border border-[#c9dce4]/80 bg-white/70 p-4 text-right shadow-sm backdrop-blur-xl transition hover:border-[#00aeca]/60 hover:bg-[#f7fdff] dark:border-[#3b494c]/50 dark:bg-[#0d1c2d]/70 dark:hover:border-[#00daf3]/50 dark:hover:bg-[#122131]/85 sm:min-h-32 sm:p-5">
                        <span class="absolute inset-0 bg-gradient-to-br from-[#00daf3]/10 to-transparent opacity-0 transition group-hover:opacity-100"></span>
                        <span class="relative mb-3 flex text-[#006875] dark:text-[#c3f5ff] sm:mb-4">
                            <flux:icon.magnifying-glass class="size-6" />
                        </span>
                        <span class="relative block text-sm font-bold text-[#173044] dark:text-[#d4e4fa]">استكشاف الملفات</span>
                        <span class="relative mt-2 block text-sm text-[#4c697c] dark:text-[#bac9cc]">ماذا يوجد في ملفاتي المرفوعة حديثاً؟</span>
                    </button>

                    <button type="button" class="group relative min-h-28 overflow-hidden rounded-lg border border-[#c9dce4]/80 bg-white/70 p-4 text-right shadow-sm backdrop-blur-xl transition hover:border-[#00aeca]/60 hover:bg-[#f7fdff] dark:border-[#3b494c]/50 dark:bg-[#0d1c2d]/70 dark:hover:border-[#00daf3]/50 dark:hover:bg-[#122131]/85 sm:min-h-32 sm:p-5">
                        <span class="absolute inset-0 bg-gradient-to-br from-[#00daf3]/10 to-transparent opacity-0 transition group-hover:opacity-100"></span>
                        <span class="relative mb-3 flex text-[#006875] dark:text-[#c3f5ff] sm:mb-4">
                            <flux:icon.document-text class="size-6" />
                        </span>
                        <span class="relative block text-sm font-bold text-[#173044] dark:text-[#d4e4fa]">تلخيص المستندات</span>
                        <span class="relative mt-2 block text-sm text-[#4c697c] dark:text-[#bac9cc]">لخص لي مستند الشروط والأحكام.</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-30 bg-gradient-to-t from-[#eef7fb] via-[#eef7fb] to-transparent px-3 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-10 dark:from-[#010f1f] dark:via-[#010f1f] sm:px-4 sm:pb-[calc(1.5rem+env(safe-area-inset-bottom))] md:px-8 lg:right-64">
            <div class="mx-auto w-full max-w-2xl">
                <div class="flex items-end gap-1.5 rounded-2xl border border-[#c9dce4]/90 bg-white/80 p-1.5 shadow-[0_18px_60px_rgba(13,28,45,0.12)] backdrop-blur-xl transition focus-within:border-[#00aeca] dark:border-[#3b494c]/70 dark:bg-[#0d1c2d]/80 dark:shadow-[0_18px_70px_rgba(0,218,243,0.08)] dark:focus-within:border-[#00daf3]/70 sm:gap-2 sm:p-2">
                    <button type="button" aria-label="إرفاق ملف" class="rounded-xl p-2.5 text-[#4c697c] transition hover:bg-[#e5f2f7] hover:text-[#006875] dark:text-[#bac9cc] dark:hover:bg-[#273647]/70 dark:hover:text-[#c3f5ff] sm:p-3">
                        <flux:icon.plus-circle class="size-5 sm:size-6" />
                    </button>

                    <textarea
                        class="max-h-40 min-h-10 w-full resize-none border-none bg-transparent px-1.5 py-2.5 text-sm text-[#173044] placeholder:text-[#7a93a1] focus:ring-0 dark:text-[#d4e4fa] dark:placeholder:text-[#849396] sm:max-h-48 sm:min-h-11 sm:px-2 sm:py-3 sm:text-base"
                        placeholder="اسأل LuminaRAG أو اطلب تحليل مستند..."
                        rows="1"
                    ></textarea>

                    <button type="button" aria-label="إرسال" class="m-0.5 flex items-center justify-center rounded-xl bg-[#d7e7f0] p-2.5 text-[#315065] transition hover:bg-[#00daf3] hover:text-[#001f24] dark:bg-[#273647] dark:text-[#d4e4fa] dark:hover:bg-[#c3f5ff] dark:hover:text-[#001f24] sm:m-1 sm:p-3">
                        <flux:icon.paper-airplane class="size-4 -rotate-90 sm:size-5" />
                    </button>
                </div>

                <p class="mt-2 text-center text-[10px] leading-4 text-[#7a93a1] dark:text-[#849396] sm:mt-3 sm:text-xs">
                    LuminaRAG يمكن أن يخطئ. يرجى التحقق من المعلومات المهمة.
                </p>
            </div>
        </div>
    </section>
</x-layouts::app>
