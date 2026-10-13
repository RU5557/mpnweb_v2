<x-error.layout code="419" title="Sesi Berakhir" subtitle="Page Expired" icon="fa-hourglass-half" iconBg="bg-slate-50"
    iconBorder="border-slate-200" iconColor="text-slate-500">
    Sesi kamu telah berakhir. Silakan refresh halaman dan login kembali.
    @slot('extra')
        <a href="{{ url('/') }}"
            class="block w-full bg-djp-navy hover:bg-djp-navyHover text-djp-gold font-bold py-2.5 rounded-lg text-[12.5px] text-center transition"><i
                class="fa-solid fa-rotate mr-1"></i> Refresh Halaman</a>
    @endslot
</x-error.layout>
