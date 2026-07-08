<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
    <head>
        @include('partials.head')

        <style>
            .lumina-auth [data-flux-label] {
                color: #d4e4fa;
            }

            .lumina-auth [data-flux-description],
            .lumina-auth [data-flux-subheading] {
                color: #bac9cc;
            }

            .lumina-auth [data-flux-control] {
                background-color: rgba(18, 33, 49, 0.82);
                border-color: rgba(59, 73, 76, 0.85);
                color: #d4e4fa;
            }

            .lumina-auth [data-flux-control]::placeholder {
                color: rgba(186, 201, 204, 0.62);
            }

            .lumina-auth [data-flux-control]:focus {
                border-color: #00e5ff;
                box-shadow: 0 0 0 2px rgba(0, 229, 255, 0.22);
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#051424] antialiased text-[#d4e4fa]">
        <div class="lumina-auth flex min-h-svh flex-col items-center justify-center gap-6 bg-[radial-gradient(circle_at_50%_18%,rgba(0,229,255,0.10),transparent_36%),linear-gradient(180deg,#051424_0%,#051424_58%,#010f1f_100%)] p-6 [--color-accent:#00e5ff] [--color-accent-content:#00e5ff] [--color-accent-foreground:#001f24] md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium" wire:navigate>
                    <span class="flex items-center gap-2">
                        <flux:icon name="light-bulb" variant="solid" class="size-6 text-[#00e5ff]" />
                        <span class="text-2xl font-bold leading-none text-[#00e5ff]">LuminaRAG</span>
                    </span>
                    <span class="sr-only">LuminaRAG</span>
                </a>

                <div class="rounded-xl border border-[#3b494c]/30 bg-[#112240]/70 p-6 shadow-2xl shadow-[#00e5ff]/10 backdrop-blur-xl md:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
