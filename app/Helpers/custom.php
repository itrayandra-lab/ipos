<?php

if (!function_exists('normalize_expiry_date')) {
    /**
     * Normalisasi input expired date ke format Y-m-d untuk disimpan.
     * Input bulan-tahun ("2028-01" dari <input type="month">) disimpan
     * sebagai hari TERAKHIR bulan tersebut, agar produk dianggap
     * berlaku sampai akhir bulan pada logika FEFO/expired.
     * Input tanggal penuh (Y-m-d) diteruskan apa adanya.
     */
    function normalize_expiry_date($value)
    {
        if (empty($value)) {
            return null;
        }
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', trim($value))) {
            return \Carbon\Carbon::createFromFormat('Y-m', trim($value))->endOfMonth()->toDateString();
        }
        return $value;
    }
}
