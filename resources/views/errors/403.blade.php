<x-error.layout code="403" title="Akses Ditolak" subtitle="Forbidden" icon="fa-ban" iconBg="bg-amber-50"
    iconBorder="border-amber-200" iconColor="text-amber-600">
    Kamu tidak memiliki izin untuk mengakses halaman ini. Silakan login sebagai admin atau hubungi Seksi PKD.
    @slot('extra')
        <div class="flex gap-3"><a href="{{ route('login') }}"
                class="flex-1 bg-djp-navy text-djp-gold font-bold py-2.5 rounded-lg text-[12.5px] inline-flex items-center justify-center gap-2"><i
                    class="fa-solid fa-right-to-bracket"></i> Login Admin</a><a href="{{ url('/') }}"
                class="flex-1 bg-white border border-slate-300 py-2.5 rounded-lg text-[12.5px] font-semibold text-slate-700 inline-flex items-center justify-center gap-2">Dashboard</a>
        </div>
    @endslot
</x-error.layout>
