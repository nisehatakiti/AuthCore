# AuthCore Phase 1 — Database Foundation

## Goal

AuthCore v1のApplication / User Account管理に必要なデータベース基盤を構築する。

## Tables

### wp_authcore_applications

- `application_id` — 主キー
- `application_key` — 論理識別子、一意
- `name` — 表示名称
- `status` — Application状態
- `created_at`
- `updated_at`

### wp_authcore_user_accounts

- `user_account_id` — アカウントID
- `application_id` — 所属Application
- `login_id` — Application内で一意
- `email` — Application内で一意
- `password_hash` — 認証用ハッシュ
- `status` — アカウント状態
- `email_verified` — メール認証状態
- `created_at`
- `updated_at`
- `last_login_at`

## Isolation Rules

- User Accountは必ずApplicationに所属する。
- `(application_id, login_id)` は一意。
- `(application_id, email)` は一意。
- 異なるApplication間では同じlogin_id / emailを許可する。
- AuthCoreはApplication固有データを管理しない。
- Extensionは独自のUser Accountを作成しない。

## Migration

- WordPressの`dbDelta()`を利用する。
- `authcore_db_version`でスキーマバージョンを管理する。
- Activation時にInstallを実行する。
- 通常のPlugin初期化時にもバージョン差分を検出しMigrationを実行する。
- Deactivationではデータを削除しない。

## Completion Criteria

1. AuthCoreを新規有効化すると2テーブルが作成される。
2. 再有効化しても既存データが破壊されない。
3. DBバージョンが保存される。
4. 将来のDBバージョン変更時にMigrationを実行できる。
5. Application Keyが一意である。
6. Application内のlogin_idが一意である。
7. Application内のemailが一意である。
8. 異なるApplication間では同じlogin_id / emailを許可できる。
9. WordPressのサイトDB prefixに従ってテーブル名が生成される。
10. DeactivationでAuthCoreのデータが削除されない。

## Status

Implementation completed on `feature/phase0-foundation`. Runtime verification in a real WordPress environment remains part of the Phase 1 acceptance check.
