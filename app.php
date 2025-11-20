<?php

// Lokasi file JSON
$file = __DIR__ . "/list.json";

// ------------------------------------------------------------
// Fungsi baca dan simpan JSON
// ------------------------------------------------------------
function loadList($file)
{
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveList($file, $data)
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// ------------------------------------------------------------
// MENU CLI
// ------------------------------------------------------------

echo "==========================\n";
echo " BOT MANAGER (CLI MODE)\n";
echo "==========================\n";
echo "1. Tambah Bot\n";
echo "2. Edit Status Bot\n";
echo "3. Update ke GitHub\n";
echo "--------------------------\n";
echo "Pilih menu: ";

$input = trim(fgets(STDIN));
$data  = loadList($file);

// ------------------------------------------------------------
// 1. TAMBAH BOT
// ------------------------------------------------------------
if ($input == "1") {
    echo "Nama bot: ";
    $name = trim(fgets(STDIN));

    echo "Status (free/premium/dead): ";
    $status = trim(fgets(STDIN));

    echo "Link register: ";
    $register = trim(fgets(STDIN));

    echo "Link download: ";
    $download = trim(fgets(STDIN));

    echo "Link media: ";
    $media = trim(fgets(STDIN));

    $baru = [
        "name"     => $name,
        "status"   => $status,
        "register" => $register,
        "download" => $download,
        "media"    => $media
    ];

    // Tambah data
    $data[] = $baru;

    // Urutkan berdasarkan name (A → Z)
    usort($data, function ($a, $b) {
        return strcmp(strtolower($a['name']), strtolower($b['name']));
    });

    // Simpan kembali
    saveList($file, $data);

    echo "Bot berhasil ditambahkan & diurutkan A-Z!\n";
    exit;
}


// ------------------------------------------------------------
// 2. EDIT STATUS BOT
// ------------------------------------------------------------
if ($input == "2") {

    echo "Daftar Bot:\n";
    foreach ($data as $i => $item) {
        echo ($i+1) . ". " . $item["name"] . " (status: " . $item["status"] . ")\n";
    }

    echo "Pilih nomor bot: ";
    $num = trim(fgets(STDIN));
    $idx = $num - 1;

    if (!isset($data[$idx])) {
        echo "Bot tidak ditemukan!\n";
        exit;
    }

    echo "Status baru (free/premium/dead): ";
    $status = trim(fgets(STDIN));

    $data[$idx]["status"] = $status;
    saveList($file, $data);

    echo "Status berhasil diperbarui!\n";
    exit;
}


// ------------------------------------------------------------
// 3. PUSH KE GITHUB
// ------------------------------------------------------------
if ($input == "3") {

    $repoPath = __DIR__;
    $msg = date("Y-m-d H:i:s");

    $commands = [
        "cd $repoPath",
        "git add .",
        "git commit -m \"$msg\"",
        "git push origin main"
    ];

    foreach ($commands as $cmd) {
        echo "\n> $cmd\n";
        $output = shell_exec($cmd . " 2>&1");
        echo $output . "\n";
    }

    echo "Push ke GitHub selesai!\n";
    exit;
}


// ------------------------------------------------------------
// Jika menu tidak valid
// ------------------------------------------------------------
echo "Menu tidak dikenal.\n";
exit;
