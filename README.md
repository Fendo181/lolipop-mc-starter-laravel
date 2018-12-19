## ロリポップ！マネージドクラウド スターター for Laravel

簡単に[ロリポップ! マネージドクラウド](https://mc.lolipop.jp/)に、Laravelのプロジェクトを公開する手順を説明します。

![image](https://user-images.githubusercontent.com/13227145/50228546-94ca6200-03eb-11e9-9899-a5ab46c9dc25.png)

### Tutorial

はじめて、マネージドクラウドを使ってLaravelのプロジェクトを公開をする方は[tutorial_jp.md](https://github.com/Fendo181/lolipop-mc-starter-laravel/blob/master/docs/tutorial_jp.md)を参考にしてはじめてみて下さい。

### Quick Start

>※既に[ロリポップ！マネージドクラウド](https://mc.lolipop.jp/)で登録済みでサーバにログインできている状態を想定しています。

すぐに始めてみたい方は、まずプロジェクトを落としてきて

```sh
git clone git@github.com:Fendo181/lolipop-mc-starter-laravel.git
composer install
cp .env.example.php .env
php artisan key:generate
```

設定ファイルをコピーする
```
cp deployer.exaple.php deployer.php
```

マネージドクラウドに管理画面の情報に従って環境変数を追記する

```
# mysql
DB_CONNECTION=mysql
DB_HOST=マネージドクラウドで設定しているmysqlのホスト名
DB_PORT=3306
DB_DATABASE=マネージドクラウドで設定しているmysqlのデータベース名
DB_USERNAME=マネージドクラウドで設定しているmysqlのユーザ名
DB_PASSWORD=マネージドクラウドで設定しているmysqlのパスワード

# deployer MC setting
DEPLOYER_MC_HOST`: マネージドクラウド側で設定したホスト名
DEPLOYER_MC_USER`: マネージドクラウド側で設定したユーザ名
DEPLOYER_MC_PORT`: マネージドクラウド側で設定されているポート番号
```

リポジトリ名とブランチ名と秘密鍵を置いてあるパスの情報を`deployer.php`に記述する

```php
// git@github.com:Fendo181/lolipop-mc-starter-laravel.git
set('repository', '{REPOSITORY NAME}');

// master
set('branch', '{BRANCH NAME}');


// '~/.ssh/id_rsa'
->identityFile('{/path/to/id_rsa}')
```

サーバに入って、マネージドクラウド側でプロジェクトを`git clone`する為の公開鍵と秘密鍵を生成する
[こちらの説明](https://github.com/Fendo181/lolipop-mc-starter-laravel/blob/master/docs/starter_jp.md#%E3%83%9E%E3%83%8D%E3%83%BC%E3%82%B8%E3%83%89%E3%82%AF%E3%83%A9%E3%82%A6%E3%83%89%E3%81%AE%E3%82%B5%E3%83%BC%E3%83%90%E3%81%8B%E3%82%89guthub%E3%81%A8%E5%85%AC%E9%96%8B%E9%8D%B5%E8%AA%8D%E8%A8%BC%E3%82%92%E8%A1%8C%E3%81%86%E3%82%88%E3%81%86%E3%81%AB%E8%A8%AD%E5%AE%9A%E3%81%99%E3%82%8B)を参考にして下さい。

ここまで出来たら、`deployer`でLaravelのプロジェクトをデプロイします

```sh
php ./vendor/bin/dep deploy production

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

マネージドクラウドのサーバに入って、デプロイされたプロジェクのシンボリックリンクをサーバ側で設定しているドキュメントルート(`var/www/html`)に貼ります

```sh
$ cd html/
// 既存のファイルを消しておく
$rm index.html
$rm -r img/

// シンボリックリンクを貼る
ln -s /var/www/current/public/* /var/www/html/x
```

マネージドクラウドの管理画面に戻って`プロジェクトURL`をクリックして、Laravelで作成したプロジェクトの画面が表示されている事を確認して下さい。

作業は以上に以上になります。
お疲れ様でした。
