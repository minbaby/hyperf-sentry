# hyperf sentry

## 说明

`sentry/sdk` 中导出都是静态属性，这个些操作在 `swoole` 的携程环境中会出现数据异常，所以涉及到的部分都需要 `rewrite`, 因为 `sentry/sdk` 中的类大部分都是 `final` 的。

## 版本

主版本和 hyperf 保持一致

|version|hyperf version|说明|
|-|-|-|
|0.1.*|1.1.*|-|
|2.0.*|2.0.*|-|

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