<x-error.layout code="429" title="Terlalu Banyak Percobaan" subtitle="Too Many Requests" icon="fa-stopwatch"
    iconBg="bg-orange-50" iconBorder="border-orange-200" iconColor="text-orange-600">
    Kamu melakukan terlalu banyak percobaan dalam waktu singkat. Sistem membatasi untuk keamanan (anti brute-force login
    admin).
    @slot('extra')
        <div
            class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-[11px] text-orange-800 text-center font-medium">
            Coba lagi dalam <span class="font-bold font-mono">60 detik</span>
        </div>
        <a href="{{ url('/') }}"
            class="block w-full bg-white border border-slate-300 py-2.5 rounded-lg text-[12.5px] font-semibold text-center">Kembali
            ke Dashboard</a>
    @endslot
</x-error.layout>
