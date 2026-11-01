# Changelog

## 0.5.1 - 2026-11-01

- Enable default nullable deleted_at soft deletion for roles, menus and attachment records, alongside users and dictionaries.
- Preserve role grants during recycling and restoration; permanently remove grants only for role IDs actually purged inside the same transaction.
- Hide account password hashes from model serialization while retaining session password-fingerprint validation.
- Reject structured language parameters before the framework string parser, preventing malformed language input from returning HTTP 500.
- Treat missing or non-array queue payloads as empty arrays so terminal tasks can be reset safely.
- Companion Starter adds real recycle views and owner-scoped attachment actions, tree-menu validation, a public Chinese/English index and expanded administration HTTP regressions.

## 0.5.0 - 2026-11-01

- Standardize model audit fields as created_at and updated_at DATETIME with automatic writes; keep deleted_at NULL as the only soft-delete contract.
- Remove old system-model timestamp overrides and update queue, log and message consumers.
- Add an explicit migration base and creator template that supply an unsigned auto-increment id and all three DATETIME columns, with safe refusal for occupied table names and reversible change migrations.
- Remove MD5 password verification and the legacy upgrade API. Require the same 12–72 byte bcrypt policy for login and new passwords; retain bcrypt cost rehashing.
- This is a fresh-install schema boundary and requires the matching Starter 0.5. Existing 0.4 tags retain their original behavior.

## 0.4.0 - 2026-11-01

- Add shared bcrypt password policy, legacy MD5 verification/rehash, stable session CSRF tokens, atomic single-host login rate limits and server-side upload validation.
- Consume captcha attempts, use cryptographic challenge identifiers, and reject empty/missing captcha values.
- Revalidate administrator accounts/password fingerprints and refresh role permissions on every protected request without putting password hashes in session user data.
- Default CORS to disabled and require exact allowed origins. Preserve response headers and rotated session cookies when controllers return through exceptions.
- Default form token injection, reject unsafe GET mutations, and refuse unbounded saves or missing-record form updates.
- Add security regression tests and PHP CI. Consumers must update the companion Starter login/form JavaScript and widen password storage to 255 characters together with this release.
- Replace generated virtual models with validated TableModel instances; remove the global m() helper and the virtual-model stream implementation.

The password-change plugin event no longer contains plaintext passwords. Existing login sessions must sign in again. File-based rate limiting targets one application host; use a shared atomic limiter for multiple hosts.

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
