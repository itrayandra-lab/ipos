<?php

namespace App\Helpers;

class Terbilang
{
    public static function make($angka)
    {
        $angka = abs($angka);
        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        }
        else if ($angka < 20) {
            $terbilang = self::make($angka - 10) . " belas";
        }
        else if ($angka < 100) {
            $terbilang = self::make($angka / 10) . " puluh" . self::make($angka % 10);
        }
        else if ($angka < 200) {
            $terbilang = " seratus" . self::make($angka - 100);
        }
        else if ($angka < 1000) {
            $terbilang = self::make($angka / 100) . " ratus" . self::make($angka % 100);
        }
        else if ($angka < 2000) {
            $terbilang = " seribu" . self::make($angka - 1000);
        }
        else if ($angka < 1000000) {
            $terbilang = self::make($angka / 1000) . " ribu" . self::make($angka % 1000);
        }
        else if ($angka < 1000000000) {
            $terbilang = self::make($angka / 1000000) . " juta" . self::make($angka % 1000000);
        }

        return trim($terbilang);
    }
}
