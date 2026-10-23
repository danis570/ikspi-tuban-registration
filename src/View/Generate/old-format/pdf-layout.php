<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm 15mm 10mm 15mm
            /* Margin disesuaikan */
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            /* Sedikit diperbesar agar mirip aslinya */
            color: #000;
            line-height: 1.3;
        }

        /* --- STYLE KOP SURAT --- */
        .kop-surat {
            width: 100%;
            margin-bottom: 10px;
        }

        /* --- STYLE JUDUL & KOTAK PULAU JAWA --- */
        .header-section {
            width: 100%;
            margin-bottom: 15px;
            position: relative;
        }

        /* Kotak Pulau Jawa */
        .box-pulau-jawa {
            position: absolute;
            left: 0;
            top: 5px;
            border: 1px solid #000;
            padding: 5px 20px;
            font-size: 12px;
        }

        /* Judul Tengah */
        .title-center {
            text-align: center;
            margin: 0 auto;
            width: 70%;
            /* Agar tidak menabrak kotak kiri */
        }

        .title-center h3 {
            margin: 0;
            font-size: 16px;
            text-decoration: underline;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .title-center h4 {
            margin: 0;
            font-size: 12px;
            font-weight: normal;
        }

        /* --- STYLE DATA (TANGGAL, HARI, DLL) --- */
        .data-section {
            width: 100%;
            margin-left: 25%;
            /* Menggeser teks agak ke kanan seperti gambar */
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 0;
            vertical-align: top;
            font-size: 12px;
            font-family: "Times New Roman", Times, serif;
            /* Memastikan font serif */
        }

        .data-table .label {
            width: 25%;
            text-transform: uppercase;
        }

        .data-table .colon {
            width: 2%;
        }

        .data-table .value {
            width: 73%;
            text-transform: uppercase;
            /* Di gambar huruf besar semua */
        }

        /* --- STYLE DATA DIRI & PERNYATAAN --- */
        .data-diri-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-diri-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 12px;
            /* Ukuran font seragam */
        }

        .data-diri-table .label {
            width: 22%;
            /* Lebar label kiri */
            text-transform: uppercase;
        }

        .data-diri-table .colon {
            width: 1%;
            /* Lebar titik dua */
        }

        .data-diri-table .value {
            width: 77%;
            text-transform: uppercase;
        }

        /* List Pernyataan */
        .pernyataan-list {
            margin: 5px 0 10px 15px;
            padding-left: 0;
            font-size: 12px;
        }

        .pernyataan-list li {
            margin-bottom: 2px;
            padding-left: 5px;
        }

        /* Rincian Biaya */
        .biaya-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .biaya-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .biaya-table .no {
            width: 3%;
            text-align: right;
            padding-right: 5px;
        }

        .biaya-table .desc {
            width: 55%;
        }

        .biaya-table .colon {
            width: 2%;
            text-align: center;
        }

        .biaya-table .rp {
            width: 5%;
            text-align: left;
        }

        .biaya-table .amount {
            width: 35%;
            text-align: left;
        }

        .biaya-total {
            margin-top: 5px;
            font-style: italic;
            font-weight: bold;
        }

        .biaya-total td {
            padding-top: 5px;
            border-top: 1.5px solid #000;
            /* Garis atas untuk total */
        }

                /* --- STYLE TANDA TANGAN --- */
        .ttd-container {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .ttd-title {
            font-size: 12px;
            line-height: 1.2;
        }

        .ttd-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Bagian Tengah (Ketua) */
        .ttd-center {
            width: 100%;
            text-align: center;
            clear: both;
        }

        .ttd-center-title {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .img-qr {
            width: 70px;
            height: 70px;
            margin: 5px auto;
            display: block;
        }

        .ttd-center-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

         /* --- STYLE UNTUK HALAMAN FOTO (LEMBAR 2) --- */
        .participant-page {
            /* Memaksa halaman baru sebelum elemen ini dimulai */
            page-break-before: always; 
            /* Mencegah halaman ini terpotong di tengah */
            page-break-inside: avoid;
            /* Memaksa halaman baru setelah elemen ini selesai (opsional) */
            page-break-after: always;
            
            /* Pastikan tidak ada margin atas yang berlebih dari body */
            margin-top: 0;
            padding-top: 20px; /* Beri jarak aman dari atas kertas */
        }
    </style>
</head>

<body>
    <?= $content ?>
</body>

</html>