import sys
import threading
import time
import webbrowser
from flask import Flask, render_template, request
from pystray import Icon as TrayIcon, Menu as TrayMenu, MenuItem as TrayMenuItem
from PIL import Image, ImageDraw, ImageFont

# --- Configuration ---
HOST = '127.0.0.1'
PORT = 5000
APP_URL = f"http://{HOST}:{PORT}/"
APP_TITLE = "Flask README Editor"
EMOJI_ICON = "🌐" # Emoji bạn muốn dùng
BROWSER_OPEN_DELAY = 2 # Giây - Thời gian chờ trước khi mở trình duyệt

# --- Global Variables ---
app = Flask(__name__)
tray_icon = None
flask_thread = None
shutdown_requested = threading.Event()

# --- Flask Routes ---
@app.route('/')
def index():
    current_version = int(time.time())
    return render_template('index.html', version=current_version)

# --- Flask Server Function ---
def run_flask():
    print(f"Starting Flask server on {APP_URL}")
    try:
        app.run(host=HOST, port=PORT, debug=False, use_reloader=False)
        print("Flask server stopped.")
    except OSError as e:
        print(f"!!! Error starting Flask server (Port {PORT} might be in use): {e}")
        if tray_icon:
            print("Stopping tray icon due to server error.")
            tray_icon.stop() # Dừng tray nếu server không khởi động được
    except Exception as e:
        print(f"Flask server error: {e}")
        if tray_icon:
            tray_icon.stop()

# --- Function to create icon from Emoji ---
def create_emoji_icon(emoji, size=(64, 64), font_size=48):
    try:
        image = Image.new("RGBA", size, (0, 0, 0, 0))
        draw = ImageDraw.Draw(image)
        font_path = None
        common_emoji_fonts = ["seguiemj.ttf", "Apple Color Emoji", "Noto Color Emoji", "Symbola.ttf"]
        font = None
        for font_name in common_emoji_fonts:
            try:
                font = ImageFont.truetype(font_name, font_size)
                print(f"Using font: {font_name}")
                break
            except IOError:
                continue
        if font is None:
            print("Warning: Could not find a suitable emoji font. Using default.")
            try:
                font = ImageFont.load_default()
            except IOError:
                print("Error: Default font also failed to load.")
                return None

        try:
             # Vẽ vào giữa sử dụng anchor='mm' (Pillow mới)
             draw.text((size[0] // 2, size[1] // 2), emoji, fill=(255, 255, 255, 255), font=font, anchor="mm")
        except TypeError:
             # Fallback cho Pillow cũ
             print("Warning: Pillow version might not support 'anchor'. Falling back.")
             bbox = draw.textbbox((0, 0), emoji, font=font)
             text_width = bbox[2] - bbox[0]
             text_height = bbox[3] - bbox[1]
             x = (size[0] - text_width) / 2
             y = (size[1] - text_height) / 2
             draw.text((x, y), emoji, fill=(255, 255, 255, 255), font=font)

        return image
    except Exception as e:
        print(f"Error creating emoji icon: {e}")
        return None

# --- System Tray Functions ---
def quit_app(icon, item):
    print("Exit selected. Stopping application...")
    shutdown_requested.set() # Đặt cờ (có thể dùng trong tương lai nếu cần shutdown phức tạp hơn)
    if icon:
        icon.stop() # Dừng vòng lặp của pystray
    print("Exiting process.")
    # Thoát tiến trình, thread Flask (daemon=True) sẽ tự dừng theo
    sys.exit(0)

def open_in_browser(icon=None, item=None): # Thêm giá trị mặc định cho icon, item
    """Hàm này có thể gọi từ menu tray hoặc tự động."""
    print(f"Opening {APP_URL} in browser...")
    webbrowser.open(APP_URL)

def setup_tray():
    global tray_icon
    image = create_emoji_icon(EMOJI_ICON)
    if image is None:
        print("Fatal: Could not create system tray icon image. Exiting.")
        # Nếu không tạo được icon, không nên chạy Flask thread nữa
        sys.exit(1) # Thoát luôn

    menu = TrayMenu(
        # default=True nghĩa là hành động này cũng xảy ra khi double-click icon
        TrayMenuItem('Open Editor', open_in_browser, default=True),
        TrayMenu.SEPARATOR,
        TrayMenuItem('Exit', quit_app)
    )

    tray_icon = TrayIcon(APP_TITLE, image, APP_TITLE, menu)
    print("System tray icon started. Right-click for options.")
    # tray_icon.run() sẽ chạy ở luồng chính và giữ chương trình sống
    tray_icon.run()
    # Code dưới đây chỉ chạy khi icon.stop() được gọi
    print("System tray loop finished.")


# --- Main Execution ---
if __name__ == '__main__':
    # 1. Khởi tạo và bắt đầu Flask Thread
    print("Initializing Flask thread...")
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()

    # 2. Lên lịch mở trình duyệt sau một khoảng trễ
    #    Sử dụng Timer để không chặn luồng chính
    print(f"Scheduling browser to open in {BROWSER_OPEN_DELAY} seconds...")
    browser_timer = threading.Timer(BROWSER_OPEN_DELAY, open_in_browser)
    browser_timer.start() # Bắt đầu đếm ngược

    # 3. Thiết lập và chạy System Tray Icon ở luồng chính
    #    Hàm này sẽ block luồng chính cho đến khi chọn Exit
    setup_tray()

    # Mã ở đây thường không được chạy đến vì setup_tray() block hoặc quit_app() gọi sys.exit()
    print("Main thread finished.")