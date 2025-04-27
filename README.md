# Github Readme edit tool

Đây là trình soạn thảo giúp bạn định dạng lại file readme của mình 1 cách trực quan.

File gồm có 2 phần:

* [github.php](https://github.com/pythverse/github-readme-edit-tool/blob/main/github.php "github.php") là phiên bản web, chỉ cần upload file lên hosting và có thể chỉnh sửa trực tiếp
* [main.py](https://github.com/pythverse/github-readme-edit-tool/blob/main/main.py "main.py") và folder [templates](https://github.com/pythverse/github-readme-edit-tool/tree/main/templates "templates") là file python, chạy chương trình này sẽ tự động mở trình duyệt web.

Bạn sẽ soạn thảo file Readme của mình trên trình soạn thảo WYSIWYG. Sau khi xong bạn bấm vào nút Sao chép mã để copy hoặc lưu file readme để upload file lên github
Ngoài ra còn hỗ trợ mở và edit file Readme có sẵn.
Khi chạy sẽ có biểu tượng quả địa cầu 🌐 ở dưới thanh system tray, bạn có thể kích phải vào nó để chọn exit hoặc Open editor để mở tình duyệt web

Cài đặt thư viện cần thiết để chạy file:

```Code
pip install Flask pystray Pillow
```


<br>
<br>
![Demo](https://raw.githubusercontent.com/pythverse/github-readme-edit-tool/refs/heads/main/demo.png)
