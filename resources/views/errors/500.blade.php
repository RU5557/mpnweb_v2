<x-error.layout code="500" title="Terjadi Kendala Sistem / Timeout" subtitle="System Alert"
    icon="fa-triangle-exclamation" iconBg="bg-rose-50" iconBorder="border-rose-200" iconColor="text-rose-600"
    :showLog="true" :logBase64="$logBase64 ?? null" :logFileName="$logFileName ?? null" :timestamp="$timestamp ?? null">
    Aplikasi MPNWEB mengalami gangguan teknis sementara. Tim administrator siap membantu memulihkan sistem.
    @slot('extra')
        {{-- <div class="bg-amber-50/50 border border-amber-200/50 rounded-lg p-3 text-[11px] text-amber-800 text-left">
            <i class="fa-solid fa-circle-info mr-1"></i> File log .txt berisi detail error lengkap (file, line, query, stack
            trace) yang bisa kamu kirim ke AI untuk dibantu debug.
        </div> --}}
    @endslot
</x-error.layout>
