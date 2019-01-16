# CodeIgniter + Line Message API

* 這兩天開始看 CodeIgniter 的文件，順便把之前沒有列在作品，與 Line Message API 以 CodeIgniter 實作。

* 資料夾 M、V、C 分別對應 CodeIgniter 的 modles、views、controllers 資料夾。

* line.php 則是之前還沒用 CodeIgniter 已經寫好的 class ，也就是 `M/LineAPIModel` 的前身，上線時，這個檔案並不需要使用。

## 使用

### 安裝

1. 把 M、V、C 的檔案分別放到 CodeIgniter 的 modles、views、controllers 資料夾。
2. 適當的設定伺服器的資料庫以及 CodeIgniter 的 `config/database.php`。
3. 用 `https://網址/WebView/init/` 初始化資料庫。
4. 設定 Line 的 Webhook 到 `https://網址/LineAPI/`。

### 網頁介面

1. 網頁介面在 `https://網址/WebView/`，這邊可以看到使用者發送的訊息。
2. 回復訊息，先點選使用者名字，這會把使用者 ID 自動填上。然後輸入訊息後發送。