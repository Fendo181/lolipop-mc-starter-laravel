## ロリポップ！マネージドクラウド スターター for Laravel

簡単に[ロリポップ! マネージドクラウド](https://mc.lolipop.jp/)に、Laravelのプロジェクトを公開する手順を説明します。

## インストール方法

### マネクラ側で初期設定を行う

#### マネクラで新規会員登録を行う

以下のリンクから新規会員登録を行ってください。
https://mc.lolipop.jp/

#### プロジェクトを作成する

コンテナはPHPコンテナを選択します。
![image](https://user-images.githubusercontent.com/13227145/50191052-3eb7d900-036f-11e9-89ae-e35a3c16f912.png)
次のステップでプロジェクト名とパスワードを決めてプロジェクトを作成して下さい。

#### SSH公開鍵を登録する。

マネクラでは自分が作成したプロジェクトのサーバに入る為に公開鍵認証を使っています。
[SSH公開鍵の管理画面](https://mc.lolipop.jp/console/sshkeys)から公開鍵を登録して下さい。

![image](https://user-images.githubusercontent.com/13227145/50191369-ef72a800-0370-11e9-8078-c32d696e9443.png)

公開鍵の作成は自分の開発環境の`terminal`から`ssh-keygen -t rsa`を実行して`.ssh/`以下に公開鍵と秘密鍵を登録する方法と、ブラウザ側で公開鍵を作成して秘密鍵を取得する[SSHワンクリック登録](https://note.mu/mclolipopjp/n/n4cc4e43a7eda)方式があります。

今回は`terminal`側で公開鍵を生成する方法を紹介しますが、SSHワンクリック登録で生成する場合は秘密鍵を保存する場所を`$HOME/.ssh/`以下に保存するようにして下さい。


```sh
$ ssh-keygen -t rsa -b 4096 -C "your email address"

$ ls ~/.ssh
id_rsa  id_rsa.pub

// 公開鍵の文字列をコピーする
cat .ssh/id_rsa.pub | pbcopy
```

コピーした公開鍵を登録して、管理画面に表示してあるSSHコマンドを叩いてサーバに入れる事を確認して下さい。

![image](https://user-images.githubusercontent.com/13227145/50192266-e2f04e80-0374-11e9-9599-8a025b7e2080.png)

入ると以下の画面が表示されます。

```sh
Last login: Tue Dec 18 08:57:51 2018 from 10.1.12.1
  __  __  ____   _          _ _
 |  \/  |/ ___| | |    ___ | (_)_ __   ___  _ __
 | |\/| | |     | |   / _ \| | | '_ \ / _ \| '_ \
 | |  | | |___ _| |__| (_) | | | |_) | (_) | |_) |
 |_|  |_|\____(_)_____\___/|_|_| .__/ \___/| .__/
                               |_|         |_|

******* Welcome to Lolipop! Managed Cloud *******
```


参考資料

>- [Linuxコマンド【 ssh-keygen 】認証用の鍵を生成 - Linux入門 - Webkaru](https://webkaru.net/linux/ssh-keygen-command/)
>- [SSH keyを4096bitで作成する - Qiita](https://qiita.com/hietahappousai/items/a7fae4838e510136fc08)


以上でマネクラ側での初期設定は終了となります。

### Laravel側の設定

`.env.example`を`.env`としてコピーして下さい。

```
cp .env.example .env
```

アプリケーションキーを登録して下さい。

```
php artisan key:generate
```

### Deployerの設定

マネージドクラウドにLaravelのプロジェクトをデプロイする為にデプロイツールとして、[deployer](https://deployer.org/)を使います。
deployer側でLaravel用の設定ファイルを自動で出力してくれるのですが、今回はその設定ファイルを事前にこちら側で用意したので、`deployer.php`と`.env`に設定を記述して言
って下さい

####リポジトリーとブランチの設定

`deployer.php`の以下の設定項目を編集して、自分の作業しているブランチ名とリポジトリー名を記述して下さい。
```php
// git@github.com:Fendo181/lolipop-mc-starter-laravel.git
set('repository', '{REPOSITORY NAME}');

// master
set('branch', '{BRANCH NAME}');
```

#### デプロイ先のサーバの設定

`deployer.php`の`host`で始まっている所がデプロイ先のサーバの情報になります。


```php
host(env('DEPLOYER_MC_HOST'))
    ->stage('production')
    ->user(env('DEPLOYER_MC_USER'))
    ->port(env('DEPLOYER_MC_PORT'))
    ->identityFile('~/.ssh/id_rsa.pub')
    ->set('deploy_path', '/var/www/');
```

ここの設定項目は`.env`から取得するようにしています。それぞれ以下のように対応しています。

- `DEPLOYER_MC_HOST`: マネクラ側で設定したホスト名
- `DEPLOYER_MC_USER`: マネクラ側で設定したユーザ名
- `DEPLOYER_MC_PORT`: マネクラ側で設定されているポート番号

![image](https://user-images.githubusercontent.com/13227145/50193298-5300d380-0379-11e9-8f00-daf42a1a0192.png)



#### マネクラのサーバから`Guthub`と公開鍵認証を行うように設定する

`deployer`を使ってマネクラのプロジェクトをデプロイする際に、設定を見てわかる通りGithubからプロジェクトを指定して`git clone`してきます。
その際にSSH公開鍵認証で取得する為、マネクラ側で生成した公開鍵をGithub側に登録する作業が必要です。
なので、上記で行った方法と同じ事を今度はマネクラのサーバ側で行います。

マネクラのサーバで行う

```sh
$ ssh-keygen -t rsa -b 4096 -C "YOUR@EMAIL.com"

# 鍵を確認する
$ ls
id_rsa  id_rsa.pub  known_hosts

$ cat ~/.ssh/id_rsa.pub
```
公開鍵をGithubに登録します。
[ここ](https://github.com/settings/keys)から追加してください。

Githunに正常に登録されたかを確認するには以下のコマンドを実行してください。

```sh
sh -T git@github.com
```

上手く行けば、こんな感じにレスポンスが返ってきます。

```
Hi Fendo181! You've successfully authenticated, butGitHub does not provide shell access.
odd-yoron-5564@ssh-laravel-first-endu-app:/var/www/
```

ここまでいけば`deployer.php`に設定は完了です。
`terminal`上でデプロイコマンドを実行して下さい。

```
 php ./vendor/bin/dep deploy production
```

## 参考資料

- [Deployer — How to deploy Laravel application](https://deployer.org/blog/how-to-deploy-laravel)
