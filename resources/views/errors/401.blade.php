<x-error.layout code="401" title="Belum Login" subtitle="Unauthorized" icon="fa-user-lock" iconBg="bg-slate-50"
    iconBorder="border-slate-200" iconColor="text-djp-navy">
    Kamu belum login sebagai admin. Silakan login terlebih dahulu untuk mengakses Panel Admin MPNWEB.
    @slot('extra')
        <a href="{{ route('login') }}"
            class="block w-full bg-djp-navy text-djp-gold font-bold py-2.5 rounded-lg text-[12.5px] text-center"><i
                class="fa-solid fa-right-to-bracket mr-1"></i> Login Admin</a>
    @endslot
</x-error.layout>
