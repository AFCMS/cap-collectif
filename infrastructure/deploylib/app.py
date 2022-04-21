from task import task
from fabric.operations import local, run, settings
from fabric.api import env

import os


@task
def deploy(environment='dev', user='capco'):
    "Deploy"
    if os.environ.get('CI') == 'true':
        env.compose('run -u root builder chown capco:capco -R /var/www/web /home/capco/.composer /home/capco/.cache/yarn /home/capco/.cache/bower')
    env.compose('run' + ('', ' -e PRODUCTION=true')[environment == 'prod'] + ('', ' -e CI=true')[os.environ.get('CI') == 'true'] + ' builder build')
    env.service_command('php vendor/sensio/distribution-bundle/Resources/bin/build_bootstrap.php var', 'application', env.www_app)
    env.service_command('rm -rf var/cache/dev var/cache/prod var/cache/test', 'application', env.www_app, 'root')
    env.service_command('php bin/rabbit vhost:mapping:create --password=guest --erase-vhost app/config/rabbitmq.yml', 'application', env.www_app)
    env.service_command('php bin/console simplesamlphp:config --no-interaction --env=' + environment, 'application', env.www_app)
    env.service_command('php bin/console cache:warmup --no-optional-warmers --env=' + environment, 'application', env.www_app)
    env.service_command('php bin/console assets:install --symlink', 'application', env.www_app)


@task
def clean():
    "Clean"
    env.compose('stop chrome || true')
    env.compose('rm -f chrome')


@task
def ssh(user='capco'):
    "Ssh into application container"
    env.ssh_into('application', user)


@task
def clear_cache(environment='dev'):
    "Clear cache"
    env.service_command('rm -rf var/cache/' + environment, 'application', env.www_app, 'root')


@task
def cmd(command='', environment='dev'):
    "Executing Symfony command"
    env.service_command('php -d memory_limit=-1 bin/console ' + command + ' --no-interaction --env=' + environment, 'application', env.www_app, 'root')
