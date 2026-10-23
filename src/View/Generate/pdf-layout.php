<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #000;
        }

        /* --- STYLE HALAMAN 1 (REKAP) --- */
        .kop-rekap {
            text-align: center;
            margin-bottom: 15px;
        }

        .kop-rekap h1 {
            font-size: 16px;
            margin: 0 0 3px 0;
            font-weight: bold;
        }

        .kop-rekap h2 {
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }

        .garis-kop {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 8px;
        }

        .page-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

          /* --- STYLE TABEL REKAP --- */
        .table-rekap {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Memaksa lebar kolom sesuai persentase */
        }

        .table-rekap th,
        .table-rekap td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word; /* Agar teks panjang turun ke bawah */
        }

        .table-rekap th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }

        .table-rekap td {
            font-size: 10px; /* Ukuran font isi tabel sedikit dikecilkan */
        }

        /* PEMBAGIAN LEBAR KOLOM (TOTAL HARUS 100%) */
        .col-no { width: 4%; }
        .col-foto-kecil { width: 8%; }
        .col-foto-besar { width: 9%; }
        .col-nama { width: 13%; }
        .col-ttl { width: 14%; }
        .col-alamat { width: 18%; } /* Dikurangi dari 28% agar muat kolom lain */
        .col-ayah { width: 10%; }
        .col-pelatih { width: 10%; }
        .col-ranting { width: 9%; }  /* KOLOM BARU */

        /* --- STYLE HALAMAN 2+ (GRID 2x2) --- */
        .page-break {
            page-break-before: always;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-cell {
            width: 50%;
            vertical-align: top;
            padding: 15px;
            /* Jarak antar kartu */
        }

        .card-item {
            /* Kotak pembungkus tiap peserta */
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #fff;
            min-height: 80mm;
            /* Tinggi minimal agar rapi dan muat 2x2 di A4 */
        }

        .card-data {
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
        }

        .card-data table {
            width: 100%;
        }

        .card-data td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .img-kecil {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #ccc;
            display: block;
        }

        .img-besar {
            width: 100px;
            height: 171px; 
            object-fit: cover;
            border: 1px solid #ccc;
            display: block;
        }
    </style>
</head>

<body>
    <?= $content ?>
</body>

</html>