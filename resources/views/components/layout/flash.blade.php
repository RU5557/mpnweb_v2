{{-- Versi jangka panjang terbaik: pakai x-if, bukan x-show --}}
{{-- x-if benar-benar menghapus elemen dari DOM, jadi tidak ada sisa jarak / gap --}}

@if (session('success'))
<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 5000)"
    class="contents"
>
    <template x-if="show">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100 max-h-24"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95 max-h-0"
            class="bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs px-4 py-3 rounded-lg flex items-center justify-between shadow-xs overflow-hidden"
            role="alert"
        >
            <div class="flex items-center gap-2.5 font-medium">
                <span class="p-1 bg-emerald-100 text-emerald-700 rounded-md">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                </span>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 transition p-1 ml-4">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </template>
</div>
@endif
