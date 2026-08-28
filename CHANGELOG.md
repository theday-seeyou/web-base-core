# Changelog

## 0.3.0 - 2026-08-28

### Added

- 新增默认使用 nullable `deleted_at` 的 `BaseModel`。
- 新增 `mRecycleQuery()`、`mAllQuery()`、`mRestore()` 与 `mForceDelete()` 回收站接口。

### Changed

- 删除助手不再检测或兼容 `deleted`、`is_deleted`、`deleted_time` 等旧字段。
- `SystemBase` 与 `SystemUser` 统一改用 `BaseModel` 软删除契约。

## 0.2.1 - 2026-08-28

### Fixed

- 修复 Windows 下调试服务器进程状态因路径引号与尾斜杠差异而误报的问题。

## 0.2.0 - 2026-08-28

### Security

- 使用 RFC 4122 UUID v4 与密码学安全随机数。
- 新密文改用 AES-256-GCM 认证加密，保留旧 AES-CBC 密文读取兼容。
- 禁止反序列化数据实例化 PHP 对象，并限制解压后的最大体积。
- HTTP 请求默认启用 TLS 证书校验，只允许 HTTP/HTTPS 协议。
- 图片上传校验真实图片内容、实际 MIME 与最大体积。
- 调试服务器 host、port 与队列编号在拼接命令前进行严格校验。
- 参数化进程执行不经过 Shell；保留的 Shell 接口拒绝控制字符。
- 文件复制拒绝路径穿越、符号链接及重叠目录，删除操作拒绝文件系统根目录。

### Changed

- `ToolsExtend::copy()` 默认不覆盖目标文件，也不删除来源文件。
- PHPUnit 默认不连接数据库；数据库集成测试通过 `WEBBASE_TEST_DB_*` 环境变量显式启用。
- 移除测试文件中的旧仓库标头与硬编码数据库凭据。
