$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$publicPath = Join-Path $projectRoot 'public'
$serverScript = Join-Path $projectRoot 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'

Push-Location $publicPath
try {
    php -d upload_max_filesize=50M -d post_max_size=60M -d memory_limit=256M -S 127.0.0.1:8080 -t $publicPath $serverScript
} finally {
    Pop-Location
}
