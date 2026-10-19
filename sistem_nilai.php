<?php
/*
 * Sistem Data Nilai Mahasiswa (versi PHP murni / CLI)
 * Jalankan: php nilai_mahasiswa.php
 */

/* ---------- SORTING (ascending berdasarkan $key) ---------- */
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
// Dijalankan berulang karena satu kali eksekusi pada data kecil
// terlalu cepat untuk diukur secara akurat.
function ukurWaktu($fungsi, $arr, $key, $ulang) {
    $t = microtime(true);
    for ($k = 0; $k < $ulang; $k++) $fungsi($arr, $key);
    $total = (microtime(true) - $t) * 1000;
    return array($total, $total / $ulang);
}

function profilSorting($arr, $key, $dipakai, $ulang = 1000) {
    $fungsi = ($dipakai == 'bubble') ? 'bubbleSort' : 'selectionSort';
    list($total, $avg) = ukurWaktu($fungsi, $arr, $key, $ulang);
    return array('dipakai' => $dipakai, 'total' => $total, 'avg' => $avg);
}

/* ---------- SEARCHING ---------- */
// Linear search: cek satu per satu (data tidak perlu urut)
function linearSearch($arr, $nama) {
    foreach ($arr as $i => $s) {
        if (strcasecmp($s['nama'], $nama) == 0) return $i;
    }
    return -1;
}

// Binary search: data HARUS sudah urut berdasarkan NIM
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
    foreach ($arr as $s) $hasil[] = barisData($no++, $s);
    return $hasil;
}

function traversalMundur($arr) {
    $hasil = array();
    $no = 1;
    for ($i = count($arr) - 1; $i >= 0; $i--) $hasil[] = barisData($no++, $arr[$i]);
    return $hasil;
}

/* ---------- TREE (Binary Search Tree berdasarkan NIM) ---------- */
// NIM lebih kecil -> kiri, NIM sama/lebih besar -> kanan.
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

// Struktur tree sebagai teks berindentasi
function pohonTeks($node, $indent, $label, &$out) {
    if ($node === null) return;
    $out[] = $indent . $label . $node['s']['nim'] . ' - ' . $node['s']['nama'];
    pohonTeks($node['kiri'],  $indent . '    ', '[kiri]  ', $out);
    pohonTeks($node['kanan'], $indent . '    ', '[kanan] ', $out);
}

/* ---------- GRAPH ---------- */
// Simpul = mahasiswa. Terhubung jika selisih nilai <= $batas (adjacency list).
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

function daftarTetangga($arr, $adj) {
    $hasil = array();
    foreach ($arr as $i => $s) {
        $nama = array();
        foreach ($adj[$i] as $v) $nama[] = $arr[$v]['nama'];
        $hasil[] = $s['nama'] . ' -> ' . (count($nama) ? implode(', ', $nama) : '(tidak terhubung)');
    }
    return $hasil;
}

/* ---------- INPUT / OUTPUT KONSOL ---------- */
function baca($prompt) {
    echo $prompt;
    $baris = fgets(STDIN);
    if ($baris === false) {            // input habis (EOF) -> keluar
        echo "\nSampai jumpa.\n";
        exit;
    }
    return trim($baris);
}

function cetakDaftar($judul, $baris) {
    echo "\n$judul\n";
    if (count($baris) == 0) { echo "  (kosong)\n"; return; }
    foreach ($baris as $b) echo "  $b\n";
}

function tampilData($arr) {
    echo "\n=== Data Mahasiswa ===\n";
    if (count($arr) == 0) { echo "  (kosong)\n"; return; }
    printf("  %-4s %-12s %-25s %s\n", 'No', 'NIM', 'Nama', 'Nilai');
    foreach ($arr as $i => $s) {
        printf("  %-4d %-12s %-25s %d\n", $i + 1, $s['nim'], $s['nama'], $s['nilai']);
    }
}

function tampilAntrian($antrian) {
    $n = array();
    foreach ($antrian as $s) $n[] = $s['nama'];
    echo "Antrian: " . (count($n) ? implode(' -> ', $n) : '(kosong)') . "\n";
}

/* ---------- STATE (pengganti $_SESSION) ---------- */
$data    = array();   // data mahasiswa yang sudah disimpan
$antrian = array();   // queue
$undo    = array();   // stack (menyimpan NIM)

/* ---------- MENU UTAMA ---------- */
while (true) {
    echo "\n==============================================\n";
    echo "   SISTEM DATA NILAI MAHASISWA\n";
    echo "==============================================\n";
    tampilAntrian($antrian);
    echo " 1. Tambah ke antrian\n";
    echo " 2. Proses antrian\n";
    echo " 3. Undo\n";
    echo " 4. Urutkan nilai (Bubble / Selection)\n";
    echo " 5. Cari nama (Linear Search)\n";
    echo " 6. Cari NIM (Binary Search)\n";
    echo " 7. Traversal (maju / mundur)\n";
    echo " 8. Traversal Tree (BST berdasarkan NIM)\n";
    echo " 9. Traversal Graph (BFS / DFS)\n";
    echo "10. Hapus data\n";
    echo "11. Tampilkan data\n";
    echo "12. Reset\n";
    echo " 0. Keluar\n";
    $pilih = baca("Pilih menu: ");

    if ($pilih == '1') {                           // ENQUEUE
        $nim  = baca("NIM   : ");
        $nama = baca("Nama  : ");
        $nilai = baca("Nilai (0-100): ");
        if ($nim === '' || $nama === '' || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
            echo "Input tidak valid.\n";
        } else {
            $antrian[] = array('nim' => $nim, 'nama' => $nama, 'nilai' => (int)$nilai);
            echo "Data masuk antrian.\n";
        }

    } elseif ($pilih == '2') {                     // DEQUEUE + PUSH ke stack
        if (count($antrian) > 0) {
            $s = array_shift($antrian);
            $data[] = $s;
            array_push($undo, $s['nim']);
            echo 'Data ' . $s['nama'] . " disimpan.\n";
        } else {
            echo "Antrian kosong.\n";
        }

    } elseif ($pilih == '3') {                     // POP dari stack
        if (count($undo) > 0) {
            $nim = array_pop($undo);
            foreach ($data as $i => $s) {
                if ($s['nim'] == $nim) {
                    array_unshift($antrian, $s);   // balik ke depan antrian
                    unset($data[$i]);
                    break;
                }
            }
            $data = array_values($data);
            echo "Input terakhir dibatalkan.\n";
        } else {
            echo "Tidak ada yang bisa di-undo.\n";
        }

    } elseif ($pilih == '4') {                     // SORTING + PROFILING
        $m = baca("Metode (1 = Bubble, 2 = Selection): ");
        $metode = ($m == '1') ? 'bubble' : 'selection';
        $hasil = ($metode == 'bubble')
            ? bubbleSort($data, 'nilai')
            : selectionSort($data, 'nilai');
        $tampil = array_reverse($hasil);           // nilai tertinggi di atas
        tampilData($tampil);

        if (count($data) > 1) {
            $profil = profilSorting($data, 'nilai', $metode);
            echo "\n" . ucfirst($profil['dipakai']) . ' Sort : '
                . round($profil['total'], 2) . ' ms (rata-rata '
                . round($profil['avg'], 4) . " ms)\n";
        }

    } elseif ($pilih == '5') {                     // LINEAR SEARCH
        $kunci = baca("Nama yang dicari: ");
        $i = linearSearch($data, $kunci);
        echo ($i >= 0) ? 'Nama ditemukan di nomor ' . ($i + 1) . "\n" : "Nama tidak ditemukan.\n";

    } elseif ($pilih == '6') {                     // BINARY SEARCH
        $kunci = baca("NIM yang dicari: ");
        $urut = bubbleSort($data, 'nim');
        $i = binarySearch($urut, $kunci);
        echo ($i >= 0)
            ? $urut[$i]['nama'] . ' (nilai ' . $urut[$i]['nilai'] . ")\n"
            : "NIM tidak ditemukan.\n";

    } elseif ($pilih == '7') {                     // TRAVERSAL LINEAR
        $j = baca("Arah (1 = Maju, 2 = Mundur): ");
        if ($j == '2') {
            cetakDaftar('data terakhir ke pertama', traversalMundur($data));
        } else {
            cetakDaftar('data pertama ke terakhir', traversalMaju($data));
        }

    } elseif ($pilih == '8') {                     // TRAVERSAL TREE
        $j = baca("Jenis (1 = Preorder, 2 = Inorder, 3 = Postorder, 4 = Level-order): ");
        $map = array('1' => 'preorder', '2' => 'inorder', '3' => 'postorder', '4' => 'levelorder');
        $jenis = isset($map[$j]) ? $map[$j] : 'preorder';
        $judulMap = array(
            'preorder'   => 'Preorder (Root -> Kiri -> Kanan)',
            'inorder'    => 'Inorder (Kiri -> Root -> Kanan), terurut menurut NIM',
            'postorder'  => 'Postorder (Kiri -> Kanan -> Root)',
            'levelorder' => 'Level-order (per tingkat, atas ke bawah)',
        );
        cetakDaftar($judulMap[$jenis], traversalTree($data, $jenis));

        if (count($data) > 0) {
            $struktur = array();
            pohonTeks(bangunTree($data), '', 'Root: ', $struktur);
            cetakDaftar('Struktur tree', $struktur);
        }

    } elseif ($pilih == '9') {                     // TRAVERSAL GRAPH
        if (count($data) == 0) {
            echo "Data kosong.\n";
        } else {
            tampilData($data);
            $mulai = baca("Mulai dari NIM (kosong = data pertama): ");
            $j = baca("Jenis (1 = BFS, 2 = DFS): ");
            $batasIn = baca("Selisih nilai maks [10]: ");
            $graphJenis = ($j == '2') ? 'dfs' : 'bfs';
            $batas = ($batasIn === '' || !is_numeric($batasIn)) ? 10 : max(0, (int)$batasIn);

            $adj = bangunGraph($data, $batas);
            $start = cariIndeksNim($data, $mulai);
            if ($start < 0) $start = 0;            // NIM awal tidak valid -> data pertama

            if ($graphJenis == 'bfs') {
                $urutan = bfsGraph($adj, $start);
            } else {
                $visited = array();
                $urutan = array();
                dfsGraph($adj, $start, $visited, $urutan);
            }

            $hasilGraph = array();
            $dikunjungi = array();
            $no = 1;
            foreach ($urutan as $idx) {
                $hasilGraph[] = barisData($no++, $data[$idx]);
                $dikunjungi[$idx] = true;
            }
            $takTerjangkau = array();
            foreach ($data as $i => $s) {
                if (!isset($dikunjungi[$i])) $takTerjangkau[] = $s['nama'];
            }

            echo "\nDua mahasiswa terhubung jika selisih nilainya <= $batas.\n";
            cetakDaftar(($graphJenis == 'bfs')
                ? 'BFS (melebar, pakai queue)'
                : 'DFS (mendalam, pakai rekursi)', $hasilGraph);
            if (count($takTerjangkau) > 0) {
                echo "\nTidak terjangkau dari simpul awal: " . implode(', ', $takTerjangkau) . "\n";
            }
            cetakDaftar('Daftar ketetanggaan', daftarTetangga($data, $adj));
        }

    } elseif ($pilih == '10') {                    // HAPUS satu data
        tampilData($data);
        $nim = baca("NIM yang dihapus: ");
        $nama = '';
        foreach ($data as $i => $s) {
            if ($s['nim'] == $nim) {
                $nama = $s['nama'];
                unset($data[$i]);
                break;
            }
        }
        $data = array_values($data);

        // hapus juga dari stack undo agar tidak mengacu ke data yang sudah tidak ada
        for ($k = count($undo) - 1; $k >= 0; $k--) {
            if ($undo[$k] == $nim) {
                unset($undo[$k]);
                break;
            }
        }
        $undo = array_values($undo);

        echo ($nama !== '') ? "Data $nama dihapus.\n" : "Data tidak ditemukan.\n";

    } elseif ($pilih == '11') {
        tampilData($data);

    } elseif ($pilih == '12') {                    // RESET
        $data = array();
        $antrian = array();
        $undo = array();
        echo "Semua data direset.\n";

    } elseif ($pilih == '0') {
        echo "Sampai jumpa.\n";
        break;

    } else {
        echo "Menu tidak dikenal.\n";
    }
}
