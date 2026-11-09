$ErrorActionPreference = 'Stop'
$sourceRoot = Join-Path $PSScriptRoot '..\storage\app\source-imports\2026-10-25'
New-Item -ItemType Directory -Path $sourceRoot -Force | Out-Null
$sourceUrls = @()
foreach ($listUrl in @('https://santertb.sangihekab.go.id/faskes', 'https://santertb.sangihekab.go.id/faskes?page=2')) {
    $page = Invoke-WebRequest -UseBasicParsing -Uri $listUrl -TimeoutSec 25
    $sourceUrls += [regex]::Matches($page.Content, 'https://santertb\.sangihekab\.go\.id/faskes/\d+') | ForEach-Object { $_.Value }
}
$facilities = foreach ($facilityUrl in ($sourceUrls | Sort-Object -Unique)) {
    $page = Invoke-WebRequest -UseBasicParsing -Uri $facilityUrl -TimeoutSec 25
    $facilityName = [System.Net.WebUtility]::HtmlDecode([regex]::Match($page.Content, '(?s)<h1[^>]*>(.*?)</h1>').Groups[1].Value).Trim()
    $latitudeMatch = [regex]::Match($page.Content, '(?:let|const) facilityLat\s*=\s*(-?[\d.]+)')
    $longitudeMatch = [regex]::Match($page.Content, '(?:let|const) facilityLng\s*=\s*(-?[\d.]+)')
    $latitude = if ($latitudeMatch.Success) { [double]::Parse($latitudeMatch.Groups[1].Value, [Globalization.CultureInfo]::InvariantCulture) } else { $null }
    $longitude = if ($longitudeMatch.Success) { [double]::Parse($longitudeMatch.Groups[1].Value, [Globalization.CultureInfo]::InvariantCulture) } else { $null }
    [pscustomobject]@{
        name = $facilityName; latitude = $latitude; longitude = $longitude; source_url = $facilityUrl
        accepted = ($null -ne $latitude -and $null -ne $longitude -and $latitude -ge 3 -and $latitude -le 5.7 -and $longitude -ge 125 -and $longitude -le 126.3)
    }
}
$facilities | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath (Join-Path $sourceRoot 'facilities-review.json') -Encoding UTF8
$facilities | Format-Table name,latitude,longitude,accepted
$portNames = @('Tahuna','Petta','Bukide','Lipang','Kawio','Para','Matutuang','Kawaluso','Marore','Kahakitang','Kalama','Ngalipaeng','Pananaru')
$whereClause = 'namobj IN (' + (($portNames | ForEach-Object { "'$_'" }) -join ',') + ')'
$queryUrl = 'https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/SARANA_PRASARANA/MapServer/1/query'
$query = '?where=' + [uri]::EscapeDataString($whereClause) + '&outFields=objectid,namobj,lat,lon,metadata&f=pjson&outSR=4326'
$ports = (Invoke-WebRequest -UseBasicParsing -Uri ($queryUrl + $query) -TimeoutSec 25).Content | ConvertFrom-Json
if ($ports.error) { throw ($ports.error | ConvertTo-Json) }
@{ source_url = ($queryUrl + $query); retrieved_at = (Get-Date -Format o); response = $ports } | ConvertTo-Json -Depth 16 | Set-Content -LiteralPath (Join-Path $sourceRoot 'ports-big.json') -Encoding UTF8
$ports.features | ForEach-Object { $_.attributes } | Format-Table
