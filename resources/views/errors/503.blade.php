<x-error.layout code="503" title="Sistem Dalam Pemeliharaan" subtitle="Maintenance Mode" icon="fa-screwdriver-wrench"
    iconBg="bg-amber-50" iconBorder="border-amber-200" iconColor="text-amber-600" :showLog="true" :logBase64="$logBase64 ?? null"
    :logFileName="$logFileName ?? null" :timestamp="$timestamp ?? null">

    Aplikasi MPNWEB sedang dalam pemeliharaan terjadwal. Estimasi selesai dalam beberapa menit. Silakan coba lagi nanti.

    <x-slot name="extra">
        <div class="bg-djp-navy text-white rounded-lg p-3 text-center">
            <div class="text- text-djp-gold font-mono font-bold uppercase tracking-wider">Maintenance Mode Aktif</div>

        </div>
    </x-slot>

</x-error.layout>
