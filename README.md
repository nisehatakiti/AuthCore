# AuthCore

WordPress向けの共通認証エンジン／認証基盤プラグイン。

## Concept

AuthCoreは、WordPress上で複数の独立したアプリケーション／プラグインが認証機能を共通利用できるようにするための基盤です。

重要な設計方針は、**認証の仕組みは共通化するが、アカウントはアプリケーションごとに分離する**ことです。

### アカウントの考え方

同じ人間であっても、利用するシステムが違えば別アカウントとして管理します。

例えば、

- AlumniCore User #123
- StageArt User #123

は、同じID番号であっても完全に別のアカウントです。

一方、AlumniCoreをAlumniVoiceなどの関連プラグインで拡張する場合は、同じAlumniCore基盤上のユーザーとして同一ID・同一権限体系を利用できます。

つまり、**「同じ人間だから共通アカウント」ではなく、「同じアプリケーション基盤だからアカウントを共有する」**という考え方です。

## Architecture

```text
                         AuthCore
                  共通認証エンジン
                           │
          ┌────────────────┼────────────────┐
          │                │                │
      AlumniCore        StageArt          CDRMap
          │                │                │
     User #123        User #123        User #123
          │                │                │
     Alumni権限        StageArt権限      CDRMap権限
          │                │                │
    Alumni固有DB      StageArt固有DB     CDRMap固有DB
```

## Application / Extension

AuthCore対応プラグインは `application` と `extension` に分類されます。

- **Application** — 独立したユーザー・アカウント・権限空間を持つ製品
- **Extension** — 親Applicationのユーザー・権限体系を共有する拡張

Application Keyは論理識別子として扱い、Extensionの親はApplication Keyで指定します。

## Common User Management

AuthCoreはApplicationごとの共通ユーザー管理UIと認証APIを提供します。Extensionから利用する場合は、親Applicationのユーザー空間に自動的に解決されます。

管理画面は製品側のWordPressメニューへサブメニューとして登録され、ブラウザから任意のApplication IDを指定する方式は採用していません。

## Security

- アカウント、セッション、CapabilityはApplication境界で分離
- Extensionは親Applicationのコンテキストへ解決
- パスワードはWordPressのハッシュAPIで保存
- セッションDBにはTokenそのものを保存せずSHA-256ハッシュを保存
- セッションCookieはHttpOnly / SameSite=Lax、HTTPS時はSecure
- 認証失敗時は汎用エラーを使用
- 不明アカウントでもダミーハッシュのパスワードチェックを実施
- 管理画面ではWordPress capabilityを確認

## Development Status

Phase 0〜9の実装とセキュリティ強化が完了しています。

Phase 10では、実WordPress環境でのE2E / v1受入試験を行います。受入項目は [`docs/phase10.md`](docs/phase10.md) に定義しています。

**Current status: implementation-complete, runtime acceptance pending.**

実環境でのE2E試験を完了するまでは「完全検証済み」とは扱いません。
