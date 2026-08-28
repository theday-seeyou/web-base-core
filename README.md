# WebBase Core

`theday-seeyou/web-base-core` 是 WebBase CMS 的可复用后台核心包，基于 ThinkPHP 8，提供控制器、模型、查询助手、RBAC、队列、存储和运行时服务。

## 安装

发布到 Packagist 后：

```bash
composer require theday-seeyou/web-base-core
php think service:discover
```

## 安全行为

- 本包不是 Composer plugin。
- Composer 安装和更新不会写入项目的 `app/`、`config/`、`database/` 或 `public/`。
- 包内不携带 Windows 可执行文件。
- `webbase:publish` 只能由开发者显式运行。
- `webbase:publish` 默认跳过已有文件，只有显式 `--force` 才覆盖。
- 发布命令不会自动删除旧迁移文件。

## 命名空间

```php
use WebBase\Core\Controller;
use WebBase\Core\Model;
use WebBase\Core\service\AdminService;
```

## 开发

```bash
composer install
composer validate --strict
vendor/bin/phpunit
```

## 许可证

项目自身修改使用 MIT License。衍生代码的原始版权声明见 `THIRD_PARTY_NOTICES.md`。
