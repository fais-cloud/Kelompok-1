<?php

session_start();
foreach (array('data', 'antrian', 'undo') as $k) {
    if (!isset($_SESSION[$k])) $_SESSION[$k] = array();
}

/* ---------- SORTING ---------- */
function bubbleSort($arr, $key) {  
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j][$key] > $arr[$j + 1][$key]) {
                $t = $arr[$j]; $arr[$j] = $arr[$j + 1]; $arr[$j + 1] = $t;
            }
        }
    }
    return $arr;
}

function selectionSort($arr, $key) {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j][$key] < $arr[$min][$key]) $min = $j;

        }
        $t = $arr[$i]; $arr[$i] = $arr[$min]; $arr[$min] = $t;
    }
    return $arr;
}

/* ---------- PROFILING ---------- */
function ukurWaktu($fungsi, $arr, $key, $ulang) {
    $t = microtime(true);
    for ($k = 0; $k < $ulang; $k++) $fungsi($arr, $key);
    $total = (microtime(true) - $t) * 1000;
    return array($total, $total / $ulang);
}

function profilSorting($arr, $key, $dipakai, $ulang = 1000) {
     $fungsi = ($dipakai == 'bubble') ? 'bubbleSort' : 'selectionSort';
    list($total, $avg) = ukurWaktu($fungsi, $arr, $key, $ulang);
    return array(
        'jumlah'  => count($arr),
        'key'     => $key,
        'ulang'   => $ulang,
        'dipakai' => $dipakai,
        'total'   => $total,
        'avg'     => $avg,
    );
}

/* ---------- SEARCHING ---------- */
function linearSearch($arr, $nama) {
    foreach ($arr as $i => $s) {
        if (strcasecmp($s['nama'], $nama) == 0) return $i;
    }
    return -1;
}

function binarySearch($arr, $nim) {
    $low = 0; $high = count($arr) - 1;
    while ($low <= $high) {
        $mid = (int)(($low + $high) / 2);
        if ($arr[$mid]['nim'] == $nim) return $mid;
        if ($arr[$mid]['nim'] < $nim) $low = $mid + 1;
        else $high = $mid - 1;
    }
    return -1;
}

/* ---------- TRAVERSAL ---------- */
function barisData($no, $s) {
    return $no . '. ' . $s['nim'] . ' - ' . $s['nama'] . ' (nilai ' . $s['nilai'] . ')';
}

function traversalMaju($arr) {
    $hasil = array();
    $no = 1;
    foreach ($arr as $s) {
        $hasil[] = barisData($no++, $s);
    }
    return $hasil;
}

function traversalMundur($arr) {
    $hasil = array();
    $no = 1;
    for ($i = count($arr) - 1; $i >= 0; $i--) {
        $hasil[] = barisData($no++, $arr[$i]);
    }
    return $hasil;
}

/* ---------- TREE (Berdasarkan NIM) ---------- */
function tambahNode($node, $s) {
    if ($node === null) {
        return array('s' => $s, 'kiri' => null, 'kanan' => null);
    }
    if (strnatcmp($s['nim'], $node['s']['nim']) < 0) {
        $node['kiri'] = tambahNode($node['kiri'], $s);
    } else {
        $node['kanan'] = tambahNode($node['kanan'], $s);
    }
    return $node;
}

function bangunTree($arr) {
    $root = null;
    foreach ($arr as $s) $root = tambahNode($root, $s);
    return $root;
}

function preorder($node, &$hasil) {
    if ($node === null) return;
    $hasil[] = $node['s'];
    preorder($node['kiri'], $hasil);
    preorder($node['kanan'], $hasil);
}

function inorder($node, &$hasil) {
    if ($node === null) return;
    inorder($node['kiri'], $hasil);
    $hasil[] = $node['s'];
    inorder($node['kanan'], $hasil);
}

function postorder($node, &$hasil) {
    if ($node === null) return;
    postorder($node['kiri'], $hasil);
    postorder($node['kanan'], $hasil);
    $hasil[] = $node['s'];
}

function levelorder($root, &$hasil) {
    if ($root === null) return;
    $queue = array($root);
    while (count($queue) > 0) {
        $node = array_shift($queue);
        $hasil[] = $node['s'];
        if ($node['kiri']  !== null) $queue[] = $node['kiri'];
        if ($node['kanan'] !== null) $queue[] = $node['kanan'];
    }
}

function traversalTree($arr, $jenis) {
    $root = bangunTree($arr);
    $urut = array();
    if ($jenis == 'preorder')       preorder($root, $urut);
    elseif ($jenis == 'inorder')    inorder($root, $urut);
    elseif ($jenis == 'postorder')  postorder($root, $urut);
    else                            levelorder($root, $urut);

    $hasil = array();
    $no = 1;
    foreach ($urut as $s) $hasil[] = barisData($no++, $s);
    return $hasil;
}

function pohonTeks($node, $indent, $label, &$out) {
    if ($node === null) return;
    $out[] = $indent . $label . $node['s']['nim'] . ' - ' . $node['s']['nama'];
    pohonTeks($node['kiri'],  $indent . '    ', '[kiri]  ', $out);
    pohonTeks($node['kanan'], $indent . '    ', '[kanan] ', $out);
}

/* ---------- GRAPH ---------- */
function bangunGraph($arr, $batas) {
    $n = count($arr);
    $adj = array();
    for ($i = 0; $i < $n; $i++) $adj[$i] = array();
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if (abs($arr[$i]['nilai'] - $arr[$j]['nilai']) <= $batas) {
                $adj[$i][] = $j;
                $adj[$j][] = $i;
            }
        }
    }
    return $adj;
}

function bfsGraph($adj, $start) {
    $visited = array($start => true);
    $queue = array($start);
    $urutan = array();
    while (count($queue) > 0) {
        $u = array_shift($queue);
        $urutan[] = $u;
        foreach ($adj[$u] as $v) {
            if (!isset($visited[$v])) {
                $visited[$v] = true;
                $queue[] = $v;
            }
        }
    }
    return $urutan;
}

function dfsGraph($adj, $u, &$visited, &$urutan) {
    $visited[$u] = true;
    $urutan[] = $u;
    foreach ($adj[$u] as $v) {
        if (!isset($visited[$v])) dfsGraph($adj, $v, $visited, $urutan);
    }
}

function cariIndeksNim($arr, $nim) {
    foreach ($arr as $i => $s) {
        if ($s['nim'] == $nim) return $i;
    }
    return -1;
}
q
function daftarTetangga($arr, $adj) {
    $hasil = array();
    foreach ($arr as $i => $s) {
        $nama = array();
        foreach ($adj[$i] as $v) $nama[] = $arr[$v]['nama'];
        $hasil[] = $s['nama'] . ' → ' . (count($nama) ? implode(', ', $nama) : '(tidak terhubung)');
    }
    return $hasil;
}

/* ---------- PROSES FORM ---------- */
$tampil = $_SESSION['data'];
$pesan = '';
$hasilCari = null;  
$profil = null;   
$metodePilih = 'bubble';   
$jenisPilih = 'maju';
$hasilTraversal = null;
$judulTraversal = '';

$treePilih = 'preorder';
$hasilTree = null;
$judulTree = '';
$strukturTree = array();

$graphJenis = 'bfs';
$graphStart = '';
$graphBatas = 10;
$hasilGraph = null;
$judulGraph = '';
$tetanggaGraph = array();
$takTerjangkau = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $aksi = $_POST['aksi'];

    if ($aksi == 'tambah') {                       
        $_SESSION['antrian'][] = array(
            'nim'   => trim($_POST['nim']),
            'nama'  => trim($_POST['nama']),
            'nilai' => (int)$_POST['nilai']
        );
        $pesan = 'Data masuk antrian.';

    } elseif ($aksi == 'proses') {                 
        if (count($_SESSION['antrian']) > 0) {
            $s = array_shift($_SESSION['antrian']);
            $_SESSION['data'][] = $s;
            array_push($_SESSION['undo'], $s['nim']);
            $pesan = 'Data ' . $s['nama'] . ' disimpan.';
        } else {
            $pesan = 'Antrian kosong.';
        }

    } elseif ($aksi == 'undo') {                 
        if (count($_SESSION['undo']) > 0) {
            $nim = array_pop($_SESSION['undo']);
            foreach ($_SESSION['data'] as $i => $s) {
                if ($s['nim'] == $nim) {
                    array_unshift($_SESSION['antrian'], $s); 
                    unset($_SESSION['data'][$i]);
                    break;
                }
            }
            $_SESSION['data'] = array_values($_SESSION['data']);
            $pesan = 'Input terakhir dibatalkan.';

        } else {
            $pesan = 'Tidak ada yang bisa di-undo.';
        }

    } elseif ($aksi == 'urut') {
        $metode = ($_POST['metode'] == 'bubble') ? 'bubble' : 'selection';
        $metodePilih = $metode;
        $hasil = ($metode == 'bubble')
            ? bubbleSort($_SESSION['data'], 'nilai')
            : selectionSort($_SESSION['data'], 'nilai');
        $tampil = array_reverse($hasil);           // nilai tertinggi di atas

        // PROFILING otomatis setiap sorting dijalankan
        if (count($_SESSION['data']) > 1) {
            $profil = profilSorting($_SESSION['data'], 'nilai', $metode);
        }

    } elseif ($aksi == 'cari_linear') {
        $i = linearSearch($_SESSION['data'], $_POST['kunci']);
        $hasilCari = ($i >= 0)
            ? 'Nama ditemukan di nomor ' . ($i + 1)
            : 'Nama tidak ditemukan.';

    } elseif ($aksi == 'cari_binary') {
        $urut = bubbleSort($_SESSION['data'], 'nim');
        $i = binarySearch($urut, trim($_POST['kunci']));
        $hasilCari = ($i >= 0)
            ? '' . $urut[$i]['nama'] . ' (nilai ' . $urut[$i]['nilai'] . ')'
            : 'NIM tidak ditemukan.';

    } elseif ($aksi == 'traversal') {
        $jenis = $_POST['jenis'];
        $jenisPilih = $jenis;
        if ($jenis == 'maju') {
            $judulTraversal = 'data pertama ke terakhir';
            $hasilTraversal = traversalMaju($_SESSION['data']);
        } elseif ($jenis == 'mundur') {
            $judulTraversal = 'data terakhir ke pertama';
            $hasilTraversal = traversalMundur($_SESSION['data']);
        } 

    } elseif ($aksi == 'traversal_tree') {         // TRAVERSAL TREE (BST by NIM)
        $jenis = $_POST['jenis_tree'];
        if (!in_array($jenis, array('preorder', 'inorder', 'postorder', 'levelorder'))) {
            $jenis = 'preorder';
        }
        $treePilih = $jenis;

        $judulMap = array(
            'preorder'   => 'Preorder (Root → Kiri → Kanan)',
            'inorder'    => 'Inorder (Kiri → Root → Kanan), terurut menurut NIM',
            'postorder'  => 'Postorder (Kiri → Kanan → Root)',
            'levelorder' => 'Level-order (per tingkat, atas ke bawah)',
        );
        $judulTree = $judulMap[$jenis];
        $hasilTree = traversalTree($_SESSION['data'], $jenis);
        pohonTeks(bangunTree($_SESSION['data']), '', 'Root: ', $strukturTree);

    } elseif ($aksi == 'traversal_graph') {        // TRAVERSAL GRAPH (BFS / DFS)
        $graphJenis = ($_POST['jenis_graph'] == 'dfs') ? 'dfs' : 'bfs';
        $graphBatas = max(0, (int)$_POST['batas']);
        $graphStart = trim($_POST['mulai']);
        $judulGraph = ($graphJenis == 'bfs')
            ? 'BFS (melebar, pakai queue)'
            : 'DFS (mendalam, pakai rekursi)';

        $data = $_SESSION['data'];
        $hasilGraph = array();
        if (count($data) > 0) {
            $adj = bangunGraph($data, $graphBatas);
            $start = cariIndeksNim($data, $graphStart);
            if ($start < 0) {                       // NIM awal tidak valid -> pakai data pertama
                $start = 0;
                $graphStart = $data[0]['nim'];
            }

            if ($graphJenis == 'bfs') {
                $urutan = bfsGraph($adj, $start);
            } else {
                $visited = array();
                $urutan = array();
                dfsGraph($adj, $start, $visited, $urutan);
            }

            $no = 1;
            $dikunjungi = array();
            foreach ($urutan as $idx) {
                $hasilGraph[] = barisData($no++, $data[$idx]);
                $dikunjungi[$idx] = true;
            }
            
            foreach ($data as $i => $s) {
                if (!isset($dikunjungi[$i])) $takTerjangkau[] = $s['nama'];
            }
            $tetanggaGraph = daftarTetangga($data, $adj);
        }

    } elseif ($aksi == 'hapus') {               
        $nim = $_POST['nim'];
        $nama = '';
        foreach ($_SESSION['data'] as $i => $s) {
            if ($s['nim'] == $nim) {
                $nama = $s['nama'];
                unset($_SESSION['data'][$i]);
                break;
            }
        }
        $_SESSION['data'] = array_values($_SESSION['data']);

        for ($k = count($_SESSION['undo']) - 1; $k >= 0; $k--) {
            if ($_SESSION['undo'][$k] == $nim) {
                unset($_SESSION['undo'][$k]);
                break;
            }
        }
        $_SESSION['undo'] = array_values($_SESSION['undo']);

        $tampil = $_SESSION['data'];
        $pesan = ($nama !== '') ? 'Data ' . $nama . ' dihapus.' : 'Data tidak ditemukan.';  

    } elseif ($aksi == 'reset') {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sistem Manajemen Nilai Mahasiswa</title>

<link rel="icon" type="image/svg+xml" href="logo.svg?v=2">

<style>
    /* ===== 1. VARIABEL WARNA  ===== */
    :root {
        --biru-tua:   #1e3a8a;
        --biru:       #2563eb;
        --biru-muda:  #dbeafe;
        --abu-bg:     #f1f5f9;
        --abu-garis:  #e2e8f0;
        --abu-teks:   #334155;
        --merah:      #dc2626;
        --abu-tombol: #64748b;
        --hijau:      #16a34a;
    }

    /* ===== 2. RESET & PENGATURAN DASAR ===== */
    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 24px 16px;
        font-family: "Segoe UI", Tahoma, Arial, sans-serif;
        font-size: 15px;
        color: var(--abu-teks);
        background: var(--abu-bg);
    }

    /* ===== 3. PEMBUNGKUS HALAMAN ===== */
    .container {
        max-width: 860px;
        margin: 0 auto;
    }

    /* ===== 4. JUDUL UTAMA ===== */
    h2.judul {
        font-size: 22px;
        font-weight: 600;
        letter-spacing: 0.3px;
        color: var(--biru-tua);
        display: inline-block;
        margin: 0 0 20px;
        padding: 12px 28px;
        background: var(--biru-muda);
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        box-shadow: 0 3px 8px rgba(59, 130, 246, 0.2);
    }

    /* ===== 5. KARTU (satu kartu = satu bagian/fitur) ===== */
    .card {
        background: #fff;
        border: 1px solid var(--abu-garis);
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 18px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
    }

    .card h3 {
        margin: 0 0 14px;
        padding-bottom: 10px;
        font-size: 17px;
        color: var(--biru-tua);
        border-bottom: 2px solid var(--biru-muda);
    }

    /* ===== 6. FORM & INPUT ===== */
    .baris {                      
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .baris label {
        font-weight: 600;
    }

    input[type="text"],
    input[type="number"],
    select {
        padding: 8px 10px;
        font-size: 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: var(--abu-teks);
        transition: border-color .15s, box-shadow .15s;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        outline: none;
        border-color: var(--biru);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    }

    /* ===== 7. TOMBOL ===== */
    button {
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        background: var(--biru);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background .15s, transform .05s;
    }
    button:hover  { background: var(--biru-tua); }
    button:active { transform: scale(0.96); }

    button.abu    { background: var(--abu-tombol); }
    button.abu:hover { background: #475569; }

    button.merah  { background: var(--merah); }
    button.merah:hover { background: #b91c1c; }

    /* ===== 8. KOTAK INFORMASI (antrian, profiling, hasil) ===== */
    .info {
        margin: 12px 0;
        padding: 10px 14px;
        background: var(--abu-bg);
        border-left: 4px solid var(--biru);
        border-radius: 6px;
    }

    .hasil {
        margin: 12px 0 0;
        padding: 10px 14px;
        background: #f0fdf4;
        border-left: 4px solid var(--hijau);
        border-radius: 6px;
        font-style: italic;
    }

    .daftar {                     /* hasil traversal */
        margin: 8px 0 0;
        padding: 0;
        list-style: none;
    }
    .daftar li {
        padding: 6px 12px;
        border-bottom: 1px dashed var(--abu-garis);
    }
    .daftar li:last-child { border-bottom: none; }

    .pohon {                      /* gambar struktur tree */
        margin: 8px 0 0;
        padding: 10px 14px;
        background: var(--abu-bg);
        border-radius: 6px;
        font-family: Consolas, "Courier New", monospace;
        font-size: 13px;
        overflow-x: auto;
    }

    .sub {                        /* sub-judul di dalam kartu */
        margin: 14px 0 4px;
        font-weight: 600;
        color: var(--biru-tua);
    }

    /* ===== 9. TABEL ===== */
    .tabel-wrap {                 /* supaya tabel bisa digeser di layar kecil */
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    th, td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid var(--abu-garis);
    }

    th {
        background: var(--biru-tua);
        color: #fff;
        font-weight: 600;
    }
    th:first-child { border-top-left-radius: 8px; }
    th:last-child  { border-top-right-radius: 8px; }

    tr:nth-child(even) td { background: #f8fafc; }   /* garis selang-seling */
    tr:hover td           { background: var(--biru-muda); }

/* ===== 10. RESPONSIF UNTUK HP ===== */
@media (max-width: 600px) {
    body {
        padding: 12px 10px;
        font-size: 14px;
    }

    h2.judul {
        display: block;
        text-align: center;
        font-size: 18px;
        padding: 10px 16px;
        border-radius: 18px;
    }

    .card {
        padding: 14px;
    }

    /* Form tersusun ke bawah, bukan berjajar */
    .baris {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .baris label {
        margin-bottom: -4px;
    }

    /* 16px mencegah browser iPhone otomatis zoom saat input diketuk */
    input[type="text"],

    input[type="number"],
    select {
        width: 100%;
        font-size: 16px;
    }

    /* Tombol selebar layar (tombol Hapus di tabel tidak terpengaruh) */
    .baris button {
        width: 100%;
        padding: 10px 16px;
    }

    /* Tabel lebih ringkas, tetap bisa digeser ke samping */
    th, td {
        padding: 8px;
        font-size: 13px;
    }
}

</style>
</head>
<body>
<div class="container">

<h2 class="judul">Sistem Manajemen Nilai Mahasiswa</h2>

<div class="card">
    <h3>Tambah ke Antrian</h3>
    <form method="post" class="baris">
        <input type="hidden" name="aksi" value="tambah">
        <label>NIM:</label> <input type="text" name="nim" required>
        <label>Nama:</label> <input type="text" name="nama" required>
        <label>Nilai:</label> <input type="number" name="nilai" min="0" max="100" required>
        <button>Tambah</button>
    </form>

    <div class="info">Antrian: <?php
        $n = array();
        foreach ($_SESSION['antrian'] as $s) $n[] = htmlspecialchars($s['nama']);

        echo count($n) ? implode(' &rarr; ', $n) : '(kosong)';
    ?></div>

    <div class="baris">
        <form method="post">
            <input type="hidden" name="aksi" value="proses"><button>Proses Antrian</button>
        </form>
        <form method="post">
            <input type="hidden" name="aksi" value="undo"><button class="abu">Undo</button>
        </form>
        <form method="post">
            <input type="hidden" name="aksi" value="reset"><button class="merah">Reset</button>
        </form>
    </div>
</div>

<div class="card">
    <h3>Urutkan Nilai</h3>
    <form method="post" class="baris">
        <input type="hidden" name="aksi" value="urut">
        <select name="metode">
            <option value="bubble" <?php if ($metodePilih == 'bubble') echo 'selected'; ?>>Bubble Sort</option>
            <option value="selection" <?php if ($metodePilih == 'selection') echo 'selected'; ?>>Selection Sort</option>
        </select>
        <button>Urutkan</button>
    </form>

    <?php if ($profil): ?>
    <div class="info">
        <?php echo ucfirst($profil['dipakai']); ?> Sort :
        <?php echo round($profil['total'], 2); ?> ms
        (rata-rata <?php echo round($profil['avg'], 4); ?> ms)
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Cari Data</h3>
    <form method="post" class="baris">
        <input type="text" name="kunci" placeholder="Nama / NIM" required>
        <button name="aksi" value="cari_linear">Cari Nama</button>
        <button name="aksi" value="cari_binary">Cari NIM</button>
    </form>

    <?php if ($hasilCari) echo '<div class="hasil">' . htmlspecialchars($hasilCari) . '</div>'; ?>
</div>

<div class="card">
    <h3>Traversal Array</h3>
    <form method="post" class="baris">
        <input type="hidden" name="aksi" value="traversal">
        <select name="jenis">
            <option value="maju" <?php if ($jenisPilih == 'maju') echo 'selected'; ?>>Maju</option>
            <option value="mundur" <?php if ($jenisPilih == 'mundur') echo 'selected'; ?>>Mundur</option>
        </select>
        <button>Telusuri</button>
    </form>

    <?php if ($hasilTraversal !== null): ?>
        <p><b><?php echo htmlspecialchars($judulTraversal); ?></b></p>
        <?php if (count($hasilTraversal) == 0): ?>
            <p><i>(kosong)</i></p>
        <?php else: ?>
            <ul class="daftar">
            <?php foreach ($hasilTraversal as $baris): ?>
                <li><?php echo htmlspecialchars($baris); ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Traversal Tree (Berdasarkan NIM)</h3>
    <form method="post" class="baris">
        <input type="hidden" name="aksi" value="traversal_tree">
        <select name="jenis_tree">
            <option value="preorder"   <?php if ($treePilih == 'preorder')   echo 'selected'; ?>>Preorder</option>
            <option value="inorder"    <?php if ($treePilih == 'inorder')    echo 'selected'; ?>>Inorder</option>
            <option value="postorder"  <?php if ($treePilih == 'postorder')  echo 'selected'; ?>>Postorder</option>
            <option value="levelorder" <?php if ($treePilih == 'levelorder') echo 'selected'; ?>>Level-order</option>
        </select>
        <button>Telusuri Tree</button>
    </form>

    <?php if ($hasilTree !== null): ?>
        <p><b><?php echo htmlspecialchars($judulTree); ?></b></p>
        <?php if (count($hasilTree) == 0): ?>
            <p><i>(kosong)</i></p>
        <?php else: ?>
            <ul class="daftar">
            <?php foreach ($hasilTree as $baris): ?>
                <li><?php echo htmlspecialchars($baris); ?></li>
            <?php endforeach; ?>
            </ul>

            <div class="sub">Struktur tree</div>
            <pre class="pohon"><?php echo htmlspecialchars(implode("\n", $strukturTree)); ?></pre>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Traversal Graph (BFS / DFS)</h3>
    <form method="post" class="baris">
        <input type="hidden" name="aksi" value="traversal_graph">

        <label>Mulai dari:</label>
        <select name="mulai">
            <?php foreach ($_SESSION['data'] as $s): ?>
                <option value="<?php echo htmlspecialchars($s['nim']); ?>"
                    <?php if ($graphStart == $s['nim']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($s['nim'] . ' - ' . $s['nama']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="jenis_graph">
            <option value="bfs" <?php if ($graphJenis == 'bfs') echo 'selected'; ?>>BFS</option>
            <option value="dfs" <?php if ($graphJenis == 'dfs') echo 'selected'; ?>>DFS</option>
        </select>

        <label>Selisih nilai maks:</label>
        <input type="number" name="batas" min="0" max="100" value="<?php echo $graphBatas; ?>" required>

        <button>Telusuri Graph</button>
    </form>

    <div class="info">
        Dua mahasiswa dianggap terhubung jika selisih nilainya tidak lebih dari batas di atas.
    </div>

    <?php if ($hasilGraph !== null): ?>
        <p><b><?php echo htmlspecialchars($judulGraph); ?></b></p>
        <?php if (count($hasilGraph) == 0): ?>
            <p><i>(kosong)</i></p>
        <?php else: ?>
            <ul class="daftar">
            <?php foreach ($hasilGraph as $baris): ?>
                <li><?php echo htmlspecialchars($baris); ?></li>
            <?php endforeach; ?>
            </ul>

            <?php if (count($takTerjangkau) > 0): ?>
                <div class="info">Tidak terjangkau dari simpul awal:
                    <?php echo htmlspecialchars(implode(', ', $takTerjangkau)); ?></div>
            <?php endif; ?>

            <div class="sub">Daftar ketetanggaan</div>
            <ul class="daftar">
            <?php foreach ($tetanggaGraph as $baris): ?>
                <li><?php echo htmlspecialchars($baris); ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Data Mahasiswa</h3>
    <div class="tabel-wrap">
    <table>
        <tr><th>No</th><th>NIM</th><th>Nama</th><th>Nilai</th><th>Aksi</th></tr>
        <?php foreach ($tampil as $i => $s): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($s['nim']); ?></td>
            <td><?php echo htmlspecialchars($s['nama']); ?></td>
            <td><?php echo $s['nilai']; ?></td>
            <td>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Hapus data ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="nim" value="<?php echo htmlspecialchars($s['nim']); ?>">
                    <button class="merah">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

</div>
</body>
</html>
