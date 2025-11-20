<?php
// ----------------------------
// CONFIG
// ----------------------------
$file = __DIR__ . "/list.json";
$repoPath = __DIR__;
$botToken = "7577540935:AAGcIyWwbA6KRv68X9sodT7X9-gF3772huY";
$chatId   = "-1002388014286";
$threadId = 60;
$remoteJsonUrl = "https://raw.githubusercontent.com/iewilmaestro/List-Script/refs/heads/main/list.json";

// ----------------------------
// FUNCTIONS
// ----------------------------
function loadList($file){
    if(!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json,true);
    return is_array($data)?$data:[];
}
function saveList($file,$data){
    file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}
function sendTelegramButtonToTopic($text, $token, $chat_id, $thread_id) {
    $url = "https://api.telegram.org/bot$token/sendMessage";

    // tombol inline
    $keyboard = [
        "inline_keyboard" => [
            [
                ["text" => "cek script list", "url" => "https://iewilofficial.blogspot.com/2025/08/list-script.html"]
            ]
        ]
    ];

    $data = [
        "chat_id" => $chat_id,
        "message_thread_id" => $thread_id,
        "text" => $text,
        "parse_mode" => "HTML",
        "reply_markup" => json_encode($keyboard),
        'disable_web_page_preview' => true
    ];

    $options = [
        "http" => [
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

function compareLists($old,$new){
    $old_map = [];
    foreach($old as $item) $old_map[$item['name']]=$item;
    
    $new_map = [];
    foreach($new as $item) $new_map[$item['name']]=$item;

    $lines = [];

    // Bot baru
    foreach($new as $item){
        $name = $item['name'];
        if(!isset($old_map[$name])){
            $lines[] = "➕ Bot baru: $name";
        } else {
            $oldItem = $old_map[$name];
            foreach($item as $k=>$v){
                if($k=='name') continue;
                if(!isset($oldItem[$k])) continue;
                if($oldItem[$k]!=$v){
                    $lines[] = "✏️ $k diubah: $name {$oldItem[$k]} → $v";
                }
            }
        }
    }

    // Bot dihapus
    foreach($old as $item){
        $name = $item['name'];
        if(!isset($new_map[$name])){
            $lines[] = "❌ Bot dihapus: $name";
        }
    }

    return $lines;
}

// ----------------------------
// CLI MENU
// ----------------------------
echo "==========================\n";
echo " BOT MANAGER (CLI MODE)\n";
echo "==========================\n";
echo "1. Tambah Bot\n2. Edit Status Bot\n3. Update ke GitHub\n--------------------------\nPilih menu: ";

$input = trim(fgets(STDIN));
$data = loadList($file);

// ----------------------------
// 1. Tambah Bot
// ----------------------------
if($input=="1"){
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
        "name"=>$name,
        "status"=>$status,
        "register"=>$register,
        "download"=>$download,
        "media"=>$media
    ];

    // Tambah & urutkan A-Z
    $data[] = $baru;
    usort($data,function($a,$b){ return strcmp(strtolower($a['name']), strtolower($b['name'])); });
    saveList($file,$data);

    echo "Bot berhasil ditambahkan & diurutkan A-Z!\n";
    exit;
}

// ----------------------------
// 2. Edit Status Bot
// ----------------------------
if($input=="2"){
    echo "Daftar Bot:\n";
    foreach($data as $i=>$item){
        echo ($i+1).". ".$item["name"]." (status: ".$item["status"].")\n";
    }
    echo "Pilih nomor bot: ";
    $num = trim(fgets(STDIN));
    $idx = $num-1;
    if(!isset($data[$idx])){
        echo "Bot tidak ditemukan!\n"; exit;
    }
    echo "Status baru (free/premium/dead): ";
    $status = trim(fgets(STDIN));
    $data[$idx]["status"] = $status;
    saveList($file,$data);
    echo "Status berhasil diperbarui!\n"; exit;
}

// ----------------------------
// 3. Update ke GitHub
// ----------------------------
if($input=="3"){

    // 1. Ambil list.json lama dari GitHub
    $awal_json = @file_get_contents($remoteJsonUrl);
    $awal = is_string($awal_json)?json_decode($awal_json,true):[];

    // 2. Git pull, add, commit, push
    $msg = date("Y-m-d H:i:s");
    $commands = [
        "cd $repoPath",
        "git pull",
        "git add .",
        "git commit -m \"$msg\"",
        "git push origin main"
    ];

    foreach($commands as $cmd){
        echo "\n> $cmd\n";
        $output = shell_exec($cmd." 2>&1");
        echo $output."\n";
    }

    // 3. Ambil list.json baru
    $baru = loadList($file);

    // 4. Compare
    $lines = compareLists($awal,$baru);

    if (empty($lines)) {
        $text = "✅ Git Updated: Tidak ada perubahan valid pada list.json";
    } else {
        // gunakan \n atau <br> sesuai keinginan
        $text = "✅ Git Updated\n".implode("\n",$lines);
    }


    // 5. Kirim Telegram ke topik
    sendTelegramToTopic($text,$botToken,$chatId,$threadId);

    echo "Push ke GitHub selesai & Telegram terkirim!\n";
    exit;
}

echo "Menu tidak dikenal.\n";
exit;
