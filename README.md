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

AuthCoreが共通化するのは、主に認証処理そのものです。

- ログイン／ログアウト
- パスワード管理
- パスワードリセット
- メールアドレス確認
- セッション管理
- ログイン試行制御
- 認証に関するセキュリティ機能
- 各アプリケーションから利用する認証API

一方、以下は原則として各アプリケーションが独立して管理します。

- アプリケーション固有のユーザー情報
- アプリケーション固有のプロフィール
- アプリケーション固有の権限
- アプリケーション固有のデータ

## Application Boundary

AuthCoreを利用するプラグインは、それぞれ独立したユーザー空間を持ちます。

```text
AuthCore
│
├── AlumniCore
│    ├── Alumni User #1
│    ├── Alumni User #2
│    └── Alumni User #123
│
├── StageArt
│    ├── StageArt User #1
│    ├── StageArt User #2
│    └── StageArt User #123
│
└── CDRMap
     └── CDRMap User #123
```

各アプリケーションから、原則として他アプリケーションのユーザー情報や権限は見えない状態を目指します。

## Extensibility

AuthCoreを基盤として、関連プラグインが独自の機能や権限を追加できる拡張機構を提供します。

例えば、AlumniCoreをインストールした場合、AuthCore上にAlumniCore専用の権限体系を登録できます。

```text
AuthCore
│
├── AlumniCore
│    ├── user.view
│    ├── user.edit
│    └── event.manage
│
└── StageArt
     ├── user.view
     ├── work.edit
     └── production.manage
```

同じ名前の権限であっても、アプリケーションが異なれば別の権限として扱える設計を想定しています。

AlumniVoiceのようにAlumniCoreそのものを拡張するプラグインについては、AlumniCoreのユーザーID・権限体系を共有できます。

## Design Principles

1. **Authentication is shared.**  認証処理・セキュリティ機構はAuthCoreで共通化する。
2. **Accounts are application-scoped.**  アカウントは利用するアプリケーション単位で管理する。
3. **User IDs are local to an application.**  User #123は各アプリケーションで独立したIDとして扱う。
4. **Permissions are application-scoped.**  権限は各アプリケーションの境界を越えて共有しない。
5. **Extensions share their parent application's identity.**  同一アプリケーション基盤を拡張するプラグインは、親アプリケーションのユーザー・権限体系を利用できる。
6. **Application data remains isolated.**  各アプリケーション固有のデータは、それぞれのプラグイン側で管理する。

## Planned Use

AuthCoreは、WordPress上に複数の独立したアプリケーションを構築する際の共通認証基盤として利用することを想定しています。

```text
                    WordPress
                       │
                    AuthCore
                       │
        ┌──────────────┼──────────────┐
        ↓              ↓              ↓
    AlumniCore      StageArt        CDRMap
        │              │              │
   AlumniVoice      各種拡張        各種拡張
```

## Status

現在は設計・仕様策定フェーズです。

今後、認証仕様、データベース設計、プラグインAPI、権限モデル、管理画面などを順次実装していきます。
