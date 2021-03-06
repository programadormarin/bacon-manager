<?php

/**
 * Symfony Configuration
 */

set('repository', 'git@bitbucket.org:agenciaa2c/a2c-manager.git');

env('branch', 'master');

// Symfony shared dirs
set('shared_dirs', ['app/logs']);

// Symfony shared files
set('shared_files', ['app/config/parameters.yml']);

// Symfony writable dirs
set('writable_dirs', ['app/cache', 'app/logs']);

// Assets
set('assets', ['web/css', 'web/images', 'web/js']);

// Environment vars
env('composer_options', 'install  --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction');
env('env_vars', 'SYMFONY_ENV=prod');
env('env', 'prod');

// Adding support for the Symfony3 directory structure
set('bin_dir', 'app');
set('var_dir', 'app');


/**
 * Create cache dir
 */
task('deploy:create_cache_dir', function () {
    // Set cache dir
    env('cache_dir', '{{release_path}}/' . trim(get('var_dir'), '/') . '/cache');

    // Remove cache dir if it exist
    run('if [ -d "{{cache_dir}}" ]; then rm -rf {{cache_dir}}; fi');

    // Create cache dir
    run('mkdir -p {{cache_dir}}');

    // Set rights
    run("chmod -R g+w {{cache_dir}}");
})->desc('Create cache dir');


/**
 * Normalize asset timestamps
 */
task('deploy:assets', function () {
    $assets = implode(' ', array_map(function ($asset) {
        return "{{release_path}}/$asset";
    }, get('assets')));

    $time = date('Ymdhi.s');

    run("find $assets -exec touch -t $time {} ';' &> /dev/null || true");
})->desc('Normalize asset timestamps');


/**
 * Dump all assets to the filesystem
 */
task('deploy:assetic:dump', function () {

    run('php {{release_path}}/' . trim(get('bin_dir'), '/') . '/console assetic:dump --env={{env}} --no-debug');

})->desc('Dump assets');


/**
 * Warm up cache
 */
task('deploy:cache:warmup', function () {

    run('php {{release_path}}/' . trim(get('bin_dir'), '/') . '/console cache:warmup  --env={{env}} --no-debug');

})->desc('Warm up cache');

/**
 * clean cache
 */
task('deploy:cache:clean', function () {

    run('php {{release_path}}/' . trim(get('bin_dir'), '/') . '/console c:c  --env={{env}}');

})->desc('clean cache');


/**
 * Migrate database
 */
task('database:migrate', function () {

    run('php {{release_path}}/' . trim(get('bin_dir'), '/') . '/console d:s:u --env={{env}} --force');

})->desc('Migrate database');


/**
 * Remove app_dev.php files
 */
task('deploy:clear_controllers', function () {

    run("rm -f {{release_path}}/web/app_*.php");
    run("rm -f {{release_path}}/web/config.php");

})->setPrivate();

after('deploy:update_code', 'deploy:clear_controllers');


/**
 * Update project dependencies
 */
task('deploy:update_dependencies', function () {
    writeln("Updating npm libraries...");
    run("npm update");
})->desc('Updating dependencies');


task('deploy:bower', function () {
    cd('{{release_path}}');
    writeln("Updating bower packages...");
    run("bower install --allow-root");
})->desc('Updating dependencies');

/**
 * Create cache dir
 */
task('deploy:cache_chmod', function () {
    run("chmod -Rf 777 {{cache_dir}}");
})->desc('Create cache dir');


/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:create_cache_dir',
    'deploy:shared',
    'deploy:writable',
    'deploy:assets',
    'deploy:vendors',
    'deploy:update_dependencies',
    'deploy:assetic:dump',
    'deploy:cache:warmup',
    'deploy:cache:clean',
    'deploy:symlink',
    'cleanup',
    'deploy:cache_chmod',
    'deploy:bower',
])->desc('Deploy your project');

after('deploy', 'success');