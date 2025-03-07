
### Installation:

1. Enter mediawiki directory
```
cd /path/to/mediawiki/root
```
2. Enter `extensions` directory
3. Install dependency TelegramAuthorization
```
cd extensions
```
4. Clone repository
```
git clone https://github.com/Zabqer/TelegramNotifier
```
5. Add to `LocalSettings.php`
```
$wgTelegramNotifier_BotToken = "00000000:alkf..."; # Telegram token from BotFather
```
6. Enable plugin: add to `LocalSettings.php`
```
wfLoadExtension( 'TelegramNotifier' );
```
7. Run mediawiki update command to create plugin tables
```
php maintenance/run.php update
```
