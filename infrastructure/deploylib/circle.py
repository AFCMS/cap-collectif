from task import task
from fabric.operations import local, run, settings
from fabric.api import env
import re
import yaml


@task(environments=['local', 'testing'])
def fix_environment_variables_with_lxc():
    with open('app/config/parameters.yml', 'w') as yaml_file:
        yaml_file.write(yaml.dump({
            'parameters': {
                'database_host': 'database',
                'elasticsearch_host': 'elasticsearch',
                'redis_host': 'redis',
            }
        }, default_flow_style=False))


@task(environments=['testing'])
def clean_cache():
    "Clean cache of docker images"
    commit_message = local('git log --format=%B --no-merges -n 1', capture=True)
    if re.search('\[clean-cache\]', commit_message):
        local('rm -rf ~/docker')
        local('rm -rf ~/.composer')
        local('rm -rf ~/.yarn-cache')
        local('rm -rf node_modules')


@task(environments=['testing'])
def load_cache():
    "Load cache"
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_application.tar ]]; then docker load -i ~/docker/capcotest_application.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_applicationdata.tar ]]; then docker load -i ~/docker/capcotest_applicationdata.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_builder.tar ]]; then docker load -i ~/docker/capcotest_builder.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_seleniumhub.tar ]]; then docker load -i ~/docker/capcotest_seleniumhub.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_chrome.tar ]]; then docker load -i ~/docker/capcotest_chrome.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_mailcacher.tar ]]; then docker load -i ~/docker/capcotest_mailcacher.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_elasticsearch.tar ]]; then docker load -i ~/docker/capcotest_elasticsearch.tar; fi"')
    local('/bin/bash -c "if [[ -e ~/docker/capcotest_redis.tar ]]; then docker load -i ~/docker/capcotest_redis.tar; fi"')


@task(environments=['testing'])
def save_logs():
    "Save logs"
    local('docker logs capcotest_application_1 > $CIRCLE_TEST_REPORTS/application.log')
    local('docker logs capcotest_builder_1 > $CIRCLE_TEST_REPORTS/builder.log')
    local('docker logs capcotest_elasticsearch_1 > $CIRCLE_TEST_REPORTS/elasticsearch.log')


@task(environments=['local'])
def save_fixtures_image(tag='latest'):
    "Publish a new fixtures image"
    env.service_command('php bin/console capco:reinit --force', 'application', env.www_app)
    env.service_command('mysqldump -h database -uroot --opt symfony > infrastructure/services/databasefixtures/dump.sql', 'application', env.www_app, 'root')
    env.compose('build databasefixtures')
    image_id = local('docker images | grep capco_databasefixtures | awk \'{print $3}\'', capture=True)
    local('docker tag -f ' + image_id + ' spyl94/capco-fixtures:' + tag)
    local('docker login --username=spyl94')
    local('docker push spyl94/capco-fixtures')


@task(environments=['testing'])
def save_cache():
    "Rebuild infrastructure and save cache"
    compare_url = local('echo $CIRCLE_COMPARE_URL', capture=True)

    simpleMatch = re.search('https://github.com/jolicode/CapCollectif-SF2/compare/([a-z0-9]+?)$', compare_url)
    match = re.search('https://github.com/jolicode/CapCollectif-SF2/compare/([a-z0-9]+?)\.\.\.([a-z0-9]+?)$', compare_url)

    if simpleMatch is None and match is None:
        return

    if simpleMatch is not None:
        changes = local('git diff --name-only %s | cat' % simpleMatch.group(1), capture=True).split("\n")
    else:
        changes = local('git diff --name-only %s %s | cat' % (match.group(1), match.group(2)), capture=True).split("\n")

    change_in_infrastructure = False

    for change in changes:
        match = re.search('^docker-compose.yml', change)

        if match is None:
            match = re.search('^infrastructure/services', change)

        if match is not None:
            change_in_infrastructure = True

    if change_in_infrastructure:
        env.compose('build')
        local('docker pull elasticsearch:1.7.3')
        local('docker pull redis:3')
        local('docker pull selenium/hub:2.47.1')
        local('docker pull selenium/node-chrome-debug:2.47.1')
        local('docker pull jderusse/mailcatcher:latest')
        local('mkdir -p ~/docker')
        local('docker save capcotest_application > ~/docker/capcotest_application.tar')
        local('docker save capcotest_applicationdata > ~/docker/capcotest_applicationdata.tar')
        local('docker save capcotest_builder > ~/docker/capcotest_builder.tar')
        local('docker save elasticsearch > ~/docker/capcotest_elasticsearch.tar')
        local('docker save redis > ~/docker/capcotest_redis.tar')
        local('docker save selenium/hub > ~/docker/capcotest_seleniumhub.tar')
        local('docker save selenium/node-chrome-debug > ~/docker/capcotest_chrome.tar')
        local('docker save jderusse/mailcatcher > ~/docker/capcotest_mailcacher.tar')
