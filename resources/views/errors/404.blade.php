<x-error.layout code="404" title="Halaman Tidak Ditemukan" subtitle="Not Found" icon="fa-map-location-dot"
    iconBg="bg-slate-50" iconBorder="border-slate-200" iconColor="text-djp-navy">
    URL yang kamu akses tidak tersedia, sudah dipindahkan, atau data tidak ditemukan di database MPNWEB.
    @slot('extra')
        <div class="flex gap-3">
            <a href="{{ url('/') }}"
                class="flex-1 bg-djp-navy hover:bg-djp-navyHover text-djp-gold border border-djp-gold/20 font-bold text-[12.5px] px-4 py-2.5 rounded-lg transition inline-flex items-center justify-center gap-2"><i
                    class="fa-solid fa-house"></i> Dashboard</a>
            <a href="javascript:history.back()"
                class="flex-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-[12.5px] px-4 py-2.5 rounded-lg transition inline-flex items-center justify-center gap-2"><i
                    class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>
    @endslot
</x-error.layout>
