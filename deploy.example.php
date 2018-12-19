<?php
namespace Deployer;

require 'recipe/laravel.php';
// phpdotenvを呼びすようにする
with(new \Dotenv\Dotenv(__DIR__))->load();

set('application', env('APP_NAME'));
// 'git@github.com:Fendo181/lolipop-mc-starter-laravel.git'
set('repository', '{Your Repository Name}');
// 'master'
set('branch', '{Your Branch Name}');
set('git_tty', false);

add('shared_files', ['.env']);
add('shared_dirs', []);
add('writable_dirs', ['bootstrap/cache', 'storage']);

host(env('DEPLOYER_MC_HOST'))
    ->stage('production')
    ->user(env('DEPLOYER_MC_USER'))
    ->port(env('DEPLOYER_MC_PORT'))
    // '~/.ssh/id_rsa'
    ->identityFile('{/path/to/id_rsa}')
    ->set('deploy_path', '/var/www/');

task('build', function () {
    run('cd {{release_path}} && build');
});

// .envをアップロードする
task('upload:env', function () {
    upload('.env', '{{deploy_path}}/shared/.env');
})->desc('.envをアップロード');

before('deploy:shared','upload:env');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'artisan:migrate');
