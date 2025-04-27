<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trình Soạn Thảo README (v13 - Stable)</title>

    <!-- Cache busting -->
    <?php $version = time(); ?>

    <!-- Tải CSS của Toast UI Editor -->
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css?v=<?php echo $version; ?>" />
    <!-- Tải Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css?v=<?php echo $version; ?>">

    <style>
        /* --- Layout cơ bản --- */
        html, body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            margin: 0; padding: 0; height: 100vh; overflow: hidden; background-color: #f4f4f4;
            display: flex; flex-direction: column;
        }
        .main-content {
            flex-grow: 1; display: flex; padding: 10px; box-sizing: border-box; overflow: hidden; gap: 10px;
        }
        .panel {
            flex: 1; display: flex; flex-direction: column; background-color: #fff;
            border: 1px solid #ccc; border-radius: 4px; overflow: hidden;
            transition: flex 0.3s ease, opacity 0.3s ease, margin 0.3s ease, padding 0.3s ease, border 0.3s ease; /* Thêm transition */
        }
        .panel-header {
            padding: 8px 12px; background-color: #f0f0f0; border-bottom: 1px solid #ccc;
            font-weight: bold; flex-shrink: 0; font-size: 0.9em;
        }

        /* --- Panel Trái (Editor) --- */
        #editor-panel { flex-basis: 100%; } /* Mặc định chiếm hết */
        #editor-container {
            flex-grow: 1; overflow: hidden; position: relative;
            /* Quan trọng: Container này cần là flex để quản lý TUI bên trong */
            display: flex;
            flex-direction: column;
             border-bottom-left-radius: 4px; /* Bo góc */
             border-bottom-right-radius: 4px;
        }

        /* --- Toast UI Editor Styling --- */
        /* Container chính của TUI Editor */
        .toastui-editor-defaultUI {
            border: none !important;
            height: 100%; /* Chiếm hết chiều cao của #editor-container */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Ngăn container này cuộn */
        }
        /* Toolbar cố định */
        .toastui-editor-toolbar-wrapper {
            flex-shrink: 0; /* Không co lại */
            position: relative; /* Đảm bảo không bị che */
            z-index: 1;
            background-color: #f9f9f9;
            border-bottom: 1px solid #ddd; /* Thêm viền dưới rõ ràng */
        }
        /* Vùng chính chứa trình soạn thảo */
        .toastui-editor-main-container {
            flex-grow: 1; /* Chiếm không gian còn lại */
            overflow: hidden; /* Ngăn container này cuộn */
            position: relative; /* Giúp con cuộn đúng */
        }
        /* Container cho chế độ WYSIWYG (Phần được cuộn) */
        .toastui-editor-ww-container {
            overflow-y: auto; /* <<< Chỉ container này được phép cuộn >>> */
            height: 100%; /* Chiếm hết chiều cao của cha (.toastui-editor-main-container) */
            background-color: #fff; /* Nền trắng */
            border-bottom-left-radius: 4px; /* Bo góc */
            border-bottom-right-radius: 4px;
        }
        /* Vùng nội dung thực tế */
        .toastui-editor-contents {
            padding: 15px;
            box-sizing: border-box;
            /* Bỏ height và overflow ở đây */
            line-height: 1.6;
        }
        /* Style ảnh, code block */
        .toastui-editor-contents img { max-width: 90%; display: block; margin: 15px auto; border: 1px solid #eee; height: auto; }
        .toastui-editor-contents pre { background-color: #f5f5f5; padding: 1em !important; border-radius: 4px; margin: 1em 0 !important; white-space: pre-wrap; word-wrap: break-word; overflow-x: auto; /* Cho phép cuộn ngang code dài */ }

        /* --- CSS cho Raw Preview Panel --- */
        #raw-panel {
            flex-basis: 0; flex-grow: 0; opacity: 0; padding: 0; margin-left: 0; border: none;
        }
        #raw-panel.visible {
            flex-basis: 50%; flex-grow: 1; opacity: 1; margin-left: 10px; border: 1px solid #ccc;
        }
        #editor-panel.half-width { flex-basis: 50%; }
        #raw-markdown-output {
            flex-grow: 1; width: 100%; box-sizing: border-box; padding: 10px; border: none;
            font-family: "Courier New", monospace; font-size: 13px; line-height: 1.4;
            background-color: #2b2b2b; color: #cccccc; resize: none;
            border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;
        }

        /* --- CSS cho thanh nút --- */
        .button-bar {
            flex-shrink: 0; padding: 10px 15px; background-color: #e9e9e9;
            border-top: 1px solid #ccc; text-align: center;
        }
        .button-bar button {
            padding: 8px 15px; margin: 0 5px; cursor: pointer; border: 1px solid #adadad;
            border-radius: 4px; background-color: #f5f5f5; font-size: 14px; transition: background-color 0.2s ease;
        }
        .button-bar button:hover { background-color: #e0e0e0; border-color: #888; }
        .button-bar button:active { background-color: #d0d0d0; }
        .button-bar button i { margin-right: 6px; }
        #fileInput { display: none; }
    </style>
</head>
<body>

    <div class="main-content">
        <!-- Panel Trái: Soạn thảo -->
        <div class="panel" id="editor-panel">
            <div class="panel-header">Soạn Thảo Trực Quan (WYSIWYG)</div>
            <div id="editor-container"></div> <!-- Container cho TUI Editor -->
        </div>

        <!-- Panel Phải: Xem Mã Markdown Thô -->
        <div class="panel" id="raw-panel">
            <div class="panel-header">Mã Markdown Thô</div>
            <textarea id="raw-markdown-output" readonly></textarea>
        </div>
    </div>

    <!-- Thanh Nút Điều Khiển -->
    <div class="button-bar">
        <input type="file" id="fileInput" accept=".md,.txt">
        <button id="openButton"><i class="fas fa-folder-open"></i> Mở File</button>
        <button id="copyButton"><i class="fas fa-copy"></i> Sao chép Mã</button>
        <button id="toggleRawButton"><i class="fas fa-code"></i> Xem Mã Thô</button>
        <button id="saveButton"><i class="fas fa-save"></i> Lưu File README.md</button>
    </div>

    <!-- Tải JS của Toast UI Editor -->
    <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js?v=<?php echo $version; ?>"></script>
    <!-- Không cần Prism JS -->

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            console.log("DOM loaded");

            // Lấy tham chiếu element
            const editorPanel = document.getElementById('editor-panel');
            const rawPanel = document.getElementById('raw-panel');
            const editorContainer = document.getElementById('editor-container');
            const rawOutputTextArea = document.getElementById('raw-markdown-output');
            const openButton = document.getElementById('openButton');
            const fileInput = document.getElementById('fileInput');
            const copyButton = document.getElementById('copyButton');
            const saveButton = document.getElementById('saveButton');
            const toggleRawButton = document.getElementById('toggleRawButton');
            let editor;
            let isRawVisible = false;

            // Kiểm tra element
            if (!editorPanel || !rawPanel || !editorContainer || !rawOutputTextArea || !openButton || !fileInput || !copyButton || !saveButton || !toggleRawButton) {
                 console.error("FATAL: Required HTML elements missing.");
                 alert("Interface loading error. Check Console (F12).");
                 return;
            }

            // Khởi tạo Editor
            try {
                 console.log("Initializing Toast UI Editor...");
                 editor = new toastui.Editor({
                    el: editorContainer,
                    height: '100%', // <<< QUAN TRỌNG: Để Editor tự quản lý chiều cao bên trong container của nó
                    initialEditType: 'wysiwyg',
                    previewStyle: 'vertical',
                    initialValue: '# Chào mừng!\n\nBắt đầu soạn thảo ở đây.',
                    hideModeSwitch: true, // <<< Ẩn tab chuyển đổi Markdown/WYSIWYG >>>
                    // Không cần plugins
                 });
                 console.log("Toast UI Editor initialized successfully!");

                 // Hàm Cập nhật Raw Preview
                 function updateRawPreview() {
                    if (editor) { rawOutputTextArea.value = editor.getMarkdown(); }
                 }
                 editor.on('change', updateRawPreview);
                 updateRawPreview(); // Cập nhật lần đầu

                 // Gắn sự kiện nút
                 openButton.addEventListener('click', () => { fileInput.click(); });
                 fileInput.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    if (file && editor) {
                        const reader = new FileReader();
                        reader.onload = (e) => { try { editor.setMarkdown(e.target.result); console.log("File loaded."); } catch (error) { console.error("Read error:", error); alert("Could not read file."); } finally { fileInput.value = ''; } };
                        reader.onerror = (e) => { console.error("FileReader error:", e); alert("Error reading file."); fileInput.value = ''; }
                        reader.readAsText(file);
                    } else if (!editor) { alert("Editor not ready."); fileInput.value = ''; }
                 });
                 copyButton.addEventListener('click', () => {
                    const contentToCopy = rawOutputTextArea.value;
                    if (contentToCopy) { navigator.clipboard.writeText(contentToCopy).then(() => { console.log('Copied!'); const o = copyButton.innerHTML; copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!'; setTimeout(() => { copyButton.innerHTML = o; }, 2000); }).catch(err => { console.error('Copy err: ', err); alert('Copy failed.'); }); } else { alert('Nothing to copy.'); }
                 });
                 saveButton.addEventListener('click', () => {
                    if (editor) { const c = editor.getMarkdown(); if (!c && !confirm("Empty, save?")) { return; } const b = new Blob([c], { type: 'text/markdown;charset=utf-8' }); const l = document.createElement('a'); l.href = URL.createObjectURL(b); l.download = 'README.md'; l.style.display = 'none'; document.body.appendChild(l); l.click(); document.body.removeChild(l); URL.revokeObjectURL(l.href); console.log("Download initiated."); } else { alert("Editor not ready."); }
                 });
                 toggleRawButton.addEventListener('click', () => {
                     isRawVisible = !isRawVisible;
                     if (isRawVisible) { rawPanel.classList.add('visible'); editorPanel.classList.add('half-width'); toggleRawButton.innerHTML = '<i class="fas fa-eye-slash"></i> Ẩn Mã Thô'; }
                     else { rawPanel.classList.remove('visible'); editorPanel.classList.remove('half-width'); toggleRawButton.innerHTML = '<i class="fas fa-code"></i> Xem Mã Thô'; }
                     console.log(`Raw panel ${isRawVisible ? 'shown' : 'hidden'}`);
                     // Thử gọi resize sau khi thay đổi layout (có thể không cần thiết)
                     // setTimeout(() => { if(editor) editor.setHeight('auto'); }, 350); // Đợi transition hoàn thành
                 });

                 console.log("Event listeners attached.");

            } catch (error) {
                 console.error("Critical error initializing Toast UI Editor:", error);
                 // Hiển thị lỗi rõ ràng hơn trong vùng editor
                 editorContainer.innerHTML = `<div style="padding:20px; color:red;">
                    <h3>Lỗi Khởi Tạo Trình Soạn Thảo</h3>
                    <p>Đã xảy ra lỗi khi tải trình soạn thảo. Vui lòng kiểm tra Console (F12) để biết chi tiết.</p>
                    <p><strong>Thông báo lỗi:</strong> ${error.message}</p>
                 </div>`;
                 // Vô hiệu hóa nút
                 openButton.disabled = true; copyButton.disabled = true; saveButton.disabled = true; toggleRawButton.disabled = true;
            }
        }); // Kết thúc DOMContentLoaded

    </script>

</body>
</html>
