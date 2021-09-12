# hyperf sentry

## WARNING

1. 原版sentry更新比较多，如果需要可以自行 fork 然后修改。

   - https://docs.sentry.io/platforms/php/
   - https://docs.sentry.io/platforms/php/guides/laravel/
   - https://docs.sentry.io/platforms/php/guides/laravel/other-versions/lumen/

2. 新版升级，需要熟悉 `sentry/sdk`， 改动较多

## 已知问题

sentry/sdk 依赖的 http 类库报错

    可能会报错 `Argument 1 passed to swoole_curl_setopt() must be an instance of Swoole\Curl\Handler, null given`
   - `vendor/sentry/sentry/src/Transport/HttpTransport.php:110`
   - `vendor/symfony/http-client/Response/CurlResponse.php:74`
   
解决方案：

1. 编译 `swoole` 的时候， 需要启用 ` --enable-swoole-curl` 参数，
2. 关闭CURL HOOK，修改 `SWOOLE_HOOK_FLAGS` to `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL`

    ```
    ! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL^SWOOLE_HOOK_CURL);
    ```

## 说明

`sentry/sdk` 中导出都是静态属性，这个些操作在 `swoole` 的携程环境中会出现数据异常，所以涉及到的部分都需要 `rewrite`, 因为 `sentry/sdk` 中的类大部分都是 `final` 的。

## 版本

主版本和 hyperf 保持一致

|version|hyperf version|说明|
|-|-|-|
|0.1.*|1.1.*|-|
|2.0.*|2.0.*|-|
|2.2.*|>=2.2.*| 本次更新移除 hyperf 包依赖|

## 使用

1. 配置文件

 发布： `php bin/hyperf.php vendor:publish minbaby/hyperf-sentry`
 
 然后在 `.env` 中添加 `SENTRY_DSN=`

2. 注册 `SentryExceptionHandler`

```php
return [
    'handler' => [
        'http' => [
            Minbaby\HyperfSentry\SentryExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
```

## 参考

- https://github.com/getsentry/sentry-php
- https://github.com/getsentry/sentry-laravel