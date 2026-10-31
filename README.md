# WebBase Core

`theday-seeyou/web-base-core` 是 WebBase 的可复用 ThinkPHP 后台核心包，
提供控制器、模型、查询助手、RBAC、队列、存储和运行时服务。

本包只包含通用后台基础设施，不包含动态内容模型设计器、页面搭建、
模板协议、模板市场或企业站内容产品能力。

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
- 文件复制默认不覆盖目标文件，也不会删除来源文件。
- HTTP/HTTPS 默认校验证书，并禁止跳转到本地文件等非网络协议。
- 加密数据使用 AES-256-GCM，同时保留旧 AES-CBC 密文的只读兼容。

## 命名空间

```php
use WebBase\Core\Controller;
use WebBase\Core\BaseModel;
use WebBase\Core\Model;
use WebBase\Core\service\AdminService;
```

## 默认软删除与回收站

需要回收站能力的业务模型统一继承 `BaseModel`，对应数据表必须包含
`deleted_at datetime null default null`。框架不检测也不兼容其他删除字段。

```php
final class Article extends BaseModel
{
}

Article::mQuery();         // 仅正常数据，自动添加 deleted_at IS NULL
Article::mRecycleQuery();  // 仅回收站数据
Article::mAllQuery();      // 正常数据和回收站数据
Article::mDelete();        // 软删除
Article::mRestore();       // 恢复
Article::mForceDelete();   // 从回收站永久删除
```

## 开发

```bash
composer install
composer validate --strict
vendor/bin/phpunit
composer audit
```

普通单元测试不要求数据库。需要运行数据库集成测试时，显式配置：

```bash
set WEBBASE_TEST_DB_HOST=127.0.0.1
set WEBBASE_TEST_DB_PORT=3306
set WEBBASE_TEST_DB_NAME=webbase_test
set WEBBASE_TEST_DB_USER=root
set WEBBASE_TEST_DB_PASSWORD=your-password
vendor/bin/phpunit
```

PowerShell 使用 `$env:WEBBASE_TEST_DB_NAME = "webbase_test"` 形式设置环境变量。

## 许可证

项目自身修改使用 MIT License。衍生代码的原始版权声明见 `THIRD_PARTY_NOTICES.md`。
