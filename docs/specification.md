# AuthCore Specification

## AuthCore対応プラグイン メタ情報仕様 v1.0

### 1. 目的

AuthCore対応プラグインは、WordPressプラグインヘッダーにAuthCore専用メタ情報を定義する。

AuthCoreはこのメタ情報を読み取り、対象プラグインを `application` または `extension` として認識し、Applicationの登録・既存Applicationとの関連付けを行う。

### 2. プラグイン種別

#### application

独立したユーザー・アカウント・権限空間を持つ製品。

例:

- AlumniCore
- StageArt
- CDRMap

#### extension

既存Applicationの機能を拡張するプラグイン。独立したユーザー空間を持たず、親ApplicationのユーザーID・権限体系を共有する。

例:

- AlumniVoice → AlumniCore
- AlumniEvent → AlumniCore

### 3. Application必須メタ情報

| メタ情報 | 必須 | 値の例 | 説明 |
|---|---|---|---|
| `AuthCore` | ○ | `application` | プラグイン種別 |
| `AuthCore Application Key` | ○ | `alumni` | Applicationを一意に識別する固定キー |
| `AuthCore Application Name` | ○ | `AlumniCore` | 人間向けの表示名称 |

例:

```php
/*
Plugin Name: AlumniCore
Version: 1.0.0
Author: nisehatakiti

AuthCore: application
AuthCore Application Key: alumni
AuthCore Application Name: AlumniCore
*/
```

### 4. Extension必須メタ情報

| メタ情報 | 必須 | 値の例 | 説明 |
|---|---|---|---|
| `AuthCore` | ○ | `extension` | プラグイン種別 |
| `AuthCore Application Key` | ○ | `alumni-voice` | Extension自身を一意に識別するキー |
| `AuthCore Application Name` | ○ | `AlumniVoice` | 人間向けの表示名称 |
| `AuthCore Parent Application` | ○ | `alumni` | 親ApplicationのApplication Key |

例:

```php
/*
Plugin Name: AlumniVoice
Version: 1.0.0
Author: nisehatakiti

AuthCore: extension
AuthCore Application Key: alumni-voice
AuthCore Application Name: AlumniVoice
AuthCore Parent Application: alumni
*/
```

### 5. 共通任意メタ情報

以下はApplication / Extension共通の任意項目とする。

| メタ情報 | 必須 | 値の例 | 用途 |
|---|---|---|---|
| `AuthCore Application Version` | 任意 | `1.0.0` | AuthCore上での製品バージョン |
| `AuthCore Application URI` | 任意 | `https://github.com/nisehatakiti/AlumniCore` | 製品情報へのURI |
| `AuthCore Vendor` | 任意 | `nisehatakiti` | 開発元識別 |
| `AuthCore Vendor URI` | 任意 | `https://github.com/nisehatakiti` | 開発元情報 |
| `AuthCore Description` | 任意 | `同窓会管理システム` | AuthCore管理画面用説明 |
| `AuthCore Icon` | 任意 | `dashicons-groups` | 管理画面表示用アイコン |

### 6. Extension専用予約項目

`AuthCore Extension Type` を将来の拡張用予約項目とする。

v1.0では具体的な値を規定しない。

### 7. Application Key

`AuthCore Application Key` はAuthCore上の論理識別子とする。

- 必須
- Application / Extensionごとに一意
- 原則として変更不可
- 小文字英数字とハイフンを基本とする
- 製品表示名称とは分離する

推奨例:

```text
alumni
stageart
cdrmap
alumni-voice
```

### 8. Parent Application

`AuthCore Parent Application` はApplication IDではなく、親Applicationの `application_key` で指定する。

これにより、AuthCore内部でApplication IDが変更されてもプラグインのメタ情報を変更する必要がない。

例:

```text
AuthCore Parent Application: alumni
```

は、AuthCore内部で `application_key = alumni` のApplicationを検索し、その `application_id` を親として解決する。

### 9. ユーザー空間のルール

Applicationは独立したユーザー空間を持つ。

Extensionは親Applicationのユーザー空間を共有し、新しいユーザー空間を作成しない。

したがって、次は別アカウントである。

```text
AlumniCore User #123
StageArt User #123
```

一方、以下は同じユーザー空間を利用する。

```text
AlumniCore User #123
AlumniVoice → AlumniCore User #123を利用
```

### 10. メールアドレスとログインID

メールアドレスおよびログインIDはApplication単位で一意とする。

```sql
UNIQUE (application_id, email)
UNIQUE (application_id, login_id)
```

異なるApplication間では同じメールアドレス、同じログインIDを登録できる。

### 11. AuthCoreによる登録フロー

プラグイン有効化時に、対象プラグインのAuthCoreメタ情報を読み取る。

```text
Plugin Activation
      ↓
AuthCore Metadata Detection
      ↓
AuthCore metadata exists?
      ├─ No → AuthCore対象外
      └─ Yes
           ↓
        Type check
        ├─ application
        │    ↓
        │  Application存在確認
        │    ↓
        │  未登録なら自動登録
        │
        └─ extension
             ↓
           Parent Application確認
             ↓
           親Application取得
             ↓
           Extension登録
```

### 12. エラー処理

必須メタ情報が不足している場合や、Extensionの親Applicationが存在しない場合は登録処理を完了しない。

例:

```text
ERROR: Required AuthCore metadata is missing.
ERROR: Parent Application "unknown" was not found.
```

不完全なメタ情報から中途半端なApplicationを自動生成しない。

### 13. ApplicationとExtensionの違い

| 項目 | Application | Extension |
|---|---|---|
| 独立ユーザー空間 | ○ | × |
| 独自Application ID | ○ | × |
| 独自ユーザーID体系 | ○ | × |
| 独自ログイン領域 | ○ | × |
| 親Application | なし | 必須 |
| ユーザー共有 | なし | 親Applicationと共有 |
| 権限 | 独自 | 親Applicationを拡張可能 |
| 固有データ | 独自 | 独自 |
| AuthCore利用 | ○ | ○ |

### 14. 設計原則

1. 認証処理はAuthCoreで共通化する。
2. アカウントはApplication単位で分離する。
3. User IDはApplication内で独立したIDとして扱う。
4. 権限はApplication単位で分離する。
5. Extensionは親Applicationのユーザー・権限体系を共有する。
6. Application固有データは各プラグイン側で管理する。
7. AuthCoreはApplication固有の業務データを直接管理しない。
8. ApplicationとExtensionの判定は、プラグイン自身が宣言するAuthCoreメタ情報を基準とする。

## 15. Status

**v1.0 確定仕様**

今後の実装は、本仕様を基準として進める。
