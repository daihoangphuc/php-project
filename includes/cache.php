<?php
function getCache($key) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
        return unserialize(file_get_contents($cacheFile));
    }
    return false;
}

function setCache($key, $data, $ttl = 3600) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';
    file_put_contents($cacheFile, serialize($data));
} 