@props(['logBase64' => null, 'logFileName' => null, 'timestamp' => null])

<div class="space-y-3">
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-left text-[11.5px] text-slate-600 space-y-2">
        <p class="font-bold text-slate-700 text-[11px] uppercase tracking-wider flex items-center gap-1.5"><i
                class="fa-solid fa-list-check text-djp-navy"></i> Langkah Penyelesaian:</p>
        <ol class="list-decimal list-inside space-y-1.5 pl-1 font-medium">
            <li>Klik <span class="text-djp-navy font-bold bg-white border px-1.5 py-0.5 rounded">Unduh Log Error
                    (.txt)</span></li>
            <li>Klik <span
                    class="text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">Hubungi
                    Admin</span></li>
            <li>Lampirkan file <span class="font-mono font-bold">.txt</span> ke chat admin</li>
        </ol>
        @if ($timestamp)
            <div class="pt-2 text-[11px] text-slate-500 font-mono">
                <i class="fa-regular fa-clock mr-1"></i>
                <span id="error-time" data-utc="{{ \Carbon\Carbon::parse($timestamp)->toISOString() }}">
                    {{ \Carbon\Carbon::parse($timestamp)->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') }} WIB
                </span>
            </div>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        @if ($logBase64)
            <a href="data:text/plain;base64,{{ $logBase64 }}" download="{{ $logFileName ?? 'Error_Log_MPNWEB.txt' }}"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-djp-navy font-bold text-[12.5px] px-4 py-2.5 rounded-lg transition shadow-sm">
                <i class="fa-solid fa-file-arrow-down"></i> Unduh Log Error (.txt)
            </a>
        @endif

        @php
            $waNumber = config('admin.wa_number', '628123456789');
            $waText = rawurlencode(
                'Halo Admin MPNWEB, saya mengalami error pada ' .
                    ($timestamp
                        ? \Carbon\Carbon::parse($timestamp)->format('Y-m-d H:i:s')
                        : now()->format('Y-m-d H:i:s')) .
                    '. Saya lampirkan file log .txt nya.',
            );
            $waUrl = "https://wa.me/{$waNumber}?text={$waText}";
        @endphp

        <a href="{{ $waUrl }}" target="_blank"
            class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[12.5px] px-4 py-2.5 rounded-lg transition shadow-sm">
            <i class="fa-brands fa-whatsapp text-[15px]"></i> Hubungi Admin
        </a>
    </div>

    {{-- Untuk debug AI - textarea copyable (hidden by default, bisa di-toggle) --}}
    {{-- <details class="group">
        <summary
            class="text-[11px] font-bold text-slate-500 hover:text-djp-navy cursor-pointer flex items-center gap-1.5 list-none">
            <i class="fa-solid fa-chevron-right text-[10px] transition-transform group-open:rotate-90"></i> Lihat log
            untuk debugging
        </summary>
        <div
            class="mt-2 bg-djp-navy text-slate-200 rounded-lg p-3 font-mono text-[10px] overflow-auto max-h-60 whitespace-pre-wrap break-all border border-djp-navyBorder">
            {{ base64_decode($logBase64) }}</div>
    </details> --}}
</div>
