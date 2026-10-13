@if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs px-4 py-3 rounded-lg flex items-center justify-between shadow-xs"
        role="alert">
        <div class="flex items-center gap-2.5 font-medium">
            <span class="p-1 bg-emerald-100 text-emerald-700 rounded-md"><i
                    class="fa-solid fa-circle-check text-sm"></i></span>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 transition p-1"><i
                class="fa-solid fa-xmark text-sm"></i></button>
    </div>
@endif
