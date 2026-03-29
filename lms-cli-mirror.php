<?php
/**
 * Plugin Name: LMS WP-CLI Mirror Importer (Pro)
 * Description: Production-grade XML importer with automated completion detection and real-time process monitoring.
 * Version: 4.9.0
 */

if ( ! defined( "ABSPATH" ) ) exit;

class LMS_CLI_Mirror_Importer {
    
    private $option_name = "lms_cli_mirror_status";
    private $icon_svg = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSIjYThhZGFmIiBkPSJNMTcuNSAxMGMwIDQuMTQtMy4zNiA3LjUtNy41IDcuNVMyLjUgMTQuMTQgMi41IDEwIDUuODYgMi41IDEwIDIuNXM3LjUgMy4zNiA3LjUgNy41ek0xMCA0LjVMNiA5aDN2NGgyVjloM2wtNC00LjV6Ii8+PC9zdmc+';
    
    public function __construct() {
        $this->optimize_server();
        add_action( "admin_menu", [ $this, "add_admin_menu" ] );
        add_action( "wp_ajax_lms_mirror_upload", [ $this, "ajax_upload" ] );
        add_action( "wp_ajax_lms_mirror_start", [ $this, "ajax_start" ] );
        add_action( "wp_ajax_lms_mirror_get_log", [ $this, "ajax_get_log" ] );
        add_action( "wp_ajax_lms_mirror_cancel", [ $this, "ajax_cancel" ] );
        register_activation_hook( __FILE__, [ $this, "plugin_activation" ] );
    }

    private function optimize_server() {
        @ini_set( 'memory_limit', '512M' );
        @ini_set( 'upload_max_filesize', '256M' );
        @ini_set( 'post_max_size', '256M' );
        @ini_set( 'max_execution_time', '1200' );
    }

    private function check_compatibility() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $results = [];
        $results['shell_exec'] = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
        $wp_bin = "wp";
        if (file_exists("/usr/local/bin/wp")) $wp_bin = "/usr/local/bin/wp";
        $results['wp_cli'] = $results['shell_exec'] ? (bool)shell_exec("$wp_bin --version --allow-root") : false;
        $results['masterstudy'] = is_plugin_active('masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php') || is_plugin_active('masterstudy-lms-learning-management-system-pro/masterstudy-lms-learning-management-system-pro.php');
        $results['importer'] = is_plugin_active('wordpress-importer/wordpress-importer.php');
        $results['ready'] = ($results['shell_exec'] && $results['wp_cli'] && $results['masterstudy'] && $results['importer']);
        return $results;
    }

    public function plugin_activation() {
        $htaccess = ABSPATH . '.htaccess';
        if ( is_writable( $htaccess ) ) {
            $content = file_get_contents( $htaccess );
            if ( strpos( $content, 'LMS_IMPORTER_LIMITS' ) === false ) {
                $rules = "\n\n# BEGIN LMS_IMPORTER_LIMITS\nphp_value upload_max_filesize 256M\nphp_value post_max_size 256M\nphp_value memory_limit 512M\nphp_value max_execution_time 1200\n# END LMS_IMPORTER_LIMITS\n";
                file_put_contents( $htaccess, $content . $rules );
            }
        }
    }
    
    public function add_admin_menu() {
        add_menu_page( "LMS CLI Mirror", "LMS CLI Mirror", "manage_options", "lms-cli-mirror", [ $this, "admin_page" ], $this->icon_svg, 30 );
    }
    
    public function admin_page() {
        $status = get_option( $this->option_name );
        $comp = $this->check_compatibility();
        ?>
        <div class="wrap">
            <div style="display: flex; align-items: center; margin-bottom: 25px; margin-top: 10px;">
                <div style="background: #2c3e50; padding: 10px; border-radius: 8px; margin-right: 15px; display: flex; align-items: center; justify-content: center;">
                    <img src="<?php echo $this->icon_svg; ?>" style="width: 32px; height: 32px; filter: invert(1);" />
                </div>
                <h1 style="margin: 0;">LMS Enterprise Importer <span style="font-size: 12px; background: #2c3e50; color: #fff; padding: 3px 8px; border-radius: 4px; vertical-align: middle; margin-left: 10px;">v4.9.0 AUTO</span></h1>
            </div>
            
            <div id="setup-area" <?php echo ( $status && $status["status"] === "running" ) ? 'style="display:none;"' : ""; ?>>
                <div class="card" style="max-width: 850px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: none;">
                    <h2 style="margin-top:0;">System Diagnostics</h2>
                    <div style="margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 15px; background: <?php echo $comp['shell_exec'] ? '#ebf5eb' : '#fdf2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo $comp['shell_exec'] ? '#c3e6cb' : '#f5c6cb'; ?>;">
                            <span style="font-weight:bold; display:block; margin-bottom:5px;">Shell Execution</span> 
                            <?php echo $comp['shell_exec'] ? '<span style="color:#2e7d32;">✅ ENABLED</span>' : '<span style="color:#d32f2f;">❌ BLOCKED</span>'; ?>
                        </div>
                        <div style="padding: 15px; background: <?php echo $comp['wp_cli'] ? '#ebf5eb' : '#fdf2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo $comp['wp_cli'] ? '#c3e6cb' : '#f5c6cb'; ?>;">
                            <span style="font-weight:bold; display:block; margin-bottom:5px;">WP-CLI Engine</span> 
                            <?php echo $comp['wp_cli'] ? '<span style="color:#2e7d32;">✅ DETECTED</span>' : '<span style="color:#d32f2f;">❌ NOT FOUND</span>'; ?>
                        </div>
                        <div style="padding: 15px; background: <?php echo $comp['masterstudy'] ? '#ebf5eb' : '#fdf2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo $comp['masterstudy'] ? '#c3e6cb' : '#f5c6cb'; ?>;">
                            <span style="font-weight:bold; display:block; margin-bottom:5px;">MasterStudy LMS</span> 
                            <?php echo $comp['masterstudy'] ? '<span style="color:#2e7d32;">✅ ACTIVE</span>' : '<span style="color:#d32f2f;">❌ MISSING</span>'; ?>
                        </div>
                        <div style="padding: 15px; background: <?php echo $comp['importer'] ? '#ebf5eb' : '#fdf2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo $comp['importer'] ? '#c3e6cb' : '#f5c6cb'; ?>;">
                            <span style="font-weight:bold; display:block; margin-bottom:5px;">WP Importer</span> 
                            <?php echo $comp['importer'] ? '<span style="color:#2e7d32;">✅ ACTIVE</span>' : '<span style="color:#d32f2f;">❌ MISSING</span>'; ?>
                        </div>
                    </div>

                    <div id="upload-container" style="border: 2px dashed #ccd0d4; padding: 60px 40px; text-align: center; border-radius: 8px; background: #f9f9f9; cursor: pointer; position: relative; <?php echo $comp['ready'] ? '' : 'opacity:0.5; pointer-events:none;'; ?>">
                        <span class="dashicons dashicons-upload" style="font-size: 48px; width: 48px; height: 48px; color: #2271b1;"></span>
                        <p style="font-size: 18px; margin: 15px 0;">Click or Drag & Drop XML file</p>
                        <input type="file" id="xml-file-input" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" accept=".xml">
                    </div>

                    <div id="file-ready-area" style="display:none; margin-top: 25px; padding: 25px; background: #f0f6fb; border-radius: 8px; border-left: 4px solid #2271b1;">
                        <div style="display:flex; justify-content:space-between; margin-bottom: 10px;">
                            <span id="selected-filename" style="font-weight:600;"></span>
                            <span id="upload-percentage" style="font-weight:bold; color:#2271b1;">0%</span>
                        </div>
                        <div id="progress-container" style="display:none; height: 8px; background: #d0d7de; border-radius: 4px; overflow: hidden;">
                            <div id="progress-bar" style="height: 100%; width: 0%; background: #2271b1; transition: width 0.3s ease;"></div>
                        </div>
                        <div id="action-buttons" style="margin-top: 20px;">
                            <button id="start-mirror-btn" class="button button-primary button-large" <?php echo $comp['ready'] ? '' : 'disabled'; ?>>START BACKGROUND IMPORT</button>
                            <button id="reset-upload-btn" class="button button-link" style="color: #d63638;">Change File</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="log-area" <?php echo ( !$status || $status["status"] !== "running" ) ? 'style="display:none;"' : ""; ?>>
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 5px;">
                            <span id="telemetry-spinner" class="spinner is-active" style="float:none; margin:0 10px 0 0;"></span>
                            <span id="telemetry-label" style="font-weight: 700; color: #d63638;">ACTIVE TELEMETRY</span>
                        </div>
                        <div style="color: #666; font-size: 12px;" id="telemetry-subtext">PID: <span id="current-pid"><?php echo isset($status['pid']) ? $status['pid'] : 'N/A'; ?></span> | ENGINE: WP-CLI</div>
                    </div>
                    <button id="cancel-mirror-btn" class="button button-link-delete" style="color: #d63638; border: 1px solid #d63638; padding: 5px 20px; border-radius: 4px;">CANCEL IMPORT</button>
                </div>
                <div style="background:#0d1117; color:#c9d1d9; padding:30px; height:700px; overflow-y:auto; font-family:monospace; font-size: 13px; line-height:1.7; border-radius:10px; border: 1px solid #30363d; white-space: pre-wrap;" id="terminal-box">Synchronizing stream...</div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let lastContent = "";
            let selectedFile = null;
            let isFinished = false;
            const fileInput = document.getElementById('xml-file-input');

            $(fileInput).change(function(e) {
                if (e.target.files.length > 0) {
                    selectedFile = e.target.files[0];
                    $("#selected-filename").text(selectedFile.name);
                    $("#upload-container").hide(); $("#file-ready-area").show();
                }
            });

            function fetchLog() {
                if (isFinished) return;
                $.post(ajaxurl, {action: "lms_mirror_get_log"}, function(res) {
                    if (res.success) {
                        if (res.data.content !== lastContent) {
                            lastContent = res.data.content;
                            let lines = res.data.content.split('\n');
                            let formatted = lines.map(line => {
                                let t = line.trim();
                                if (!t) return "";
                                if (t.includes("Processing post")) return `\n<span style="color:#58a6ff;">[PROCESS]</span> ${t}`;
                                if (t.includes("Added post_meta")) return `   <span style="color:#3fb950;">➔</span> ${t}`;
                                if (t.includes("already exists")) return `   <span style="color:#d29922;">[SKIP]</span> ${t}`;
                                if (t.includes("All done")) return `\n<span style="color:#2ecc71; font-weight:bold; font-size:16px;">[SUCCESS] ${t}</span>`;
                                return `<span style="color:#8b949e;">[INFO]</span> ${t}`;
                            }).join('\n');
                            $("#terminal-box").html(formatted);
                            $("#terminal-box").scrollTop($("#terminal-box")[0].scrollHeight);
                        }

                        if (res.data.finished) {
                            isFinished = true;
                            $("#telemetry-spinner").removeClass("is-active").hide();
                            $("#telemetry-label").text("IMPORT COMPLETE").css("color", "#2ecc71");
                            $("#telemetry-subtext").html('<span style="color:#2ecc71; font-weight:bold;">TASK FINISHED SUCCESSFULLY</span>');
                            $("#cancel-mirror-btn").text("FINISH & RETURN")
                                .css({"color": "#fff", "background": "#2e7d32", "border-color": "#2e7d32"})
                                .removeClass("button-link-delete");
                        }
                    }
                });
            }

            $("#start-mirror-btn").click(function() {
                if (!selectedFile) return;
                $("#progress-container, #upload-percentage").show();
                $("#action-buttons").hide();
                let formData = new FormData();
                formData.append('action', 'lms_mirror_upload');
                formData.append('import_file', selectedFile);

                $.ajax({
                    url: ajaxurl, type: 'POST', data: formData, processData: false, contentType: false,
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", (evt) => {
                            if (evt.lengthComputable) {
                                let p = Math.round((evt.loaded / evt.total) * 100);
                                $("#progress-bar").css("width", p + "%");
                                $("#upload-percentage").text(p + "%");
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(r) {
                        if (r.success) {
                            $("#upload-percentage").text("BOOTING ENGINE...");
                            $.post(ajaxurl, {action: "lms_mirror_start"}, (r2) => {
                                if (r2.success) location.reload();
                                else alert("Engine failed: " + r2.data);
                            });
                        } else alert("Upload Error");
                    }
                });
            });

            $("#cancel-mirror-btn").click(() => {
                if (isFinished) { location.reload(); return; }
                if(confirm("Terminate process?")) {
                    $.post(ajaxurl, {action: "lms_mirror_cancel"}, () => location.reload());
                }
            });

            if ($("#log-area").is(":visible")) { setInterval(fetchLog, 2500); fetchLog(); }
        });
        </script>
        <?php
    }

    public function ajax_upload() {
        if (!current_user_can("manage_options")) wp_send_json_error();
        move_uploaded_file($_FILES['import_file']['tmp_name'], plugin_dir_path(__FILE__) . "import.xml");
        wp_send_json_success();
    }

    public function ajax_start() {
        if (!current_user_can("manage_options")) wp_send_json_error();
        $xml = plugin_dir_path(__FILE__) . "import.xml";
        $log = plugin_dir_path(__FILE__) . "import.log";
        $wp_bin = file_exists("/usr/local/bin/wp") ? "/usr/local/bin/wp" : "wp";
        
        @file_put_contents($log, "[" . date("H:i:s") . "] SYSTEM: Booting Engine...\n");
        $cmd = "nohup env WP_CLI_ALLOW_ROOT=1 $wp_bin import " . escapeshellarg($xml) . " --authors=create --skip-plugins=masterstudy-lms-learning-management-system-pro --skip-themes --path=" . escapeshellarg(ABSPATH) . " >> " . escapeshellarg($log) . " 2>&1 & echo $!";
        $pid = trim(shell_exec($cmd));
        
        update_option($this->option_name, ["status" => "running", "pid" => $pid]);
        wp_send_json_success(["pid" => $pid]);
    }
    
    public function ajax_get_log() {
        $status = get_option($this->option_name);
        $log = plugin_dir_path(__FILE__) . "import.log";
        if ( ! file_exists( $log ) ) wp_send_json_success( [ "content" => "Connecting...", "finished" => false ] );
        
        $content = (filesize($log) > 25000) ? $this->tail_file($log, 25000) : file_get_contents($log);
        $finished = false;

        // CRITICAL: Detect completion by checking PID and looking for "All done"
        if ($status && !empty($status['pid'])) {
            $pid = (int)$status['pid'];
            $is_running = shell_exec("ps -p $pid");
            if (!$is_running || strpos($content, "All done") !== false) {
                $finished = true;
                delete_option($this->option_name);
            }
        }
        
        wp_send_json_success(["content" => $content, "finished" => $finished]);
    }

    private function tail_file($path, $bytes) {
        $fp = fopen($path, 'r'); fseek($fp, -$bytes, SEEK_END);
        $data = fread($fp, $bytes); fclose($fp);
        return $data;
    }
    
    public function ajax_cancel() {
        $status = get_option($this->option_name);
        if (!empty($status['pid'])) shell_exec("kill -9 " . (int)$status['pid']);
        delete_option($this->option_name);
        wp_send_json_success();
    }
}
new LMS_CLI_Mirror_Importer();
