## マネージドクラウドへのデプロイ方法

![image](https://user-images.githubusercontent.com/13227145/50143655-8d725e00-02f0-11e9-92a0-2b24b4b432fc.png)

### マネージドクラウド側で初期設定を行う

#### 新規会員登録を行う

以下のリンクから新規会員登録を行ってください。

https://mc.lolipop.jp/

#### プロジェクトを作成する

コンテナはPHPコンテナを選択します。
![image](https://user-images.githubusercontent.com/13227145/50191052-3eb7d900-036f-11e9-89ae-e35a3c16f912.png)
次のステップでプロジェクト名とDBのパスワードを決めてプロジェクトを作成して下さい。

#### SSH公開鍵を登録する。

マネージドクラウドでは自分が作成したプロジェクトのサーバに入る為に公開鍵認証を使っています。
[SSH公開鍵の管理画面](https://mc.lolipop.jp/console/sshkeys)から公開鍵を登録して下さい。

![image](https://user-images.githubusercontent.com/13227145/50191369-ef72a800-0370-11e9-8078-c32d696e9443.png)

公開鍵の作成は自分の開発環境の`terminal`から`ssh-keygen -t rsa`を実行して`.ssh/`以下に公開鍵と秘密鍵を登録する方法と、ブラウザ側で公開鍵を作成して秘密鍵を取得する[SSHワンクリック登録](https://note.mu/mclolipopjp/n/n4cc4e43a7eda)方式があります。

今回は`terminal`側で生成する方法を紹介しますが、SSHワンクリック登録で生成する場合は秘密鍵を保存する場所は`$HOME/.ssh/`以下に保存する事をお勧めします。
この秘密鍵の保存場所は後で説明するデプロイ時の設定でも必要となりますので覚えておいて下さい。


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


以上でマネージドクラウド側での初期設定は終了となります。

### Laravel側の設定

次にLaravel側の設定になります。
まずはライブラリをインストールする為に`composer install`を実行します。

```sh
composer install
```

出来たら`.env.example`を`.env`としてコピーして下さい。

```
cp .env.example .env
```

アプリケーションキーを登録して下さい。

```
php artisan key:generate
```

バージョンを確認します。

```
php artisan -V
Laravel Framework 5.7.19
```

以上でLaravel側の設定は終わりになります。
デプロイする前にローカルでちゃんとLaravelがインストールされたかを確認したい場合は`php artisan server`でローカルサーバが立ち上がりますので、以下のコマンドを実行して下さい。

```
php artisan serve
Laravel development server started: <http://127.0.0.1:8000>
```

ブラウザに`http://127.0.0.1:8000`を入れてアクセスした際にLaravelの画面が表示されていれば無事にインストール作業は終わりです。
以上でLaravel側の設定は終わりになります。

### deployerの設定

マネージドクラウドにLaravelのプロジェクトをデプロイする為のデプロイツールとして、[deployer](https://deployer.org/)を使います。
deployer側でLaravel用の設定ファイルを自動で出力してくれるのですが、今回はその設定ファイルをマネージドクラウドにデプロイする為に変更を加えたテンプレートファイル(`deployer.example.php`)を用意したので、以下のコマンドを実行してコピーして下さい。

```sh
$ cp deploy.example.php deploy.php
```

次に、デプロイする為の設定を`deployer.php`と`.env`に記述していきます。

#### リポジトリ名とブランチ名と秘密鍵が置いてあるパスの設定

`deployer.php`の以下の設定項目を編集して、自分の作業しているブランチ名とリポジトリ名、そして秘密鍵(`id_rsa`)が置いてあるパスを記述して下さい。

```php
// git@github.com:Fendo181/lolipop-mc-starter-laravel.git
set('repository', '{REPOSITORY NAME}');

// master
set('branch', '{BRANCH NAME}');


// '~/.ssh/id_rsa'
->identityFile('{/path/to/id_rsa}')
```

#### デプロイ先のサーバの設定

`deployer.php`の`host`で始まっている所がデプロイ先のサーバの情報になります。


```php
host(env('DEPLOYER_MC_HOST'))
    ->stage('production')
    ->user(env('DEPLOYER_MC_USER'))
    ->port(env('DEPLOYER_MC_PORT'))
    // '~/.ssh/id_rsa'
    ->identityFile('{/path/to/id_rsa}')
    ->set('deploy_path', '/var/www/');
```

ここの設定項目は`.env`から取得するようにしています。それぞれ以下のように対応しています。

- `DEPLOYER_MC_HOST`: マネージドクラウド側で設定したホスト名
- `DEPLOYER_MC_USER`: マネージドクラウド側で設定したユーザ名
- `DEPLOYER_MC_PORT`: マネージドクラウド側で設定されているポート番号

![image](https://user-images.githubusercontent.com/13227145/50193298-5300d380-0379-11e9-8f00-daf42a1a0192.png)



#### マネージドクラウドのサーバから`Guthub`と公開鍵認証を行うように設定する

`deployer`を使ってマネージドクラウドのプロジェクトをデプロイする際に、設定を見てわかる通り`Github`からプロジェクトを指定して`git clone`してきます。
その際はSSH公開鍵認証で取得する為、マネージドクラウド側で生成した公開鍵を`Github`に登録する作業が必要です。
なので、上記で行った方法と同じ事を今度はマネージドクラウドのサーバ側で行います。

マネージドクラウドのサーバで実行して下さい。

```sh
$ ssh-keygen -t rsa -b 4096 -C "YOUR@EMAIL.com"

# 鍵を確認する
$ ls
id_rsa  id_rsa.pub  known_hosts

$ cat ~/.ssh/id_rsa.pub
```
公開鍵をGithubに登録します。
[ここ](https://github.com/settings/keys)から追加してください。

`Github`に正常に登録されたかを確認するには以下のコマンドを実行してください。

```sh
sh -T git@github.com
```

上手く行けば、こんな感じにレスポンスが返ってきます。

```
Hi Fendo181! You've successfully authenticated, butGitHub does not provide shell access.
odd-yoron-5564@ssh-laravel-first-endu-app:/var/www/
```

#### デプロイする

ここまでいけば`deployer.php`に設定は完了です。
`terminal`上でデプロイコマンドを実行して下さい。

```
 php ./vendor/bin/dep deploy production
```

デプロイに成功するとこんな感じに表示されます。

```sh
duction
✈︎ Deploying master on ssh-1.mc.lolipop.jp
✔ Executing task deploy:prepare
✔ Executing task deploy:lock
✔ Executing task deploy:release
✔ Executing task deploy:update_code
✔ Executing task upload:env
✔ Executing task deploy:shared
✔ Executing task deploy:vendors
✔ Executing task deploy:writable
✔ Executing task artisan:storage:link
✔ Executing task artisan:view:clear
✔ Executing task artisan:cache:clear
✔ Executing task artisan:config:cache
✔ Executing task artisan:optimize
✔ Executing task deploy:symlink
✔ Executing task deploy:unlock
✔ Executing task cleanup
Successfully deployed!
```

#### ドキュメントルートにシンボリックリンクを貼る

ここまで来れば後もう少しです。
マネージドクラウドでは`/var/www/html`以下がドキュメントルートになっており、ここにファイルを置く事でアクセスが来た際にページを表示するようになっています。一方で`deployer`でデプロイしたLaravelのプロジェクトはどこにデプロイされたのか?と言うと、`var/www/current`以下にデプロイされます。
正確には`var/www/releases/1`にデプロイされているのですが、`deployer`側で自動で[シンボリックリンク](https://kazmax.zpp.jp/linux_beginner/symbolic_link.html#ah_1)を貼って`var/www/current`に飛ぶようにしています。

これと同じようにシンボリックリンクを使って、ドキュメントルートになっている`var/www/html`にアクセスが来たら、`var/www/current/public`に飛ばすようにします。マネージドクラウドのサーバに入って以下のコマンドを実行してシンボリックリンクを生成して下さい。

```sh
ln -s /var/www/current/public/* /var/www/html/
```
※ここは絶対パスを指定してください。

その後、`var/www/html`にシンボリックリンクが貼られている確認します。
この時`var/www/html`以下には`index.html`と`index.php`が混在している状況になります。
Webの仕様上、`index.html`と`index.php`が両方存在する際は、`index.html`を先に見てしまうので、既存の`index.html`と`img`ディレクトリは削除して下さい。

```
$rm index.html
$rm -r img/
```

出来たら、管理画面に表示されている`プロジェクトURL`を叩いて、Laravelのプロジェクトが表示されている事を確認して下さい。


![image](https://user-images.githubusercontent.com/13227145/50228546-94ca6200-03eb-11e9-9899-a5ab46c9dc25.png)

おめでとうございます! :tada:
これでLaravelのプロジェクトがマネージドクラウドに無事にデプロイできアプリケーションが公開されました!

### マイグレーションを実行する

最後にマイグレーションですが、まずマネージドクラウド側の`mysql`に入る為の設定を`.env`に追記していきます。

#### .envにDBの設定を記述する

マネージドクラウドの管理画面に入ってmysqlの設定を元に`.env`の設定を追記してください。

```.env
DB_CONNECTION=mysql
DB_HOST=マネージドクラウドで設定しているmysqlのホスト名
DB_PORT=3306
DB_DATABASE=マネージドクラウドで設定しているmysqlのデータベース名
DB_USERNAME=マネージドクラウドで設定しているmysqlのユーザ名
DB_PASSWORD=マネージドクラウドで設定しているmysqlのパスワード
```

![image](https://user-images.githubusercontent.com/13227145/50230442-086e6e00-03f0-11e9-82f4-2650a4dc333a.png)

#### デプロイ実行時にマイグレーションを行う

この状態でデプロイ後にサーバに入って`php artisan migrade`を実行しても良いですが、`deployer`でデプロイ後に自動でマイグレーションを実行するようにする事もできます。`deployer.php`の一番下にある`before('deploy:symlink', 'artisan:migrate')`をコメントアウトを外して下さい。

```php
// Migrate database before symlink new release.
before('deploy:symlink', 'artisan:migrate');
```

この状態でデプロイを実行します。

```sh
木 20 :lolipop-mc-starter-laravel [endu]# php vendor/bin/dep deploy production
✈︎ Deploying master on ssh-1.mc.lolipop.jp
✔ Executing task deploy:prepare
✔ Executing task deploy:lock
✔ Executing task deploy:release
✔ Executing task deploy:update_code
✔ Executing task upload:env
✔ Executing task deploy:shared
✔ Executing task deploy:vendors
✔ Executing task deploy:writable
✔ Executing task artisan:storage:link
✔ Executing task artisan:view:clear
✔ Executing task artisan:cache:clear
✔ Executing task artisan:config:cache
✔ Executing task artisan:optimize
✔ Executing task artisan:migrate //自動でマイグレーションを実行してくれる
✔ Executing task deploy:symlink
✔ Executing task deploy:unlock
✔ Executing task cleanup
Successfully deployed!
```

マネージドクラウドのサーバに入ってmysql側で正常にtableが作成されたかを確認します。
以下のコマンドを実行してmysqlにはいります。

```sh
$ mysql -uDB_USERNAME -hDB_HOST -p
Enter password:DB_PASS
```
DBを選択する。

```
use DB_DATABASE
```

`table`を確認する。

```sql
mysql> mysql> select * from migrations;
+----+------------------------------------------------+-------+
| id | migration                                      | batch |
+----+------------------------------------------------+-------+
|  3 | 2014_10_12_000000_create_users_table           |     1 |
|  4 | 2014_10_12_100000_create_password_resets_table |     1 |
+----+------------------------------------------------+-------+
2 rows in set (0.00 sec)
```

マイグレーションが正常に実行された事を確認しました。
以上で「ロリポップ！マネージドクラウド スターター for Laravel」の解説は終わりになります。
お疲れ様でした。

## 資料

- [インストール 5.7 Laravel](https://readouble.com/laravel/5.7/ja/installation.html)
- [Deployer — How to deploy Laravel application](https://deployer.org/blog/how-to-deploy-laravel)
