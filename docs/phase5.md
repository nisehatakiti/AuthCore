# AuthCore Phase 5 — Session / Current User

## 目的

Phase 5では、Phase 4の認証結果をログイン状態として保持し、ログアウトと現在ユーザー取得を提供する。

対象:

- ログイン状態の保持
- ログアウト
- 現在ユーザー取得
- アプリケーション境界の検証
- Extensionの親Applicationユーザー空間共有
- 公開API

## セッション方式

AuthCoreはPHPのグローバルセッションには依存せず、AuthCore専用のランダムトークンをHTTP Cookieで保持する。

Cookieに保存するのは64文字のhex tokenのみ。DBにはtokenそのものではなくSHA-256 hashを保存する。

Cookie属性:

- HttpOnly: true
- Secure: HTTPS時のみtrue
- SameSite: Lax
- Path: WordPressのCOOKIEPATH（未設定時は `/`）
- 有効期間: 14日

Cookie名はApplication contextごとに `authcore_session_{application_id}` とする。これにより、AlumniCoreとStageArtのログイン状態を独立して保持できる。

## セッションDB

`wp_authcore_sessions`:

- `session_id`
- `application_id`
- `user_account_id`
- `token_hash`
- `created_at`
- `expires_at`
- `last_used_at`

`token_hash` は一意。`application_id` と `user_account_id`、有効期限にインデックスを持つ。

DB versionは `1.2.0` に更新した。

## Login

```php
$result = AuthCore::login($applicationId, $identifier, $password);
```

内部ではPhase 4の `Authenticator` を使用する。

認証成功時のみ:

1. 既存の同一Applicationセッションを破棄
2. 暗号学的に安全なランダムtokenを生成
3. tokenのSHA-256 hashをDBへ保存
4. tokenをHttpOnly Cookieへ設定

認証失敗時はCookieを新規発行しない。

戻り値はPhase 4と同じ `AuthenticationResult`。パスワードhashは含まれない。

## Logout

```php
AuthCore::logout($applicationId);
```

指定Application contextのCookieを読み、対応するDBセッションをApplication IDで限定して削除したうえでCookieを失効させる。

## Current User

```php
$account = AuthCore::getCurrentAccount($applicationId);
```

ログイン中のアカウントを返す。未ログイン、期限切れ、不正token、またはactive以外のアカウントの場合は `null` を返す。

Current User取得時にもApplication IDをDBクエリへ必ず含めるため、別Applicationのセッションを利用できない。

また、セッションに紐づくアカウントが後から `suspended` / `disabled` になった場合も、次回Current User取得時にセッションを無効化する。

## Application boundary

`applicationId` はセッション処理のたびに登録済みかつactiveかを検証する。

Extensionが指定された場合は、そのExtensionの `parent_application_id` をApplication contextとして使用する。

したがって、例えば:

```text
AlumniVoice (extension)
    ↓ parent
AlumniCore (application)
    ↓
User Account #123
```

の場合、AlumniVoiceはAlumniCoreと同じユーザー空間を利用する。

一方、StageArtなど別Applicationのcontextでは、そのセッションを利用できない。

親Applicationが存在しない、applicationではない、またはinactiveの場合は `AuthCoreException` とする。

## Public API

```php
AuthCore::login($applicationId, $identifier, $password): AuthenticationResult
AuthCore::logout($applicationId): void
AuthCore::getCurrentAccount($applicationId): ?array
```

Phase 4の直接認証APIも引き続き利用できる。

```php
AuthCore::authenticate($applicationId, $identifier, $password): AuthenticationResult
```

`authenticate()` は認証のみでセッションを作成しない。ログイン状態を作る場合は `login()` を利用する。

## Security notes

- Session tokenは `random_bytes()` で生成
- DBにはtoken hashのみ保存
- CookieはHttpOnly
- HTTPS時はSecure
- SameSite=Lax
- Session lookupは必ずApplication IDでスコープ
- Expired sessionはCurrent User取得時に削除
- Account statusもCurrent User取得時に再検証
- Logoutは対象Application contextのセッションだけを削除
- パスワードhashはCurrent Userの戻り値から除外

## Phase 5 completion criteria

- [x] Session DB schema
- [x] Session repository
- [x] Login state persistence
- [x] Logout
- [x] Current User retrieval
- [x] Application boundary validation
- [x] Extension parent Application context
- [x] Secure cookie handling
- [x] Public API
- [x] Documentation
- [ ] Runtime verification in a real WordPress environment
