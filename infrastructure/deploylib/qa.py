from task import task
from fabric.operations import local, run, settings
from fabric.api import env
import time

capcobot = {
    'user': 'capco',
    'email': 'capco.bot@gmail.com',
    'pass': 'elephpant-can-fly',
}


@task(environments=['local', 'testing'])
def check_dependencies():
    "Check dependencies"
    env.compose_run('composer validate', 'builder', '.', no_deps=True)
    env.service_command('bin/console security:check', 'application', env.www_app)


@task(environments=['local', 'testing'])
def check_codestyle():
    "Check code style"
    env.compose_run('yarn run checkcs', 'builder', '.', no_deps=True)
    env.compose_run('pep8 infrastructure/deploylib --ignore=E501', 'builder', '.', no_deps=True)
    env.service_command('php bin/console lint:twig app src', 'application', env.www_app)
    env.compose_run('php-cs-fixer fix --rules=@Symfony --using-cache=no --dry-run --diff src', 'builder', '.', no_deps=True)


@task(environments=['local'])
def lint():
    "Lint"
    env.compose_run('php-cs-fixer fix --rules=@Symfony --using-cache=no --diff src || echo true', 'builder', '.', no_deps=True)
    env.compose_run('yarn run lint', 'builder', '.', no_deps=True)
    env.compose_run('autopep8 --in-place --aggressive --aggressive infrastructure/deploylib/* --ignore=E501', 'builder', '.', no_deps=True)


@task(environments=['local', 'testing'])
def static_analysis():
    "Run static analysis tools"
    local('yarn run typecheck')
    local('yarn run typecheck:coverage || true')
    env.service_command('php bin/phpstan analyse src || true', 'application', env.www_app)


@task(environments=['local', 'testing'])
def phpspec():
    "Run PHP Unit Tests"
    env.service_command('./bin/phpspec run --no-code-generation', 'application', env.www_app)


@task(environments=['local', 'testing'])
def jest():
    "Run JS Unit Tests"
    env.compose('run -e CI=True builder yarn test')


@task(environments=['local', 'testing'])
def behat(fast_failure='true', profile=False, tags='false', feature='false', parallel='false'):
    "Run Gerhkin Tests"
    env.service_command('mysqldump --opt -h database -u root symfony > var/db.backup', 'application', env.www_app)
    if profile:
        jobs = [profile]
    else:
        jobs = ['api', 'commands', 'frontend', 'javascript']

    for job in jobs:
        command = 'php -d memory_limit=-1 ./bin/behat' + ('', ' --parallel-process 10')[parallel != 'false'] + ' -p ' + job + ('', '  --tags=' + tags)[tags != 'false'] + ('', '  --stop-on-failure')[fast_failure == 'true'] + ('', ' --name ' + feature)[feature != 'false']
        env.service_command(command, 'application', env.www_app)


@task(environments=['local'])
def view():
    local('echo "secret" | open vnc://`docker-machine ip dinghy`')


@task(environments=['local'])
def clear_fixtures():
    local('docker ps -a | awk \'{ print $1,$2 }\' | grep capco/fixtures | awk \'{print $1 }\' | xargs -I {} docker rm -f {}')


@task(environments=['local'])
def kill_database_container():
    with settings(warn_only=True):
        local('docker ps -a | grep databasefixtures | awk \'{print $1}\' | xargs -I {} docker kill {}')


@task(environments=['local', 'testing'])
def save_fixtures_image(tag='latest', publish='false'):
    "Publish a new fixtures image"
    env.service_command('php bin/console capco:reinit --force --no-toggles', 'application', env.www_app)
    env.service_command('mysqldump -h database -uroot --opt symfony > infrastructure/services/databasefixtures/dump.sql', 'application', env.www_app, 'root')
    local('docker build -t capco/fixtures:latest infrastructure/services/databasefixtures')
    if publish != 'false':
        local('docker login -e ' + capcobot['email'] + ' -u ' + capcobot['user'] + ' -p ' + capcobot['pass'])
        local('docker push capco/fixtures')


@task(environments=['local'])
def blackfire_curl(url):
    "Blackfire curl"
    local('docker exec -i capco_application_1 blackfire --client-id=$BLACKFIRE_CLIENT_ID --client-token=$BLACKFIRE_CLIENT_TOKEN curl ' + url)


@task(environments=['local'])
def blackfire_run(cli):
    "Blackfire run"
    local('eval "$(docker-machine env dinghy)" && docker exec -u root -i capco_application_1 blackfire run ' + cli)


@task(environments=['local'])
def setup_git_hooks():
    "Set git hooks"
    local('rm -f .git/hooks/pre-commit && ln -s ../../infrastructure/git-hooks/hooks/pre-commit .git/hooks/pre-commit')


@task(environments=['local'])
def run_pre_commit_hook():
    "Run pre-commit hook"
    env.compose_run('infrastructure/git-hooks/scripts/cs-fixer.sh', 'builder', 'capco', '/var/www', no_deps=True)
    env.compose_run('infrastructure/git-hooks/scripts/pep8.sh', 'builder', 'capco', '/var/www', no_deps=True)
