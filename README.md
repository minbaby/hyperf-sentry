# hyperf sentry

## WARNING

1. 原版sentry更新比较多，如果需要可以自行 fork 然后修改。

   - https://docs.sentry.io/platforms/php/
   - https://docs.sentry.io/platforms/php/guides/laravel/
   - https://docs.sentry.io/platforms/php/guides/laravel/other-versions/lumen/

2. 新版升级，改动较多
   - 使用 [class_map](https://hyperf.wiki/2.0/#/zh-cn/annotation?id=classmap-%e5%8a%9f%e8%83%bd) 重写 `\Sentry\SentrySdk` 
   - 使用 Aspect，拦截单例，`Minbaby\HyperfSentry\Aspect\SingletonHookAspect::class`

## 已知问题
   
1. sentry/sdk 依赖的 http 类库报错

    报错 `Argument 1 passed to swoole_curl_setopt() must be an instance of Swoole\Curl\Handler, null given`
    - `vendor/sentry/sentry/src/Transport/HttpTransport.php:110`
    - `vendor/symfony/http-client/Response/CurlResponse.php:74`
   
    解决方案：

    1. 编译 `swoole` 的时候， 需要启用 ` --enable-swoole-curl` 参数，
    2. 关闭CURL HOOK，修改 `SWOOLE_HOOK_FLAGS` to `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL`

    说明：
      1. 从 v4.5.4 版本起，`SWOOLE_HOOK_ALL` 包括 `SWOOLE_HOOK_CURL` （这种是不完全hook，在某些场景下会报错）
      2. 从 v4.6.0 版本起，启用`--enable-swoole-curl`后， `SWOOLE_HOOK_ALL` 包括 `SWOOLE_HOOK_NATIVE_CURL`
## 说明

`sentry/sdk` 类库经过更新迭代，当前版本已经非常现代化了(3.0+)。再辅以 `Hyperf` 2.0+ 强大的 `AOP`功能，除了少部分单例和辅助方法，基本已经不需要特殊修改了。

## 版本

主版本和 hyperf 保持一致

|version|hyperf version|说明|
|-|-|-|
|0.1.*|1.1.*|-|
|2.0.*|2.0.*|-|
|2.2.*|>=2.1.*| 本次更新移除 hyperf 包依赖|
|3.0.*|>=3.0.*| 因为 `Hyperf\Utils\Context` => `Hyperf\Utils\Context` |

## 使用
1. 安装
 
```shell
composer require minbaby/hyperf-sentry
```

2. 配置文件

 发布： `php bin/hyperf.php vendor:publish minbaby/hyperf-sentry`
 
 然后在 `.env` 中添加 `SENTRY_DSN=`

3. 注册 `SentryExceptionHandler`

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
4. 执行 `php bin/hyperf.php sentry:test`

## 参考

- https://github.com/getsentry/sentry-php
- https://github.com/getsentry/sentry-laravel
